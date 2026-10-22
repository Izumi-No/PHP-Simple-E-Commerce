/**
 * Presentation-facing re-export of the cart store. The real implementation
 * lives in `infrastructure/store/cart` and satisfies the `ICartStore` port.
 */

export type { CartItem } from "../../../../domain/entities/cart";
export type { ICartStore as CartStore } from "../../../../domain/ports/cart-store";
export { useCart } from "../../../../infrastructure/store/cart";
