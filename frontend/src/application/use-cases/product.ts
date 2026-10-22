import type { Product } from "../../domain/entities/product";
import type { IProductRepository } from "../../domain/ports/product-repository";

/** Use-cases depend on ports, never on infrastructure. */
export class ProductService {
	constructor(private readonly products: IProductRepository) {}

	listProducts(): Promise<Product[]> {
		return this.products.list();
	}

	getProduct(id: string): Promise<Product> {
		return this.products.getById(id);
	}

	search(query: string, products: Product[]): Product[] {
		const q = query.trim().toLowerCase();
		if (!q) return products;
		return products.filter(
			(p) =>
				p.name.toLowerCase().includes(q) ||
				p.description.toLowerCase().includes(q),
		);
	}

	sort(
		products: Product[],
		by: "recent" | "price-asc" | "price-desc",
	): Product[] {
		const items = [...products];
		if (by === "price-asc") items.sort((a, b) => a.price - b.price);
		if (by === "price-desc") items.sort((a, b) => b.price - a.price);
		return items;
	}
}
