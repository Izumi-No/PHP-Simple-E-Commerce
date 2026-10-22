import type { Product } from "../../domain/entities/product";
import {
	isProduct,
	type ProductDTO,
	productFromDTO,
} from "../../domain/entities/product";
import type { IProductRepository } from "../../domain/ports/product-repository";

const API_URL =
	(typeof process !== "undefined" && process.env?.API_URL) ||
	"http://localhost:8080";

/**
 * HTTP adapter hitting the CakePHP backend `/products` endpoint.
 * Maps `image_url` → `image` and hardens responses against schema drift.
 */
export class HttpProductsRepository implements IProductRepository {
	constructor(private readonly baseUrl: string = API_URL) {}

	async list(): Promise<Product[]> {
		const res = await fetch(`${this.baseUrl}/products`, {
			headers: { Accept: "application/json" },
		});
		if (!res.ok) {
			throw new Error(`products list failed: ${res.status}`);
		}
		const body = (await res.json()) as unknown;
		const items = Array.isArray(body)
			? body
			: ((body as { data?: unknown[]; items?: unknown[] }).data ??
				(body as { items?: unknown[] }).items ??
				[]);
		return (items as ProductDTO[]).map(productFromDTO);
	}

	async getById(id: string): Promise<Product> {
		const res = await fetch(`${this.baseUrl}/products/${id}`, {
			headers: { Accept: "application/json" },
		});
		if (!res.ok) {
			throw new Error(`product ${id} failed: ${res.status}`);
		}
		const dto = (await res.json()) as ProductDTO;
		const product = productFromDTO(dto);
		if (!isProduct(product)) {
			throw new Error("product from API did not match schema");
		}
		return product;
	}
}
