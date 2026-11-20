<?php

declare(strict_types=1);

namespace Tests\Unit\Collectables;

use Izumi\Backend\app\Controllers\Collectables\CollectablesController;
use Izumi\Backend\app\Models\Auth\Session;
use Izumi\Backend\app\Models\Auth\User;
use Izumi\Backend\app\Models\Collectable\Collectable;
use Izumi\Backend\app\Models\Shop\Shop;
use Izumi\Backend\app\Models\Tag\Tag;
use Izumi\Backend\app\Repositories\Collectables\InMemoryCollectablesRepository;
use Izumi\Backend\app\Repositories\CollectableTags\InMemoryCollectableTagsRepository;
use Izumi\Backend\app\Repositories\Sessions\InMemorySessionsRepository;
use Izumi\Backend\app\Repositories\Shops\InMemoryShopsRepository;
use Izumi\Backend\app\Repositories\Tags\InMemoryTagsRepository;
use Izumi\Backend\app\Repositories\Users\InMemoryUsersRepository;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\Request;
use PHPUnit\Framework\TestCase;

final class CollectablesControllerTest extends TestCase
{
    private InMemoryUsersRepository $users;
    private InMemoryShopsRepository $shops;
    private InMemoryTagsRepository $tags;
    private InMemoryCollectablesRepository $collectables;
    private InMemoryCollectableTagsRepository $collectableTags;
    private InMemorySessionsRepository $sessions;
    private CollectablesController $controller;

    protected function setUp(): void
    {
        $this->users = new InMemoryUsersRepository();
        $this->shops = new InMemoryShopsRepository();
        $this->tags = new InMemoryTagsRepository();
        $this->collectables = new InMemoryCollectablesRepository();
        $this->collectableTags = new InMemoryCollectableTagsRepository();
        $this->sessions = new InMemorySessionsRepository();

        $resolver = new SessionResolver($this->sessions, $this->users);
        $this->controller = new CollectablesController(
            $this->collectables,
            $this->collectableTags,
            $this->shops,
            $this->tags,
            $resolver,
        );

        // Authenticated user with a shop
        $user = User::create(['name' => 'Test User', 'nickname' => 'testuser']);
        if (is_object($user)) {
            $this->users->save($user);
            $shop = Shop::create(['name' => 'Test Shop', 'owner' => $user->id]);
            if (is_object($shop)) {
                $this->shops->save($shop);
            }
            $session = Session::create([
                'user' => $user->id,
                'expiration' => date('Y-m-d H:i:s', time() + 86400),
            ]);
            $this->sessions->save($session);
            $_COOKIE[SessionResolver::COOKIE_NAME] = $session->id;
        }
    }

