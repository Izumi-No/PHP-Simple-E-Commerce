import type { CartItem } from "../../domain/entities/cart";
import type { ICartStore } from "../../domain/ports/cart-store";

export class CartService {
	constructor(private readonly store: ICartStore) {}

	add(item: Omit<CartItem, "quantity">, quantity = 1): CartItem {
		return this.store.add(item, quantity);
	}

	remove(id: string): void {
		this.store.remove(id);
	}

	setQuantity(id: string, quantity: number): void {
		this.store.setQuantity(id, quantity);
	}

	clear(): void {
		this.store.clear();
	}

	get totalItems(): number {
		return this.store.count;
	}

	get totalPrice(): number {
		return this.store.subtotal;
	}
}
