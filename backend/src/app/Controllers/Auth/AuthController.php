<?php

namespace Izumi\Backend\app\Controllers\Auth;

use Izumi\Backend\app\DTOs\Auth\AuthLoginDTO;
use Izumi\Backend\app\DTOs\Auth\AuthMeResponseDTO;
use Izumi\Backend\app\DTOs\Auth\AuthRegisterDTO;
use Izumi\Backend\app\DTOs\Auth\AuthVerifyDTO;
use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\Session;
use Izumi\Backend\app\Models\Auth\TempUser;
use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Models\Auth\UserIdentity;
use Izumi\Backend\app\Repositories\Sessions\ISessionsRepository;
use Izumi\Backend\app\Repositories\TempUsers\ITempUsersRepository;
use Izumi\Backend\app\Repositories\UserIdentities\IUserIdentitiesRepository;
use Izumi\Backend\app\Repositories\Users\IUsersRepository;
use Izumi\Backend\app\Shared\Attributes\ApiOperation;
use Izumi\Backend\app\Shared\Attributes\ApiResponse;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Errors\ValidationError;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use JsonException;

final class AuthController extends Controller
{
    private const SESSION_TTL_HOURS = 24;
    private const TEMP_USER_TTL_HOURS = 24;

    public function __construct(
        private readonly IUsersRepository $users,
        private readonly IUserIdentitiesRepository $identities,
        private readonly ISessionsRepository $sessions,
        private readonly ITempUsersRepository $tempUsers,
        private readonly SessionResolver $resolver,
    ) {}

    /**
     * @param array{code: string, message: string} $data
     */
    protected function response(mixed $data, int $status = 200, array $headers = []): Response
    {
        $allHeaders = ['Content-Type' => 'application/json', ...$headers];
        return new Response($data, $status, $allHeaders);
    }

