<?php

declare(strict_types=1);

namespace Tests\Unit\Collectables;

use Izumi\Backend\app\Models\Collectable\Collectable;
use Izumi\Backend\app\Models\CollectableTag\CollectableTag;
use Izumi\Backend\app\Shared\Errors\Error;
use PHPUnit\Framework\TestCase;

final class CollectableModelTest extends TestCase
{
    public function testCollectableCreation(): void
    {
        $c = Collectable::create([
            'name' => 'Rare Card',
            'description' => 'A rare trading card.',
            'images' => ['https://example.com/card-front.jpg'],
            'price' => '49.90',
            'shop' => 'shop-id',
        ]);

        $this->assertInstanceOf(Collectable::class, $c);
        $this->assertSame('Rare Card', $c->name);
        $this->assertSame('A rare trading card.', $c->description);
        $this->assertSame(['https://example.com/card-front.jpg'], $c->images);
        $this->assertSame('49.90', $c->price);
        $this->assertSame('shop-id', $c->shop);
    }

    public function testPriceNormalizedToTwoDecimals(): void
    {
        $c = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => [],
            'price' => '49.9',
            'shop' => 'shop-id',
        ]);

        $this->assertInstanceOf(Collectable::class, $c);
        $this->assertSame('49.90', $c->price);
    }

    public function testPriceIntegerNormalized(): void
    {
        $c = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => [],
            'price' => '50',
            'shop' => 'shop-id',
        ]);

        $this->assertInstanceOf(Collectable::class, $c);
        $this->assertSame('50.00', $c->price);
    }

    public function testNegativePriceRejected(): void
    {
        $result = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => [],
            'price' => '-1.00',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Error::class, $result);
    }

    public function testPriceWithMoreThanTwoDecimalsRejected(): void
    {
        $result = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => [],
            'price' => '1.234',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testInvalidPriceStringRejected(): void
    {
        $result = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => [],
            'price' => 'abc',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testInvalidImageUrlRejected(): void
    {
        $result = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => ['not-a-url'],
            'price' => '1.00',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testEmptyNameRejected(): void
    {
        $result = Collectable::create([
            'name' => '',
            'description' => 'desc',
            'images' => [],
            'price' => '1.00',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testEmptyDescriptionRejected(): void
    {
        $result = Collectable::create([
            'name' => 'Card',
            'description' => '',
            'images' => [],
            'price' => '1.00',
            'shop' => 'shop-id',
        ]);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testReconstructionFromJsonImages(): void
    {
        $original = Collectable::create([
            'name' => 'Card',
            'description' => 'desc',
            'images' => ['https://example.com/a.jpg'],
            'price' => '49.90',
            'shop' => 'shop-id',
        ]);

        if (!is_object($original)) {
            $this->fail('Could not create collectable');
        }

        // Simulate a DB row where images is a JSON string
        $reconstructed = Collectable::fromArray([
            'id' => $original->id,
            'name' => $original->name,
            'description' => $original->description,
            'images' => json_encode($original->images),
            'price' => $original->price,
            'shop' => $original->shop,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame(['https://example.com/a.jpg'], $reconstructed->images);
        $this->assertSame('49.90', $reconstructed->price);
    }

    public function testUpdate(): void
    {
        $c = Collectable::create([
            'name' => 'Old Name',
            'description' => 'desc',
            'images' => [],
            'price' => '1.00',
            'shop' => 'shop-id',
        ]);

        if (!is_object($c)) {
            $this->fail('Could not create collectable');
        }

        $errors = $c->update(['name' => 'New Name', 'price' => '2.50']);
        $this->assertSame([], $errors);
        $this->assertSame('New Name', $c->name);
        $this->assertSame('2.50', $c->price);
    }
}

final class CollectableTagModelTest extends TestCase
{
    public function testCreation(): void
    {
        $ct = CollectableTag::create(['collectable' => 'c-id', 'tag' => 't-id']);

        $this->assertInstanceOf(CollectableTag::class, $ct);
        $this->assertSame('c-id', $ct->collectable);
        $this->assertSame('t-id', $ct->tag);
    }

    public function testEmptyCollectableRejected(): void
    {
        $result = CollectableTag::create(['collectable' => '', 'tag' => 't-id']);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testEmptyTagRejected(): void
    {
        $result = CollectableTag::create(['collectable' => 'c-id', 'tag' => '']);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testReconstruction(): void
    {
        $original = CollectableTag::create(['collectable' => 'c-id', 'tag' => 't-id']);

        if (!is_object($original)) {
            $this->fail('Could not create collectable tag');
        }

        $reconstructed = CollectableTag::fromArray([
            'id' => $original->id,
            'collectable' => $original->collectable,
            'tag' => $original->tag,
        ]);

        $this->assertSame($original->id, $reconstructed->id);
        $this->assertSame($original->collectable, $reconstructed->collectable);
    }
}
