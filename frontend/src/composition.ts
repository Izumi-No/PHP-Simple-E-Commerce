import { CartService } from "./application/use-cases/cart";
import { ProductService } from "./application/use-cases/product";
import { MockProductsRepository } from "./infrastructure/http/products.mock";
import { HttpProductsRepository } from "./infrastructure/http/products.repository";
import { useCart } from "./infrastructure/store/cart";
import { themeStore } from "./infrastructure/store/theme";

/**
 * Composition root: wires ports → adapters. Presentation only depends on the
 * application services exposed here.
 */

const repository =
	typeof process !== "undefined" && process.env?.USE_MOCK === "1"
		? new MockProductsRepository()
		: new HttpProductsRepository();

export const productService = new ProductService(repository);
export const cartService = new CartService(useCart());
export const theme = themeStore;
