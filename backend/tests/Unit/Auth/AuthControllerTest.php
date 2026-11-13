<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Izumi\Backend\app\Controllers\Auth\AuthController;
use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\TempUser;
use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Models\Auth\UserIdentity;
use Izumi\Backend\app\Repositories\Sessions\InMemorySessionsRepository;
use Izumi\Backend\app\Repositories\TempUsers\InMemoryTempUsersRepository;
use Izumi\Backend\app\Repositories\UserIdentities\InMemoryUserIdentitiesRepository;
use Izumi\Backend\app\Repositories\Users\InMemoryUsersRepository;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\Request;
use PHPUnit\Framework\TestCase;

final class AuthControllerTest extends TestCase
{
    private InMemoryUsersRepository $users;
    private InMemoryUserIdentitiesRepository $identities;
    private InMemorySessionsRepository $sessions;
    private InMemoryTempUsersRepository $tempUsers;
    private AuthController $controller;

    protected function setUp(): void
    {
        $this->users = new InMemoryUsersRepository();
        $this->identities = new InMemoryUserIdentitiesRepository();
        $this->sessions = new InMemorySessionsRepository();
        $this->tempUsers = new InMemoryTempUsersRepository();

        $resolver = new SessionResolver($this->sessions, $this->users);

        $this->controller = new AuthController(
            $this->users,
            $this->identities,
            $this->sessions,
            $this->tempUsers,
            $resolver,
        );
    }

    // ── Register ──

