<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use Izumi\Backend\app\Models\Auth\AuthenticationProvider;
use Izumi\Backend\app\Models\Auth\Session;
use Izumi\Backend\app\Models\Auth\TempUser;
use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Models\Auth\UserIdentity;
use Izumi\Backend\app\Shared\Errors\Error;
use PHPUnit\Framework\TestCase;

final class AuthModelTest extends TestCase
{
    // ── User ──

    public function testUserCreation(): void
    {
        $user = User::create(['name' => 'Kevyn Ribeiro', 'nickname' => 'izumi']);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Kevyn Ribeiro', $user->name);
        $this->assertSame('izumi', $user->nickname);
        $this->assertNotEmpty($user->id);
    }

    public function testUserCreationWithInvalidName(): void
    {
        $result = User::create(['name' => '', 'nickname' => 'izumi']);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testUserCreationWithInvalidNickname(): void
    {
        $result = User::create(['name' => 'Kevyn', 'nickname' => '']);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testUserReconstruction(): void
    {
        $original = User::create(['name' => 'Kevyn', 'nickname' => 'izumi']);

        if (!is_object($original)) {
            $this->fail('Could not create user');
        }

        $reconstructed = User::fromArray([
            'id' => $original->id,
            'name' => $original->name,
            'nickname' => $original->nickname,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->name, $reconstructed->name);
        $this->assertSame($original->nickname, $reconstructed->nickname);
    }

    public function testUserUpdate(): void
    {
        $user = User::create(['name' => 'Kevyn', 'nickname' => 'izumi']);

        if (!is_object($user)) {
            $this->fail('Could not create user');
        }

        $errors = $user->update(['name' => 'Kevyn R', 'nickname' => 'izumi_dev']);

        $this->assertSame([], $errors);
        $this->assertSame('Kevyn R', $user->name);
        $this->assertSame('izumi_dev', $user->nickname);
    }

    // ── UserIdentity ──

    public function testUserIdentityCreationPassword(): void
    {
        $identity = UserIdentity::create([
            'user' => 'user-id',
            'provider' => AuthenticationProvider::Password->value,
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$hashed',
        ]);

        $this->assertInstanceOf(UserIdentity::class, $identity);
        $this->assertSame(AuthenticationProvider::Password, $identity->provider);
        $this->assertSame('user@example.com', $identity->email);
    }

    public function testUserIdentityCreationOpenIDConnect(): void
    {
        $identity = UserIdentity::create([
            'user' => 'user-id',
            'provider' => AuthenticationProvider::OpenIDConnect->value,
            'provider_subject' => 'google-abc123',
        ]);

        $this->assertInstanceOf(UserIdentity::class, $identity);
        $this->assertSame(AuthenticationProvider::OpenIDConnect, $identity->provider);
        $this->assertSame('google-abc123', $identity->provider_subject);
    }

    public function testUserIdentityPasswordRequiresHash(): void
    {
        $result = UserIdentity::create([
            'user' => 'user-id',
            'provider' => AuthenticationProvider::Password->value,
            'email' => 'user@example.com',
            // missing password_hash
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testUserIdentityOpenIDConnectRequiresSubject(): void
    {
        $result = UserIdentity::create([
            'user' => 'user-id',
            'provider' => AuthenticationProvider::OpenIDConnect->value,
            // missing provider_subject
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testUserIdentityInvalidProvider(): void
    {
        $result = UserIdentity::create([
            'user' => 'user-id',
            'provider' => 'invalid_provider',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testUserIdentityReconstruction(): void
    {
        $original = UserIdentity::create([
            'user' => 'user-id',
            'provider' => AuthenticationProvider::Password->value,
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$hashed',
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create identity');
        }

        $reconstructed = UserIdentity::fromArray([
            'id' => $original->id,
            'user' => $original->user,
            'provider' => $original->provider->value,
            'provider_subject' => $original->provider_subject,
            'email' => $original->email,
            'password_hash' => $original->password_hash,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->provider, $reconstructed->provider);
        $this->assertSame($original->email, $reconstructed->email);
    }

    // ── Session ──

    public function testSessionCreation(): void
    {
        $session = Session::create([
            'user' => 'user-id',
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertSame('user-id', $session->user);
        $this->assertFalse($session->isExpired());
    }

    public function testSessionExpiryDetection(): void
    {
        $session = Session::create([
            'user' => 'user-id',
            'expiration' => date('Y-m-d H:i:s', time() - 86400),
            'created_at' => date('Y-m-d H:i:s', time() - 86400),
        ]);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue($session->isExpired());
    }

    public function testSessionReconstruction(): void
    {
        $original = Session::create([
            'user' => 'user-id',
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create session');
        }

        $reconstructed = Session::fromArray([
            'id' => $original->id,
            'user' => $original->user,
            'expiration' => $original->toArray()['expiration'],
            'created_at' => $original->toArray()['created_at'],
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->user, $reconstructed->user);
    }

    // ── TempUser ──

    public function testTempUserCreation(): void
    {
        $tempUser = TempUser::create([
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$hashed',
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
        ]);

        $this->assertInstanceOf(TempUser::class, $tempUser);
        $this->assertSame('user@example.com', $tempUser->email);
        $this->assertFalse($tempUser->isExpired());
    }

    public function testTempUserInvalidEmail(): void
    {
        $result = TempUser::create([
            'email' => 'invalid-email',
            'password_hash' => '$2y$10$hashed',
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testTempUserExpiryDetection(): void
    {
        $tempUser = TempUser::create([
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$hashed',
            'expiration' => date('Y-m-d H:i:s', time() - 86400),
        ]);

        $this->assertInstanceOf(TempUser::class, $tempUser);
        $this->assertTrue($tempUser->isExpired());
    }

    public function testTempUserReconstruction(): void
    {
        $original = TempUser::create([
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$hashed',
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create temp user');
        }

        $reconstructed = TempUser::fromArray([
            'id' => $original->id,
            'email' => $original->email,
            'password_hash' => $original->password_hash,
            'expiration' => $original->toArray()['expiration'],
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->email, $reconstructed->email);
    }

    // ── AuthenticationProvider ──

    public function testAuthenticationProviderValues(): void
    {
        $this->assertSame('password', AuthenticationProvider::Password->value);
        $this->assertSame('openid_connect', AuthenticationProvider::OpenIDConnect->value);
    }
}
