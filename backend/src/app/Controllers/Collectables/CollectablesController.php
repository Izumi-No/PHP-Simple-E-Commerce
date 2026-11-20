<?php

declare(strict_types=1);

namespace Izumi\Backend\app\Controllers\Collectables;

use Izumi\Backend\app\DTOs\Collectable\CollectableRequestDTO;
use Izumi\Backend\app\DTOs\Collectable\CollectableResponseDTO;
use Izumi\Backend\app\DTOs\Collectable\TagAssociationRequestDTO;
use Izumi\Backend\app\Models\Collectable\Collectable;
use Izumi\Backend\app\Models\CollectableTag\CollectableTag;
use Izumi\Backend\app\Repositories\Collectables\ICollectablesRepository;
use Izumi\Backend\app\Repositories\CollectableTags\ICollectableTagsRepository;
use Izumi\Backend\app\Repositories\Shops\IShopsRepository;
use Izumi\Backend\app\Repositories\Tags\ITagsRepository;
use Izumi\Backend\app\Shared\Attributes\ApiOperation;
use Izumi\Backend\app\Shared\Attributes\ApiResponse;
use Izumi\Backend\app\Shared\Auth\SessionResolver;
use Izumi\Backend\app\Shared\Controller;
use Izumi\Backend\app\Shared\Errors\Error;
use Izumi\Backend\app\Shared\Errors\ValidationError;
use Izumi\Backend\app\Shared\Request;
use Izumi\Backend\app\Shared\Response;
use JsonException;

final class CollectablesController extends Controller
{
    public function __construct(
        private readonly ICollectablesRepository $collectables,
        private readonly ICollectableTagsRepository $collectableTags,
        private readonly IShopsRepository $shops,
        private readonly ITagsRepository $tags,
        private readonly SessionResolver $resolver,
    ) {}

    #[ApiOperation(summary: 'List all collectables with tags', tags: ['collectables'])]
    #[ApiResponse(status: 200, description: 'List of collectables with tags', dto: CollectableResponseDTO::class)]
    public function getAll(Request $request): Response
    {
        $collectables = $this->collectables->findAll();
        $items = array_map(fn(Collectable $c): array => $this->toResponseArray($c), $collectables);

        return $this->response(['data' => $items]);
    }

    #[ApiOperation(summary: 'Get a collectable by ID with tags', tags: ['collectables'])]
    #[ApiResponse(status: 200, description: 'Collectable with tags', dto: CollectableResponseDTO::class)]
    #[ApiResponse(status: 404, description: 'Collectable not found')]
    public function getById(Request $request): Response
    {
        $collectableId = $request->params['id'] ?? '';
        $collectable = $this->collectables->findById($collectableId);

        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        return $this->response(['data' => $this->toResponseArray($collectable)]);
    }

