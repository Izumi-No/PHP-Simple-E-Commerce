import type { CartItem } from "../entities/cart";

/** Driving port: the cart is client-side state, but the interface is explicit. */
export interface ICartStore {
	readonly items: CartItem[];
	readonly count: number;
	readonly subtotal: number;
	add(item: Omit<CartItem, "quantity">, quantity?: number): CartItem;
	remove(id: string): void;
	setQuantity(id: string, quantity: number): void;
	increment(id: string): void;
	decrement(id: string): void;
	clear(): void;
}
