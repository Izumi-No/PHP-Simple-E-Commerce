<?php

namespace Izumi\Backend\app\Controllers\Shops;

use Izumi\Backend\app\DTOs\Shop\ShopCreateDTO;
use Izumi\Backend\app\DTOs\Shop\ShopResponseDTO;
use Izumi\Backend\app\DTOs\Tag\TagCreateDTO;
use Izumi\Backend\app\DTOs\Tag\TagResponseDTO;
use Izumi\Backend\app\Models\Shop\Shop;
use Izumi\Backend\app\Models\Tag\Tag;
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

final class ShopsController extends Controller
{
    public function __construct(
        private readonly IShopsRepository $shops,
        private readonly ITagsRepository $tags,
        private readonly SessionResolver $resolver,
    ) {}

    #[ApiOperation(summary: 'List all shops', tags: ['shops'])]
    #[ApiResponse(status: 200, description: 'List of shops', dto: ShopResponseDTO::class)]
    public function getAll(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $shops = $this->shops->findAll();

        return $this->response([
            'data' => array_map(static fn(Shop $shop): array => $shop->toArray(), $shops),
        ]);
    }

    #[ApiOperation(summary: 'Create a shop for the authenticated user', request: ShopCreateDTO::class, tags: ['shops'])]
    #[ApiResponse(status: 201, description: 'Shop created', dto: ShopResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'User already owns a shop')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function create(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $existing = $this->shops->findByOwner($user->id);
        if ($existing !== null) {
            return $this->response(['error' => 'User already owns a shop'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(ShopCreateDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        $shop = Shop::create([
            'name' => $dto->name,
            'banner' => $dto->banner,
            'owner' => $user->id,
        ]);

        if (is_array($shop)) {
            return $this->response(['errors' => self::mapErrors($shop)], 422);
        }

        $this->shops->save($shop);

        return $this->response([
            'message' => 'Shop created successfully',
            'data' => $shop->toArray(),
        ], 201);
    }

    #[ApiOperation(summary: 'Create a tag in a shop (owner only)', request: TagCreateDTO::class, tags: ['shops'])]
    #[ApiResponse(status: 201, description: 'Tag created', dto: TagResponseDTO::class)]
    #[ApiResponse(status: 400, description: 'Invalid request body')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the shop owner')]
    #[ApiResponse(status: 404, description: 'Shop not found')]
    #[ApiResponse(status: 422, description: 'Validation error or duplicate tag name')]
    public function createTag(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $shopId = $request->params['shopId'] ?? '';
        if ($shopId === '') {
            return $this->response(['error' => 'Shop ID is required'], 400);
        }

        $shop = $this->shops->findById($shopId);
        if ($shop === null) {
            return $this->response(['error' => 'Shop not found'], 404);
        }

        if ($shop->owner !== $user->id) {
            return $this->response(['error' => 'Only the shop owner can create tags'], 403);
        }

        if ($request->body === '') {
            return $this->response(['error' => 'Request body is empty'], 400);
        }

        try {
            $result = $request->validate(TagCreateDTO::class);
        } catch (JsonException) {
            return $this->response(['error' => 'Invalid JSON format'], 400);
        }

        if ($result->isInvalid()) {
            return $this->response(['errors' => self::mapValidationErrors($result->errors)], 422);
        }

        $dto = $result->value();

        // Check for duplicate tag name within the shop
        $existingTags = $this->tags->findByShop($shopId);
        foreach ($existingTags as $tag) {
            if (strtolower($tag->name) === strtolower($dto->name)) {
                return $this->response(['error' => 'A tag with this name already exists in the shop'], 422);
            }
        }

        $tag = Tag::create([
            'name' => $dto->name,
            'shop' => $shopId,
        ]);

        if (is_array($tag)) {
            return $this->response(['errors' => self::mapErrors($tag)], 422);
        }

        $this->tags->save($tag);

        return $this->response([
            'message' => 'Tag created successfully',
            'data' => $tag->toArray(),
        ], 201);
    }

    #[ApiOperation(summary: 'Delete a tag from a shop (owner only)', tags: ['shops'])]
    #[ApiResponse(status: 200, description: 'Tag deleted')]
    #[ApiResponse(status: 400, description: 'Missing tag ID')]
    #[ApiResponse(status: 401, description: 'Not authenticated')]
    #[ApiResponse(status: 403, description: 'Not the shop owner')]
    #[ApiResponse(status: 404, description: 'Shop or tag not found')]
    public function deleteTag(Request $request): Response
    {
        $user = $this->resolveUser($request);
        if ($user === null) {
            return $this->response(['error' => 'Not authenticated'], 401);
        }

        $shopId = $request->params['shopId'] ?? '';
        $tagId = $request->params['tagId'] ?? '';

        if ($shopId === '' || $tagId === '') {
            return $this->response(['error' => 'Shop ID and Tag ID are required'], 400);
        }

        $shop = $this->shops->findById($shopId);
        if ($shop === null) {
            return $this->response(['error' => 'Shop not found'], 404);
        }

        if ($shop->owner !== $user->id) {
            return $this->response(['error' => 'Only the shop owner can delete tags'], 403);
        }

        $tag = $this->tags->findById($tagId);
        if ($tag === null || $tag->shop !== $shopId) {
            return $this->response(['error' => 'Tag not found'], 404);
        }

        $this->tags->delete($tagId);

        return $this->response(['message' => 'Tag deleted successfully']);
    }

    private function resolveUser(Request $request): ?\Izumi\Backend\app\Models\Auth\User
    {
        $cookieValue = $_COOKIE[SessionResolver::COOKIE_NAME] ?? null;
        return $this->resolver->resolve($cookieValue);
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
