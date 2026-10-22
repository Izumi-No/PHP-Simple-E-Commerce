/**
 * Domain entity. Matches the backend `ProductResponseDTO` contract, but the
 * domain is free to evolve independently of the wire shape (the HTTP adapter
 * maps `image_url` → `image`).
 */
export interface Product {
	readonly id: string;
	readonly name: string;
	readonly price: number;
	readonly description: string;
	readonly stock: number;
	readonly image: string;
	readonly rating?: number;
	readonly badge?: string;
}

export const isProduct = (value: unknown): value is Product => {
	if (typeof value !== "object" || value === null) return false;
	const p = value as Record<string, unknown>;
	return (
		typeof p.id === "string" &&
		typeof p.name === "string" &&
		typeof p.price === "number"
	);
};

/** DTO shape coming over the wire. */
export interface ProductDTO {
	id: string;
	name: string;
	price: number;
	description: string;
	quantity: number;
	image_url: string;
}

export const productFromDTO = (dto: ProductDTO): Product => ({
	id: dto.id,
	name: dto.name,
	price: dto.price,
	description: dto.description,
	stock: dto.quantity,
	image: dto.image_url,
});
