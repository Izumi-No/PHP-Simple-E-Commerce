import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { cn, escapeHtml, formatCurrency } from "../../../lib/utils";
import { useCart } from "../cart/index";

export interface ProductCardDetail {
	id: string;
	name: string;
	price: number;
	image?: string;
}

export class ProductCard extends BaseElement {
	static observedAttributes = [
		"id",
		"name",
		"price",
		"image",
		"description",
		"stock",
		"rating",
		"href",
		"badge",
		"compare-at",
	];

	protected render(): void {
		const name = this.getAttribute("name") ?? "";
		const description = this.getAttribute("description") ?? "";
		const image = this.getAttribute("image") ?? "";
		const href = this.getAttribute("href") ?? "#";
		const price = Number(this.getAttribute("price") ?? 0);
		const compareAt = this.getAttribute("compare-at");
		const stock = Number(this.getAttribute("stock") ?? 0);
		const rating = this.getAttribute("rating");
		const badge = this.getAttribute("badge");

		const priceHtml = `<span class="text-base font-semibold">${escapeHtml(
			formatCurrency(price),
		)}</span>${
			compareAt
				? `<span class="text-sm text-muted-foreground line-through">${escapeHtml(
						formatCurrency(Number(compareAt)),
					)}</span>`
				: ""
		}`;

		const imageHtml = image
			? `<img data-slot="product-image" src="${escapeHtml(image)}" alt="${escapeHtml(
					name,
				)}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />`
			: `<span class="grid h-full w-full place-items-center text-muted-foreground">${icon(
					"image-off",
					{ class: "size-8", strokeWidth: 1.5 },
				)}</span>`;

		const soldOut = stock <= 0;

		this.innerHTML = `
			<article data-slot="product-card" class="group flex flex-col overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-shadow hover:shadow-md">
				<a href="${escapeHtml(href)}" class="relative block aspect-square overflow-hidden bg-muted">
					${imageHtml}
					${
						badge
							? `<span class="absolute top-2 left-2 rounded-md bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground">${escapeHtml(
									badge,
								)}</span>`
							: ""
					}
					${
						soldOut
							? `<span class="absolute inset-0 grid place-items-center bg-background/70 text-sm font-medium">Esgotado</span>`
							: ""
					}
				</a>
				<div class="flex flex-1 flex-col gap-2 p-4">
					<h3 class="line-clamp-2 leading-tight font-medium">${escapeHtml(name)}</h3>
					${
						description
							? `<p class="line-clamp-2 text-sm text-muted-foreground">${escapeHtml(
									description,
								)}</p>`
							: ""
					}
					${
						rating
							? `<z-rating value="${escapeHtml(rating)}" show-value></z-rating>`
							: ""
					}
					<div class="mt-auto flex items-center justify-between gap-2 pt-2">
						<span class="inline-flex items-baseline gap-2">${priceHtml}</span>
						<button type="button" data-add-to-cart ${soldOut ? "disabled" : ""}
							aria-label="Adicionar ao carrinho"
							class="grid size-9 place-items-center rounded-md bg-primary text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50">
							${icon("plus", { class: "size-4" })}
						</button>
					</div>
					<span data-content class="contents"></span>
				</div>
			</article>`;

		this.querySelector("[data-add-to-cart]")?.addEventListener("click", () => {
			this.addToCart();
		});
	}

	private addToCart(): void {
		const detail: ProductCardDetail = {
			id: this.getAttribute("id") ?? "",
			name: this.getAttribute("name") ?? "",
			price: Number(this.getAttribute("price") ?? 0),
			image: this.getAttribute("image") ?? undefined,
		};

		useCart().add(detail);
		this.dispatchEvent(
			new CustomEvent<ProductCardDetail>("add-to-cart", {
				detail,
				bubbles: true,
			}),
		);
	}
}

export const productCardClass = cn(
	"group flex flex-col overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm",
);

defineElement("z-product-card", ProductCard);
