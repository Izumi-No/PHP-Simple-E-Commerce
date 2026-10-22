import { productService } from "../composition";
import type { Product } from "../domain/entities/product";
import type { IProductRepository } from "../domain/ports/product-repository";
import { MockProductsRepository } from "../infrastructure/http/products.mock";

/**
 * Home bootstrap: loads products through the composition root. If the API is
 * unreachable, falls back to `MockProductsRepository` so the page never breaks.
 */
export async function loadProducts(): Promise<void> {
	const grid = document.getElementById("products-grid");
	if (!grid) return;

	let repo: IProductRepository;
	try {
		const products = await productService.listProducts();
		render(grid, products);
		return;
	} catch {
		repo = new MockProductsRepository();
	}
	render(grid, await repo.list());
}

function render(grid: HTMLElement, products: Product[]): void {
	grid.innerHTML = "";

	for (const product of products) {
		const cell = document.createElement("div");
		cell.dataset.searchable = (
			product.name +
			" " +
			product.description
		).toLowerCase();

		const card = document.createElement("z-product-card");
		card.setAttribute("id", product.id);
		card.setAttribute("name", product.name);
		card.setAttribute("description", product.description);
		card.setAttribute("price", String(product.price));
		card.setAttribute("image", product.image);
		card.setAttribute("stock", String(product.stock));
		if (product.rating) card.setAttribute("rating", String(product.rating));
		if (product.badge) card.setAttribute("badge", product.badge);

		cell.appendChild(card);
		grid.appendChild(cell);
	}

	// search filter (works regardless of what's wired in Alpine)
	const input = document.querySelector<HTMLInputElement>(
		"#products-search, [data-search-input]",
	);
	if (input) {
		input.addEventListener("input", () => {
			const query = input.value.trim().toLowerCase();
			for (const cell of grid.children) {
				const text = (cell as HTMLElement).dataset.searchable ?? "";
				(cell as HTMLElement).style.display =
					query === "" || text.includes(query) ? "" : "none";
			}
		});
	}
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", loadProducts, { once: true });
} else {
	loadProducts();
}
