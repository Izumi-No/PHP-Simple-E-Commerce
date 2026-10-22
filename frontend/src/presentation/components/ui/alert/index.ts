import {
	BaseElement,
	createStyledElement,
	defineElement,
} from "../../../lib/base";
import { type IconName, icon } from "../../../lib/icons";
import { cn } from "../../../lib/utils";

const BASE =
	"relative grid w-full grid-cols-[0_1fr] items-start gap-y-0.5 rounded-lg border px-4 py-3 text-sm has-[>svg]:grid-cols-[1rem_1fr] has-[>svg]:gap-x-3 [&>svg]:size-4 [&>svg]:translate-y-0.5";

const VARIANTS: Record<string, string> = {
	default: "bg-card text-card-foreground",
	destructive:
		"border-destructive/50 text-destructive [&>svg]:text-current *:data-[slot=alert-description]:text-destructive/90",
};

export const alertParts = {
	"alert-title": createStyledElement(
		"col-start-2 line-clamp-1 min-h-4 font-medium tracking-tight",
	),
	"alert-description": createStyledElement(
		"col-start-2 grid justify-items-start gap-1 text-sm text-muted-foreground [&_p]:leading-relaxed",
	),
};

export class Alert extends BaseElement {
	static observedAttributes = ["variant", "icon"];

	protected render(): void {
		const iconName = this.getAttribute("icon");
		const iconHtml = iconName
			? icon(iconName as IconName, { class: "size-4" })
			: "";
		this.innerHTML = `<div role="alert" data-slot="alert">${iconHtml}<div data-content class="contents"></div></div>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const variant = this.getAttribute("variant") ?? "default";
		const el = this.querySelector<HTMLElement>("[data-slot=alert]");
		if (!el) return;
		el.className = cn(
			BASE,
			VARIANTS[variant] ?? VARIANTS.default,
			this.getAttribute("class"),
		);
	}
}

defineElement("z-alert", Alert);
for (const [name, ctor] of Object.entries(alertParts)) {
	defineElement(`z-${name}`, ctor);
}
