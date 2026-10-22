export interface CartItem {
	readonly id: string;
	readonly name: string;
	readonly price: number;
	readonly image?: string;
	quantity: number;
}

export interface Cart {
	items: CartItem[];
	add(item: Omit<CartItem, "quantity">, quantity?: number): CartItem;
	remove(id: string): void;
	setQuantity(id: string, quantity: number): void;
	clear(): void;
}

/** Pure helpers — no side effects, easy to unit test. */
export const cartCount = (items: CartItem[]): number =>
	items.reduce((total, item) => total + item.quantity, 0);

export const cartSubtotal = (items: CartItem[]): number =>
	items.reduce((total, item) => total + item.price * item.quantity, 0);