    public function testRegisterCreatesTempUser(): void
    {
        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->register($request);

        $this->assertSame(201, $response->status);
        $this->assertSame('user@example.com', $response->body['data']['email']);

        $all = $this->tempUsers->findAll();
        $this->assertCount(1, $all);
        $this->assertFalse($all[0]->isExpired());
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        // Pre-seed an active password identity
        $user = User::create(['name' => 'Existing', 'nickname' => 'existing']);
        if (is_object($user)) {
            $this->users->save($user);
            $identity = UserIdentity::create([
                'user' => $user->id,
                'provider' => AuthenticationProvider::Password->value,
                'email' => 'taken@example.com',
                'password_hash' => password_hash('x', PASSWORD_BCRYPT),
            ]);
            if (is_object($identity)) {
                $this->identities->save($identity);
            }
        }

        $request = $this->request('POST', [
            'email' => 'taken@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->register($request);

        $this->assertSame(409, $response->status);
        $this->assertCount(0, $this->tempUsers->findAll());
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'short',
        ]);

        $response = $this->controller->register($request);

        $this->assertSame(422, $response->status);
        $this->assertCount(0, $this->tempUsers->findAll());
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $request = $this->request('POST', [
            'email' => 'not-an-email',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->register($request);

        $this->assertSame(422, $response->status);
    }

    public function testRegisterWithEmptyBody(): void
    {
        $request = new Request(method: 'POST', uri: '/auth/register', body: '');

        $response = $this->controller->register($request);

        $this->assertSame(400, $response->status);
    }

    // ── Verify ──

    public function testVerifyPromotesTempUser(): void
    {
        // Create a temp user first
        $this->registerUser();

        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->verify($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(1, $this->users->findAll());
        $this->assertCount(0, $this->tempUsers->findAll());
    }

    public function testVerifyRejectsUnknownEmail(): void
    {
        $request = $this->request('POST', [
            'email' => 'unknown@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->verify($request);

        $this->assertSame(404, $response->status);
    }

    public function testVerifyRejectsExpiredRegistration(): void
    {
        // Seed an expired temp user directly
        $expired = TempUser::create([
            'email' => 'expired@example.com',
            'password_hash' => password_hash('strongpassword', PASSWORD_BCRYPT),
            'expiration' => date('Y-m-d H:i:s', time() - 86400),
        ]);
        if (is_object($expired)) {
            $this->tempUsers->save($expired);
        }

        $request = $this->request('POST', [
            'email' => 'expired@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->verify($request);

        $this->assertSame(404, $response->status);
    }

    public function testVerifyRejectsWrongPassword(): void
    {
        $this->registerUser();

        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response = $this->controller->verify($request);

        $this->assertSame(401, $response->status);
    }

    // ── Login ──

    public function testLoginSetsSessionCookie(): void
    {
        $this->setupLoggedInUser();

        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->login($request);

        $this->assertSame(200, $response->status);
        $this->assertArrayHasKey('Set-Cookie', $response->headers);
        $this->assertStringContainsString('session_id=', $response->headers['Set-Cookie']);
        $this->assertStringContainsString('HttpOnly', $response->headers['Set-Cookie']);
        $this->assertStringContainsString('SameSite=Lax', $response->headers['Set-Cookie']);
    }

    public function testLoginRejectsUnknownEmail(): void
    {
        $request = $this->request('POST', [
            'email' => 'nobody@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->status);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->setupLoggedInUser();

        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response = $this->controller->login($request);

        $this->assertSame(401, $response->status);
    }

    // ── Me ──

    public function testMeReturnsUserWithValidSession(): void
    {
        $sessionId = $this->setupLoggedInUser();

        $_COOKIE[SessionResolver::COOKIE_NAME] = $sessionId;

        $response = $this->controller->me(new Request(method: 'GET', uri: '/auth/me'));

        $this->assertSame(200, $response->status);
        $this->assertSame('user@example.com', $response->body['data']['name']);
    }

    public function testMeReturns401WithoutSession(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);

        $response = $this->controller->me(new Request(method: 'GET', uri: '/auth/me'));

        $this->assertSame(401, $response->status);
    }

    public function testMeReturns401ForExpiredSession(): void
    {
        // Create an expired session
        $user = User::create(['name' => 'user@example.com', 'nickname' => 'user_1000']);
        if (is_object($user)) {
            $this->users->save($user);
        }

        $expiredSession = \Izumi\Backend\app\Models\Auth\Session::create([
            'user' => $user->id,
            'expiration' => date('Y-m-d H:i:s', time() - 3600),
            'created_at' => date('Y-m-d H:i:s', time() - 7200),
        ]);
        if (is_object($expiredSession)) {
            $this->sessions->save($expiredSession);
        }

        $_COOKIE[SessionResolver::COOKIE_NAME] = $expiredSession->id;

        $response = $this->controller->me(new Request(method: 'GET', uri: '/auth/me'));

        $this->assertSame(401, $response->status);
    }

    // ── Logout ──

    public function testLogoutInvalidatesSession(): void
    {
        $sessionId = $this->setupLoggedInUser();

        $_COOKIE[SessionResolver::COOKIE_NAME] = $sessionId;

        $response = $this->controller->logout(new Request(method: 'POST', uri: '/auth/logout'));

        $this->assertSame(200, $response->status);

        // After logout, the session should no longer resolve
        $response = $this->controller->me(new Request(method: 'GET', uri: '/auth/me'));
        $this->assertSame(401, $response->status);
    }

    public function testLogoutWithoutSessionReturns401(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);

        $response = $this->controller->logout(new Request(method: 'POST', uri: '/auth/logout'));

        $this->assertSame(401, $response->status);
    }

    // ── Helpers ──

    /**
     * @return array<string, mixed>
     */
    private function request(string $method, array $body = []): Request
    {
        return new Request(method: $method, uri: '/', body: json_encode($body, JSON_THROW_ON_ERROR));
    }

    private function registerUser(): void
    {
        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'strongpassword',
        ]);

        $response = $this->controller->register($request);
        $this->assertSame(201, $response->status);
    }

    /**
     * Creates a user + identity ready for login and returns the new session id.
     */
    private function setupLoggedInUser(): string
    {
        $user = User::create(['name' => 'user@example.com', 'nickname' => 'user_1000']);
        if (!is_object($user)) {
            $this->fail('Could not create user');
        }
        $this->users->save($user);

        $identity = UserIdentity::create([
            'user' => $user->id,
            'provider' => AuthenticationProvider::Password->value,
            'email' => 'user@example.com',
            'password_hash' => password_hash('strongpassword', PASSWORD_BCRYPT),
        ]);
        if (!is_object($identity)) {
            $this->fail('Could not create identity');
        }
        $this->identities->save($identity);

        // Perform a login to create the session
        $request = $this->request('POST', [
            'email' => 'user@example.com',
            'password' => 'strongpassword',
        ]);
        $response = $this->controller->login($request);
        $this->assertSame(200, $response->status);

        $sessions = $this->sessions->findAll();
        $this->assertCount(1, $sessions);

        return $sessions[0]->id;
    }
}