    #[ApiOperation(
        summary: 'Register a new user (creates a TempUser)',
        request: AuthRegisterDTO::class,
        tags: ['auth'],
    )]
    #[ApiResponse(status: 201, description: 'TempUser created successfully')]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 409, description: 'Email already in use')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function register(Request $request): Response
    {
        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(AuthRegisterDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        // Reject if an active password identity already exists for this email
        $existing = $this->identities->findByEmail($dto->email);
        if ($existing !== null) {
            return $this->response(['error' => 'An account with this email already exists'], 409);
        }

        $passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);
        $expiration = new \DateTimeImmutable('+' . self::TEMP_USER_TTL_HOURS . ' hours');

        $tempUser = TempUser::create([
            'email' => $dto->email,
            'password_hash' => $passwordHash,
            'expiration' => $expiration->format('Y-m-d H:i:s'),
        ]);

        if (is_array($tempUser)) {
            return $this->response(['errors' => self::mapErrors($tempUser)], 422);
        }

        $this->tempUsers->save($tempUser);

        return $this->response([
            'message' => 'Registration started. Verify your email to complete the process.',
            'data' => ['id' => $tempUser->id, 'email' => $tempUser->email],
        ], 201);
    }

    #[ApiOperation(
        summary: 'Verify a TempUser and promote to a real User',
        request: AuthVerifyDTO::class,
        tags: ['auth'],
    )]
    #[ApiResponse(status: 200, description: 'User verified and session created')]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 404, description: 'TempUser not found or expired')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function verify(Request $request): Response
    {
        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(AuthVerifyDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();
        $tempUser = $this->tempUsers->findByEmail($dto->email);

        if ($tempUser === null) {
            return $this->response(['error' => 'No pending registration found for this email'], 404);
        }

        if ($tempUser->isExpired()) {
            $this->tempUsers->delete($tempUser->id);
            return $this->response(['error' => 'Registration has expired'], 404);
        }

        if (!password_verify($dto->password, $tempUser->password_hash)) {
            return $this->response(['error' => 'Password does not match the registration'], 401);
        }

        $user = User::create([
            'name' => $dto->email,
            'nickname' => $this->generateNickname($dto->email),
        ]);

        if (is_array($user)) {
            return $this->response(['errors' => self::mapErrors($user)], 422);
        }

        $this->users->save($user);

        $identity = UserIdentity::create([
            'user' => $user->id,
            'provider' => AuthenticationProvider::Password->value,
            'email' => $dto->email,
            'password_hash' => $tempUser->password_hash,
        ]);

        if (is_array($identity)) {
            return $this->response(['errors' => self::mapErrors($identity)], 422);
        }

        $this->identities->save($identity);
        $this->tempUsers->delete($tempUser->id);

        $session = $this->createSession($user->id);
        $cookie = $this->buildSetCookieHeader($session->id, true);

        return $this->response(['message' => 'User verified and session created', 'data' => $user->toArray()], 200, [
            'Set-Cookie' => $cookie,
        ]);
    }

    #[ApiOperation(summary: 'Login with email and password', request: AuthLoginDTO::class, tags: ['auth'])]
    #[ApiResponse(status: 200, description: 'Login successful, session cookie set')]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Invalid credentials')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function login(Request $request): Response
    {
        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(AuthLoginDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();
        $identity = $this->identities->findByEmail($dto->email);

        if ($identity === null || !password_verify($dto->password, $identity->password_hash)) {
            return $this->response(['error' => 'Invalid credentials'], 401);
        }

        $user = $this->users->findById($identity->user);

        if ($user === null) {
            return $this->response(['error' => 'Invalid credentials'], 401);
        }

        $session = $this->createSession($user->id);
        $cookie = $this->buildSetCookieHeader($session->id, true);

        return $this->response(['message' => 'Login successful', 'data' => $user->toArray()], 200, [
            'Set-Cookie' => $cookie,
        ]);
    }

    #[ApiOperation(summary: 'Logout and invalidate the current session', tags: ['auth'])]
    #[ApiResponse(status: 200, description: 'Logged out successfully')]
    #[ApiResponse(status: 401, description: 'No active session')]
    public function logout(Request $request): Response
    {
        $cookieValue = $_COOKIE[SessionResolver::COOKIE_NAME] ?? null;

        if ($cookieValue === null || $cookieValue === '') {
            return $this->response(['error' => 'No active session'], 401);
        }

        $this->sessions->delete($cookieValue);
        $clearCookie = $this->buildSetCookieHeader('', false);

        return $this->response(['message' => 'Logged out successfully'], 200, ['Set-Cookie' => $clearCookie]);
    }

    #[ApiOperation(summary: 'Get the currently authenticated user', tags: ['auth'])]
    #[ApiResponse(status: 200, description: 'Authenticated user', dto: AuthMeResponseDTO::class)]
    #[ApiResponse(status: 401, description: 'No active session or invalid credentials')]
    public function me(Request $request): Response
    {
        $cookieValue = $_COOKIE[SessionResolver::COOKIE_NAME] ?? null;
        $user = $this->resolver->resolve($cookieValue);

        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        return $this->response(['data' => $user->toArray()]);
    }

    private function createSession(string $userId): Session
    {
        $expiration = new \DateTimeImmutable('+' . self::SESSION_TTL_HOURS . ' hours');

        $session = Session::create([
            'user' => $userId,
            'expiration' => $expiration->format('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->sessions->save($session);

        return $session;
    }

    private function generateNickname(string $email): string
    {
        $localPart = explode('@', $email)[0];
        $localPart = (string) preg_replace('/[^a-zA-Z0-9]/', '', $localPart);
        $suffix = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        return $localPart . '_' . $suffix;
    }

    private function buildSetCookieHeader(string $sessionId, bool $active): string
    {
        $base = SessionResolver::COOKIE_NAME;

        if ($active) {
            $cookie = "{$base}={$sessionId}; Path=/; HttpOnly; SameSite=Lax";
            $isSecure = ($_SERVER['HTTPS'] ?? '') === 'on';
            if ($isSecure) {
                $cookie .= '; Secure';
            }
            return $cookie;
        }

        return "{$base}=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0";
    }

    /**
     * @param list<ValidationError> $errors
     * @return list<array{field: string, code: string, message: string}>
     */
    private static function mapValidationErrors(array $errors): array
    {
        return array_map(static fn(ValidationError $error): array => $error->toArray(), $errors);
    }

    /**
     * @param list<Error> $errors
     * @return list<array{code: string, message: string}>
     */
    private static function mapErrors(array $errors): array
    {
        return array_map(static fn(Error $error): array => $error->toArray(), $errors);
    }
}
