import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export type ButtonVariant =
	| "default"
	| "destructive"
	| "outline"
	| "secondary"
	| "ghost"
	| "link";

export type ButtonSize = "default" | "sm" | "lg" | "icon" | "icon-sm";

const BASE =
	"inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all outline-none shrink-0 disabled:pointer-events-none disabled:opacity-50 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4";

const VARIANTS: Record<ButtonVariant, string> = {
	default: "bg-primary text-primary-foreground shadow-xs hover:bg-primary/90",
	destructive:
		"bg-destructive text-destructive-foreground shadow-xs hover:bg-destructive/90",
	outline:
		"border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground",
	secondary:
		"bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80",
	ghost: "hover:bg-accent hover:text-accent-foreground",
	link: "text-primary underline-offset-4 hover:underline",
};

const SIZES: Record<ButtonSize, string> = {
	default: "h-9 px-4 py-2 has-[>svg]:px-3",
	sm: "h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5",
	lg: "h-10 rounded-md px-6 has-[>svg]:px-4",
	icon: "size-9",
	"icon-sm": "size-8",
};

export interface ButtonVariantOptions {
	variant?: ButtonVariant;
	size?: ButtonSize;
	class?: string | null;
}

export function buttonVariants(options: ButtonVariantOptions = {}): string {
	return cn(
		BASE,
		VARIANTS[options.variant ?? "default"],
		SIZES[options.size ?? "default"],
		options.class,
	);
}

export class Button extends BaseElement {
	static observedAttributes = [
		"variant",
		"size",
		"type",
		"disabled",
		"href",
		"target",
		"rel",
	];

	private control: HTMLButtonElement | HTMLAnchorElement | null = null;

	protected render(): void {
		const href = this.getAttribute("href");
		const tag = href ? "a" : "button";

		this.innerHTML = `<${tag} data-slot="button"><span data-content class="contents"></span></${tag}>`;

		this.control = this.querySelector("[data-slot=button]") as
			| HTMLButtonElement
			| HTMLAnchorElement;

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const control = this.control;
		if (!control) return;

		control.className = buttonVariants({
			variant: this.variant,
			size: this.size,
			class: this.getAttribute("class"),
		});

		if (control instanceof HTMLButtonElement) {
			control.type =
				this.getAttribute("type") === "submit" ? "submit" : "button";
			control.disabled = this.hasAttribute("disabled");
		} else {
			const href = this.getAttribute("href");
			if (href) control.href = href;
			const target = this.getAttribute("target");
			if (target) control.target = target;
			const rel = this.getAttribute("rel");
			if (rel) control.rel = rel;
			if (this.hasAttribute("disabled")) {
				control.setAttribute("aria-disabled", "true");
			} else {
				control.removeAttribute("aria-disabled");
			}
		}

		this.setAttribute("data-variant", this.variant);
		this.setAttribute("data-size", this.size);
	}

	private get variant(): ButtonVariant {
		const value = this.getAttribute("variant") as ButtonVariant | null;
		return value && value in VARIANTS ? value : "default";
	}

	private get size(): ButtonSize {
		const value = this.getAttribute("size") as ButtonSize | null;
		return value && value in SIZES ? value : "default";
	}
}

defineElement("z-button", Button);
