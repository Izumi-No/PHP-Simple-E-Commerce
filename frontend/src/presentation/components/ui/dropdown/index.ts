import {
	BaseElement,
	createStyledElement,
	defineElement,
} from "../../../lib/base";
import { escapeHtml } from "../../../lib/utils";
import { buttonVariants } from "../button/index";

const CONTENT =
	"absolute right-0 z-50 mt-1 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md";

export class Dropdown extends BaseElement {
	static observedAttributes = ["trigger", "align", "open", "model", "x-model"];

	protected render(): void {
		const trigger = this.getAttribute("trigger") ?? "";

		this.innerHTML = `
			<div data-slot="dropdown" class="relative inline-block text-left">
				<button type="button" data-slot="dropdown-trigger" @click="open = !open"
					:aria-expanded="open" class="${buttonVariants({ variant: "outline" })}">
					${escapeHtml(trigger)}
				</button>
				<div data-slot="dropdown-content" x-show="open" x-transition.opacity.duration.150ms
					@click.outside="open = false"
					@click="if ($event.target.closest('[role=menuitem]')) open = false"
					class="${CONTENT}">
					<span data-content class="contents"></span>
				</div>
			</div>`;

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		if (!this.hasAttribute("x-data")) {
			this.setAttribute("x-data", `{ open: ${this.hasAttribute("open")} }`);
		}
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}
		if (this.hasAttribute("x-model")) this.setAttribute("x-modelable", "open");
	}
}

export class DropdownItem extends BaseElement {
	protected render(): void {
		this.innerHTML = `<button type="button" role="menuitem" data-slot="dropdown-item" class=""><span data-content class="contents"></span></button>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const el = this.querySelector<HTMLElement>("[data-slot=dropdown-item]");
		if (!el) return;
		el.className = [
			"flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-left text-sm outline-hidden select-none",
			"hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground",
			this.getAttribute("class") ?? "",
		]
			.filter(Boolean)
			.join(" ");
	}
}

export const dropdownParts = {
	"dropdown-label": createStyledElement(
		"px-2 py-1.5 text-xs font-medium text-muted-foreground",
	),
	"dropdown-separator": createStyledElement("-mx-1 my-1 h-px bg-border"),
};

defineElement("z-dropdown", Dropdown);
defineElement("z-dropdown-item", DropdownItem);
for (const [name, ctor] of Object.entries(dropdownParts)) {
	defineElement(`z-${name}`, ctor);
}