    protected function tearDown(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);
    }

    // ── CRUD ──

    public function testCreateCollectable(): void
    {
        $request = $this->post('/collectables', [
            'name' => 'Rare Card',
            'description' => 'A rare card.',
            'price' => '49.90',
            'images' => ['https://example.com/a.jpg'],
        ]);

        $response = $this->controller->create($request);

        $this->assertSame(201, $response->status);
        $this->assertSame('Rare Card', $response->body['data']['name']);
        $this->assertSame('49.90', $response->body['data']['price']);
    }

    public function testCreateCollectableRejectsNoShop(): void
    {
        // Remove the shop
        foreach ($this->shops->findAll() as $shop) {
            $this->shops->delete($shop->id);
        }

        $request = $this->post('/collectables', [
            'name' => 'Rare Card',
            'description' => 'A rare card.',
            'price' => '49.90',
        ]);

        $response = $this->controller->create($request);

        $this->assertSame(403, $response->status);
    }

    public function testCreateCollectableRequiresAuth(): void
    {
        unset($_COOKIE[SessionResolver::COOKIE_NAME]);

        $request = $this->post('/collectables', [
            'name' => 'Rare Card',
            'description' => 'A rare card.',
            'price' => '49.90',
        ]);

        $response = $this->controller->create($request);

        $this->assertSame(401, $response->status);
    }

    public function testCreateCollectableRejectsInvalidPrice(): void
    {
        $request = $this->post('/collectables', [
            'name' => 'Rare Card',
            'description' => 'A rare card.',
            'price' => '-1.00',
        ]);

        $response = $this->controller->create($request);

        $this->assertSame(422, $response->status);
    }

    public function testCreateCollectableRejectsEmptyName(): void
    {
        $request = $this->post('/collectables', [
            'name' => '',
            'description' => 'A rare card.',
            'price' => '49.90',
        ]);

        $response = $this->controller->create($request);

        $this->assertSame(422, $response->status);
    }

    public function testGetById(): void
    {
        $collectable = $this->createCollectable();

        $request = new Request(method: 'GET', uri: '/collectables/' . $collectable->id, params: [
            'id' => $collectable->id,
        ]);
        $response = $this->controller->getById($request);

        $this->assertSame(200, $response->status);
        $this->assertSame($collectable->id, $response->body['data']['id']);
    }

    public function testGetByIdReturns404ForUnknown(): void
    {
        $request = new Request(method: 'GET', uri: '/collectables/unknown-id', params: ['id' => 'unknown-id']);
        $response = $this->controller->getById($request);

        $this->assertSame(404, $response->status);
    }

    public function testGetAll(): void
    {
        $this->createCollectable();
        $this->createCollectable();

        $request = new Request(method: 'GET', uri: '/collectables');
        $response = $this->controller->getAll($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(2, $response->body['data']);
    }

    public function testUpdateCollectable(): void
    {
        $collectable = $this->createCollectable();

        $request = $this->put(
            '/collectables/' . $collectable->id,
            [
                'name' => 'Updated Card',
                'description' => 'Updated description.',
                'price' => '29.99',
            ],
            ['id' => $collectable->id],
        );

        $response = $this->controller->update($request);

        $this->assertSame(200, $response->status);
        $this->assertSame('Updated Card', $response->body['data']['name']);
    }

    public function testUpdateCollectableRejectsNonOwner(): void
    {
        $collectable = $this->createCollectable();
        $this->switchToUser('other');

        $request = $this->put(
            '/collectables/' . $collectable->id,
            [
                'name' => 'Hacked',
                'description' => 'desc',
                'price' => '1.00',
            ],
            ['id' => $collectable->id],
        );

        $response = $this->controller->update($request);

        $this->assertSame(403, $response->status);
    }

    public function testDeleteCollectable(): void
    {
        $collectable = $this->createCollectable();

        $request = new Request(method: 'DELETE', uri: '/collectables/' . $collectable->id, params: [
            'id' => $collectable->id,
        ]);
        $response = $this->controller->delete($request);

        $this->assertSame(200, $response->status);
        $this->assertNull($this->collectables->findById($collectable->id));
    }

    public function testDeleteCollectableRejectsNonOwner(): void
    {
        $collectable = $this->createCollectable();
        $this->switchToUser('other');

        $request = new Request(method: 'DELETE', uri: '/collectables/' . $collectable->id, params: [
            'id' => $collectable->id,
        ]);
        $response = $this->controller->delete($request);

        $this->assertSame(403, $response->status);
    }

    // ── Tag associations ──

    public function testAssociateTag(): void
    {
        $collectable = $this->createCollectable();
        $tag = $this->createTag();

        $request = $this->post(
            '/collectables/' . $collectable->id . '/tags',
            ['tags' => [$tag->id]],
            ['id' => $collectable->id],
        );
        $response = $this->controller->associateTag($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(1, $this->collectableTags->findByCollectable($collectable->id));
    }

    public function testReplaceTags(): void
    {
        $collectable = $this->createCollectable();
        $tag1 = $this->createTag();
        $tag2 = $this->createTag();

        // Associate tag1
        $this->controller->associateTag($this->post(
            '/collectables/' . $collectable->id . '/tags',
            ['tags' => [$tag1->id]],
            ['id' => $collectable->id],
        ));
        $this->assertCount(1, $this->collectableTags->findByCollectable($collectable->id));

        // Replace with tag2
        $request = $this->put(
            '/collectables/' . $collectable->id . '/tags',
            ['tags' => [$tag2->id]],
            ['id' => $collectable->id],
        );
        $response = $this->controller->replaceTags($request);

        $this->assertSame(200, $response->status);
        $assocs = $this->collectableTags->findByCollectable($collectable->id);
        $this->assertCount(1, $assocs);
        $this->assertSame($tag2->id, $assocs[0]->tag);
    }

    public function testRemoveTag(): void
    {
        $collectable = $this->createCollectable();
        $tag = $this->createTag();

        // Associate
        $this->controller->associateTag($this->post(
            '/collectables/' . $collectable->id . '/tags',
            ['tags' => [$tag->id]],
            ['id' => $collectable->id],
        ));
        $this->assertCount(1, $this->collectableTags->findByCollectable($collectable->id));

        // Remove
        $request = new Request(
            method: 'DELETE',
            uri: '/collectables/' . $collectable->id . '/tags/' . $tag->id,
            params: ['id' => $collectable->id, 'tagId' => $tag->id],
        );
        $response = $this->controller->removeTag($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(0, $this->collectableTags->findByCollectable($collectable->id));
    }

    public function testTagAssociationRejectsNonOwner(): void
    {
        $collectable = $this->createCollectable();
        $tag = $this->createTag();
        $this->switchToUser('other');

        $request = $this->post(
            '/collectables/' . $collectable->id . '/tags',
            ['tags' => [$tag->id]],
            ['id' => $collectable->id],
        );
        $response = $this->controller->associateTag($request);

        $this->assertSame(403, $response->status);
    }

    // ── Shop listing ──

    public function testGetShopCollectables(): void
    {
        $shop = $this->shops->findAll()[0];
        $this->createCollectable();
        $this->createCollectable();

        $request = new Request(method: 'GET', uri: '/shops/' . $shop->id . '/collectables', params: [
            'shopId' => $shop->id,
        ]);
        $response = $this->controller->getShopCollectables($request);

        $this->assertSame(200, $response->status);
        $this->assertCount(2, $response->body['data']);
    }

    public function testGetShopCollectablesReturns404ForUnknownShop(): void
    {
        $request = new Request(method: 'GET', uri: '/shops/unknown-shop/collectables', params: [
            'shopId' => 'unknown-shop',
        ]);
        $response = $this->controller->getShopCollectables($request);

        $this->assertSame(404, $response->status);
    }

    // ── Helpers ──

    private function post(string $uri, array $body, array $params = []): Request
    {
        return new Request(method: 'POST', uri: $uri, body: json_encode($body, JSON_THROW_ON_ERROR), params: $params);
    }

    private function put(string $uri, array $body, array $params = []): Request
    {
        return new Request(method: 'PUT', uri: $uri, body: json_encode($body, JSON_THROW_ON_ERROR), params: $params);
    }

    private function createCollectable(): Collectable
    {
        $request = $this->post('/collectables', [
            'name' => 'Test Collectable ' . bin2hex(random_bytes(4)),
            'description' => 'A test collectable.',
            'price' => '9.99',
            'images' => [],
        ]);
        $response = $this->controller->create($request);
        $this->assertSame(201, $response->status);

        $created = $this->collectables->findById($response->body['data']['id']);
        $this->assertNotNull($created, 'Created collectable should be findable');
        return $created;
    }

    private function createTag(): Tag
    {
        $shop = $this->shops->findAll()[0];
        $tag = Tag::create(['name' => 'Test Tag ' . bin2hex(random_bytes(4)), 'shop' => $shop->id]);
        if (!is_object($tag)) {
            $this->fail('Could not create tag');
        }
        $this->tags->save($tag);
        return $tag;
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
}
