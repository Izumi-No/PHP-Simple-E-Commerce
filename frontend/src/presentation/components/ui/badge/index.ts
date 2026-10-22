import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export type BadgeVariant =
	| "default"
	| "secondary"
	| "destructive"
	| "outline"
	| "success"
	| "warning";

const BASE =
	"inline-flex w-fit shrink-0 items-center justify-center gap-1 overflow-hidden rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] [&>svg]:pointer-events-none [&>svg]:size-3";

const VARIANTS: Record<BadgeVariant, string> = {
	default: "border-transparent bg-primary text-primary-foreground",
	secondary: "border-transparent bg-secondary text-secondary-foreground",
	destructive: "border-transparent bg-destructive text-destructive-foreground",
	outline: "text-foreground",
	success: "border-transparent bg-success text-success-foreground",
	warning: "border-transparent bg-warning text-warning-foreground",
};

export function badgeVariants(
	variant: BadgeVariant = "default",
	className?: string | null,
): string {
	return cn(BASE, VARIANTS[variant], className);
}

export class Badge extends BaseElement {
	static observedAttributes = ["variant"];

	protected render(): void {
		this.innerHTML = `<span data-slot="badge"><span data-content class="contents"></span></span>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const variant = this.getAttribute("variant") as BadgeVariant | null;
		const span = this.querySelector("[data-slot=badge]");
		if (span) {
			span.className = badgeVariants(
				variant && variant in VARIANTS ? variant : "default",
				this.getAttribute("class"),
			);
		}
	}
}

defineElement("z-badge", Badge);
