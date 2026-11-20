<?php

declare(strict_types=1);

namespace Tests\Unit\Shops;

use Izumi\Backend\app\Controllers\Shops\ShopsController;
use Izumi\Backend\app\Models\Auth\Session;
use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Repositories\Sessions\InMemorySessionsRepository;
use Izumi\Backend\app\Repositories\Shops\InMemoryShopsRepository;
use Izumi\Backend\app\Repositories\Tags\InMemoryTagsRepository;
use Izumi\Backend\app\Repositories\Users\InMemoryUsersRepository;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\Request;
use PHPUnit\Framework\TestCase;

final class ShopsControllerTest extends TestCase
{
    private InMemoryUsersRepository $users;
    private InMemoryShopsRepository $shops;
    private InMemoryTagsRepository $tags;
    private InMemorySessionsRepository $sessions;
    private ShopsController $controller;
    private string $authenticatedUserId;

    protected function setUp(): void
    {
        $this->users = new InMemoryUsersRepository();
        $this->shops = new InMemoryShopsRepository();
        $this->tags = new InMemoryTagsRepository();
        $this->sessions = new InMemorySessionsRepository();

        $resolver = new SessionResolver($this->sessions, $this->users);
        $this->controller = new ShopsController($this->shops, $this->tags, $resolver);

        // Create an authenticated user with a session
        $user = User::create(['name' => 'Test User', 'nickname' => 'testuser']);
        if (is_object($user)) {
            $this->users->save($user);
            $session = Session::create([
                'user' => $user->id,
                'expiration' => date('Y-m-d H:i:s', time() + 86400),
            ]);
            $this->sessions->save($session);
            $this->authenticatedUserId = $user->id;
            $_COOKIE[SessionResolver::COOKIE_NAME] = $session->id;
        }
    }

    protected function tearDown(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);
    }

    // ── Shop creation ──

    public function testCreateShopForUserWithoutShop(): void
    {
        $request = $this->post('/shops', ['name' => 'My Shop']);

        $response = $this->controller->create($request);

        $this->assertSame(201, $response->status);
        $this->assertSame('My Shop', $response->body['data']['name']);
        $this->assertCount(1, $this->shops->findAll());
    }

    public function testCreateShopRejectsDuplicateOwner(): void
    {
        // Create the first shop
        $this->controller->create($this->post('/shops', ['name' => 'First Shop']));

        // Try to create a second
        $request = $this->post('/shops', ['name' => 'Second Shop']);
        $response = $this->controller->create($request);

        $this->assertSame(403, $response->status);
        $this->assertSame('User already owns a shop', $response->body['error']);
    }

    public function testCreateShopRequiresAuth(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);

        $request = $this->post('/shops', ['name' => 'My Shop']);
        $response = $this->controller->create($request);

        $this->assertSame(401, $response->status);
    }

    public function testCreateShopRejectsEmptyName(): void
    {
        $request = $this->post('/shops', ['name' => '']);
        $response = $this->controller->create($request);

        $this->assertSame(422, $response->status);
    }

    public function testGetAllShops(): void
    {
        // Each user may own at most one shop, so create shops for two users.
        $this->controller->create($this->post('/shops', ['name' => 'Shop A']));
        $this->switchToUser('shop-owner-b');
        $this->controller->create($this->post('/shops', ['name' => 'Shop B']));

        $request = new Request(method: 'GET', uri: '/shops');
        $response = $this->controller->getAll($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(2, $response->body['data']);
    }

    // ── Tag creation ──

    public function testCreateTagInShop(): void
    {
        $shop = $this->createShop();

        $request = $this->post("/shops/{$shop->id}/tags", ['name' => 'Retro Cards'], ['shopId' => $shop->id]);
        $response = $this->controller->createTag($request);

        $this->assertSame(201, $response->status);
        $this->assertSame('Retro Cards', $response->body['data']['name']);
    }

    public function testCreateTagRejectsNonOwner(): void
    {
        $shop = $this->createShop();

        // Switch to a different user who does not own this shop
        $this->switchToUser('other');

        $request = $this->post("/shops/{$shop->id}/tags", ['name' => 'Retro Cards'], ['shopId' => $shop->id]);
        $response = $this->controller->createTag($request);

        $this->assertSame(403, $response->status);
    }

    public function testCreateTagRejectsDuplicateName(): void
    {
        $shop = $this->createShop();

        $this->controller->createTag($this->post(
            "/shops/{$shop->id}/tags",
            ['name' => 'Cards'],
            ['shopId' => $shop->id],
        ));
        $response = $this->controller->createTag($this->post(
            "/shops/{$shop->id}/tags",
            ['name' => 'cards'],
            ['shopId' => $shop->id],
        ));

        $this->assertSame(422, $response->status);
    }

    public function testCreateTagRequiresAuth(): void
    {
        $shop = $this->createShop();
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);

        $request = $this->post("/shops/{$shop->id}/tags", ['name' => 'Cards'], ['shopId' => $shop->id]);
        $response = $this->controller->createTag($request);

        $this->assertSame(401, $response->status);
    }

    // ── Tag deletion ──

    public function testDeleteTagInShop(): void
    {
        $shop = $this->createShop();
        $tag = $this->createTag($shop->id);

        $request = new Request(method: 'DELETE', uri: "/shops/{$shop->id}/tags/{$tag->id}", params: [
            'shopId' => $shop->id,
            'tagId' => $tag->id,
        ]);
        $response = $this->controller->deleteTag($request);

        $this->assertSame(200, $response->status);
    }

    public function testDeleteTagRejectsNonOwner(): void
    {
        $shop = $this->createShop();
        $tag = $this->createTag($shop->id);

        $this->switchToUser('other');

        $request = new Request(method: 'DELETE', uri: "/shops/{$shop->id}/tags/{$tag->id}", params: [
            'shopId' => $shop->id,
            'tagId' => $tag->id,
        ]);
        $response = $this->controller->deleteTag($request);

        $this->assertSame(403, $response->status);
    }

    // ── Helpers ──

    private function post(string $uri, array $body, array $params = []): Request
    {
        return new Request(method: 'POST', uri: $uri, body: json_encode($body, JSON_THROW_ON_ERROR), params: $params);
    }

    /**
     * Create (or reuse) a user and switch the authenticated session to them.
     */
    private function switchToUser(string $nickname): void
    {
        $user = User::create(['name' => $nickname, 'nickname' => $nickname]);
        if (!is_object($user)) {
            $this->fail("Could not create user {$nickname}");
        }
        $this->users->save($user);
        $session = Session::create([
            'user' => $user->id,
            'expiration' => date('Y-m-d H:i:s', time() + 86400),
        ]);
        $this->sessions->save($session);
        $_COOKIE[SessionResolver::COOKIE_NAME] = $session->id;
    }

    private function createShop(): \Izumi\Backend\app\Models\Shop\Shop
    {
        $request = $this->post('/shops', ['name' => 'Test Shop']);
        $response = $this->controller->create($request);
        $this->assertSame(201, $response->status);

        $shops = $this->shops->findAll();
        $this->assertCount(1, $shops);

        return $shops[0];
    }

    private function createTag(string $shopId): \Izumi\Backend\app\Models\Tag\Tag
    {
        $request = $this->post("/shops/{$shopId}/tags", ['name' => 'Test Tag'], ['shopId' => $shopId]);
        $response = $this->controller->createTag($request);
        $this->assertSame(201, $response->status);

        $tags = $this->tags->findByShop($shopId);
        $this->assertCount(1, $tags);

        return $tags[0];
    }
}
