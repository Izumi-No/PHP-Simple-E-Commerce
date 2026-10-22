import {
	type CartItem,
	cartCount,
	cartSubtotal,
} from "../../domain/entities/cart";
import type { ICartStore } from "../../domain/ports/cart-store";
import { Alpine, registerAlpineStore } from "../../presentation/lib/alpine";

/**
 * Alpine-driven cart implementation (client-side, keyed by "cart" so templates
 * can write `$store.cart.*`). Implements the ICartStore port so the domain and
 * application layers never import Alpine.
 */
const cartStoreImpl = {
	items: [] as CartItem[],

	get count(): number {
		return cartCount(this.items);
	},

	get subtotal(): number {
		return cartSubtotal(this.items);
	},

	add(item: Omit<CartItem, "quantity">, quantity = 1): CartItem {
		const existing = this.items.find((entry) => entry.id === item.id);
		if (existing) {
			existing.quantity += quantity;
			return existing;
		}
		const created: CartItem = { ...item, quantity };
		this.items.push(created);
		return created;
	},

	remove(id: string): void {
		this.items = this.items.filter((item) => item.id !== id);
	},

	setQuantity(id: string, quantity: number): void {
		const item = this.items.find((entry) => entry.id === id);
		if (!item) return;
		if (quantity <= 0) this.remove(id);
		else item.quantity = quantity;
	},

	increment(id: string): void {
		const item = this.items.find((entry) => entry.id === id);
		if (item) item.quantity += 1;
	},

	decrement(id: string): void {
		const item = this.items.find((entry) => entry.id === id);
		if (!item) return;
		if (item.quantity <= 1) this.remove(id);
		else item.quantity -= 1;
	},

	clear(): void {
		this.items = [];
	},
};

registerAlpineStore("cart", cartStoreImpl);

/** Reactive accessor for code that is outside Alpine templates. */
export function useCart(): ICartStore {
	return Alpine.store("cart") as unknown as ICartStore;
}
