<?php

declare(strict_types=1);

namespace Tests\Unit\Shops;

use Izumi\Backend\app\Models\Shop\Shop;
use Izumi\Backend\app\Models\Tag\Tag;
use Izumi\Backend\app\Shared\Errors\Error;
use PHPUnit\Framework\TestCase;

final class ShopModelTest extends TestCase
{
    public function testShopCreation(): void
    {
        $shop = Shop::create([
            'name' => 'Cool Collectibles',
            'banner' => 'https://example.com/banner.jpg',
            'owner' => 'user-id',
        ]);

        $this->assertInstanceOf(Shop::class, $shop);
        $this->assertSame('Cool Collectibles', $shop->name);
        $this->assertSame('https://example.com/banner.jpg', $shop->banner);
        $this->assertSame('user-id', $shop->owner);
    }

    public function testShopCreationWithoutBanner(): void
    {
        $shop = Shop::create([
            'name' => 'My Shop',
            'owner' => 'user-id',
        ]);

        $this->assertInstanceOf(Shop::class, $shop);
        $this->assertNull($shop->banner);
    }

    public function testShopCreationWithEmptyName(): void
    {
        $result = Shop::create([
            'name' => '',
            'owner' => 'user-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testShopCreationWithInvalidBanner(): void
    {
        $result = Shop::create([
            'name' => 'My Shop',
            'banner' => 'not-a-url',
            'owner' => 'user-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testShopReconstruction(): void
    {
        $original = Shop::create([
            'name' => 'My Shop',
            'banner' => 'https://example.com/banner.jpg',
            'owner' => 'user-id',
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create shop');
        }

        $reconstructed = Shop::fromArray([
            'id' => $original->id,
            'name' => $original->name,
            'banner' => $original->banner,
            'owner' => $original->owner,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->name, $reconstructed->name);
        $this->assertSame($original->banner, $reconstructed->banner);
    }

    public function testShopUpdate(): void
    {
        $shop = Shop::create([
            'name' => 'Old Name',
            'owner' => 'user-id',
        ]);

        if (!is_object($shop)) {
            $this->fail('Could not create shop');
        }

        $errors = $shop->update(['name' => 'New Name']);
        $this->assertSame([], $errors);
        $this->assertSame('New Name', $shop->name);
    }
}

final class TagModelTest extends TestCase
{
    public function testTagCreation(): void
    {
        $tag = Tag::create([
            'name' => 'Retro Cards',
            'shop' => 'shop-id',
        ]);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame('Retro Cards', $tag->name);
        $this->assertSame('shop-id', $tag->shop);
    }

    public function testTagCreationWithEmptyName(): void
    {
        $result = Tag::create([
            'name' => '',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testTagReconstruction(): void
    {
        $original = Tag::create([
            'name' => 'Retro Cards',
            'shop' => 'shop-id',
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create tag');
        }

        $reconstructed = Tag::fromArray([
            'id' => $original->id,
            'name' => $original->name,
            'shop' => $original->shop,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->name, $reconstructed->name);
    }
}