    #[ApiOperation(
        summary: 'Create a collectable in the authenticated user\'s shop',
        request: CollectableRequestDTO::class,
        tags: ['collectables'],
    )]
    #[ApiResponse(status: 201, description: 'Collectable created', dto: CollectableResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'User owns no shop')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function create(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $shop = $this->shops->findByOwner($user->id);
        if ($shop === null) {
            return $this->response(['error' => 'User owns no shop'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(CollectableRequestDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        $collectable = Collectable::create([
            'name' => $dto->name,
            'description' => $dto->description,
            'images' => $dto->images,
            'price' => $dto->price,
            'shop' => $shop->id,
        ]);

        if (is_array($collectable)) {
            return $this->response(['errors' => self::mapErrors($collectable)], 422);
        }

        $this->collectables->save($collectable);

        return $this->response([
            'message' => 'Collectable created successfully',
            'data' => $this->toResponseArray($collectable),
        ], 201);
    }

    #[ApiOperation(
        summary: 'Update a collectable (owner only)',
        request: CollectableRequestDTO::class,
        tags: ['collectables'],
    )]
    #[ApiResponse(status: 200, description: 'Collectable updated', dto: CollectableResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the owner')]
    #[ApiResponse(status: 404, description: 'Collectable not found')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function update(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $collectableId = $request->params['id'] ?? '';
        $collectable = $this->collectables->findById($collectableId);
        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        $shop = $this->shops->findById($collectable->shop);
        if ($shop === null || $shop->owner !== $user->id) {
            return $this->response(['error' => 'Not the owner'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(CollectableRequestDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        $this->collectables->update($collectableId, [
            'name' => $dto->name,
            'description' => $dto->description,
            'images' => $dto->images,
            'price' => $dto->price,
        ]);

        $updated = $this->collectables->findById($collectableId);
        if ($updated === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        return $this->response([
            'message' => 'Collectable updated successfully',
            'data' => $this->toResponseArray($updated),
        ]);
    }

    #[ApiOperation(summary: 'Soft-delete a collectable (owner only)', tags: ['collectables'])]
    #[ApiResponse(status: 200, description: 'Collectable deleted')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the owner')]
    #[ApiResponse(status: 404, description: 'Collectable not found')]
    public function delete(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $collectableId = $request->params['id'] ?? '';
        $collectable = $this->collectables->findById($collectableId);
        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        $shop = $this->shops->findById($collectable->shop);
        if ($shop === null || $shop->owner !== $user->id) {
            return $this->response(['error' => 'Not the owner'], 403);
        }

        $this->collectables->delete($collectableId);

        return $this->response(['message' => 'Collectable deleted successfully']);
    }

    // ── Tag associations ──

    #[ApiOperation(
        summary: 'Associate tags with a collectable (owner only)',
        request: TagAssociationRequestDTO::class,
        tags: ['collectables'],
    )]
    #[ApiResponse(status: 200, description: 'Tags associated')]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the owner')]
    #[ApiResponse(status: 404, description: 'Collectable not found')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function associateTag(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $collectableId = $request->params['id'] ?? '';
        $collectable = $this->collectables->findById($collectableId);
        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        $shop = $this->shops->findById($collectable->shop);
        if ($shop === null || $shop->owner !== $user->id) {
            return $this->response(['error' => 'Not the owner'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(TagAssociationRequestDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        $this->replaceCollectableTags($collectableId, $dto->tags);

        return $this->response(['message' => 'Tags associated successfully']);
    }

    #[ApiOperation(
        summary: 'Replace the full tag set of a collectable (owner only)',
        request: TagAssociationRequestDTO::class,
        tags: ['collectables'],
    )]
    #[ApiResponse(status: 200, description: 'Tag set replaced')]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the owner')]
    #[ApiResponse(status: 404, description: 'Collectable not found')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function replaceTags(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $collectableId = $request->params['id'] ?? '';
        $collectable = $this->collectables->findById($collectableId);
        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        $shop = $this->shops->findById($collectable->shop);
        if ($shop === null || $shop->owner !== $user->id) {
            return $this->response(['error' => 'Not the owner'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(TagAssociationRequestDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        $this->replaceCollectableTags($collectableId, $dto->tags);

        return $this->response(['message' => 'Tags replaced successfully']);
    }

    #[ApiOperation(summary: 'Remove a tag association from a collectable (owner only)', tags: ['collectables'])]
    #[ApiResponse(status: 200, description: 'Tag removed')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the owner')]
    #[ApiResponse(status: 404, description: 'Collectable or tag not found')]
    public function removeTag(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $collectableId = $request->params['id'] ?? '';
        $tagId = $request->params['tagId'] ?? '';

        $collectable = $this->collectables->findById($collectableId);
        if ($collectable === null) {
            return $this->response(['error' => 'Collectable not found'], 404);
        }

        $shop = $this->shops->findById($collectable->shop);
        if ($shop === null || $shop->owner !== $user->id) {
            return $this->response(['error' => 'Not the owner'], 403);
        }

        $tag = $this->tags->findById($tagId);
        if ($tag === null) {
            return $this->response(['error' => 'Tag not found'], 404);
        }

        $existingAssociations = $this->collectableTags->findByCollectable($collectableId);
        $association = null;
        foreach ($existingAssociations as $assoc) {
            if ($assoc->tag === $tagId) {
                $association = $assoc;
                break;
            }
        }

        if ($association === null) {
            return $this->response(['error' => 'Tag association not found'], 404);
        }

        $this->collectableTags->delete($association->id);

        return $this->response(['message' => 'Tag removed successfully']);
    }

    #[ApiOperation(summary: 'List collectables for a shop', tags: ['collectables'])]
    #[ApiResponse(status: 200, description: 'List of collectables for the shop', dto: CollectableResponseDTO::class)]
    #[ApiResponse(status: 404, description: 'Shop not found')]
    public function getShopCollectables(Request $request): Response
    {
        $shopId = $request->params['shopId'] ?? '';
        $shop = $this->shops->findById($shopId);
        if ($shop === null) {
            return $this->response(['error' => 'Shop not found'], 404);
        }

        $collectables = $this->collectables->findByShop($shopId);
        $items = array_map(fn(Collectable $c): array => $this->toResponseArray($c), $collectables);

        return $this->response(['data' => $items]);
    }

    // ── Private helpers ──

    /**
     * Replace all tag associations for a collectable with the given tag IDs.
     *
     * @param list<string> $tagIds
     */
    private function replaceCollectableTags(string $collectableId, array $tagIds): void
    {
        $existing = $this->collectableTags->findByCollectable($collectableId);
        foreach ($existing as $assoc) {
            $this->collectableTags->delete($assoc->id);
        }

        foreach ($tagIds as $tagId) {
            if (!is_string($tagId) || $tagId === '') {
                continue;
            }
            $tag = $this->tags->findById($tagId);
            if ($tag === null) {
                continue;
            }
            $assoc = CollectableTag::create([
                'collectable' => $collectableId,
                'tag' => $tagId,
            ]);
            if (is_object($assoc)) {
                $this->collectableTags->save($assoc);
            }
        }
    }

    private function resolveUser(Request $request): ?\Izumi\Backend\app\Models\Auth\User
    {
        $cookieValue = $_COOKIE[SessionResolver::COOKIE_NAME] ?? null;
        return $this->resolver->resolve($cookieValue);
    }

    private function toResponseArray(Collectable $collectable): array
    {
        $associations = $this->collectableTags->findByCollectable($collectable->id);
        $tagNames = [];
        foreach ($associations as $assoc) {
            $tag = $this->tags->findById($assoc->tag);
            if ($tag !== null) {
                $tagNames[] = $tag->name;
            }
        }

        return [
            'id' => $collectable->id,
            'name' => $collectable->name,
            'description' => $collectable->description,
            'images' => $collectable->images,
            'price' => $collectable->price,
            'shop' => $collectable->shop,
            'tags' => $tagNames,
        ];
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
