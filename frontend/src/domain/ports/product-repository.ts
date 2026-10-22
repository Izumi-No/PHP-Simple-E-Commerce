import type { Product } from "../entities/product";

/** Driven port: how the application reads products. */
export interface IProductRepository {
	list(): Promise<Product[]>;
	getById(id: string): Promise<Product>;
}
