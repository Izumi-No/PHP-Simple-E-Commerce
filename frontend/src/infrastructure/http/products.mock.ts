import type { Product } from "../../domain/entities/product";
import type { IProductRepository } from "../../domain/ports/product-repository";

const PRODUCTS: Product[] = [
	{
		id: "kirby",
		name: "kirby plush bastão macio",
		description: "maciez que abraça, do jeitinho do kirby.",
		price: 129.9,
		stock: 6,
		image: "https://picsum.photos/seed/kirby-blush/600/600",
		rating: 4.9,
		badge: "💕 muito amado",
	},
	{
		id: "labubu",
		name: "labubu chaveiro ed. passarinhada",
		description: "pálido e beijoqueiro, pronto pro bolso.",
		price: 149,
		stock: 4,
		image: "https://picsum.photos/seed/labubu/600/600",
		rating: 4.8,
	},
	{
		id: "snorlax",
		name: "snorlax figura pelúcia azul",
		description: "o bastão mais fofo da estante.",
		price: 319.9,
		stock: 2,
		image: "https://picsum.photos/seed/snorlax/600/600",
		rating: 5,
	},
	{
		id: "sunny",
		name: "sunny coelhinho rosa",
		description: "orelhas grandes, olhos doce-azeite.",
		price: 89,
		stock: 9,
		image: "https://picsum.photos/seed/pink-bunny/600/600",
		rating: 4.7,
	},
	{
		id: "miffy",
		name: "miffy na chuva",
		description: "capinha de chuva amarelinha.",
		price: 89.9,
		stock: 7,
		image: "https://picsum.photos/seed/rain-miffy/600/600",
		rating: 4.6,
	},
	{
		id: "cinnamoroll",
		name: "cinnamoroll art toy creme pastel",
		description: "orelhas que disparam o sorriso.",
		price: 239,
		stock: 3,
		image: "https://picsum.photos/seed/cinnamoroll/600/600",
		rating: 4.9,
		badge: "🍓 edição limitada",
	},
];

/** Fallback adapter while the backend is offline. */
export class MockProductsRepository implements IProductRepository {
	async list(): Promise<Product[]> {
		return PRODUCTS;
	}

	async getById(id: string): Promise<Product> {
		const found = PRODUCTS.find((p) => p.id === id);
		if (!found) throw new Error(`product ${id} not found`);
		return found;
	}
}
