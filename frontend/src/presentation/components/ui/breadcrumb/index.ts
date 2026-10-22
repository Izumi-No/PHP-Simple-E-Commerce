import {
	BaseElement,
	createStyledElement,
	defineElement,
} from "../../../lib/base";
import { icon } from "../../../lib/icons";

export const breadcrumbParts = {
	"breadcrumb-list": createStyledElement(
		"flex flex-wrap items-center gap-1.5 text-sm break-words text-muted-foreground sm:gap-2.5",
	),
	"breadcrumb-item": createStyledElement("inline-flex items-center gap-1.5"),
	"breadcrumb-link": createStyledElement(
		"transition-colors hover:text-foreground",
	),
	"breadcrumb-page": createStyledElement("font-normal text-foreground"),
	"breadcrumb-separator": createStyledElement("inline-flex [&>svg]:size-3.5"),
};

export class Breadcrumb extends BaseElement {
	protected render(): void {
		this.innerHTML = `
			<nav aria-label="breadcrumb" data-slot="breadcrumb">
				<ol data-content class="flex flex-wrap items-center gap-1.5 text-sm break-words text-muted-foreground sm:gap-2.5"></ol>
			</nav>`;
	}

	protected projectContent(): void {
		const list = this.querySelector("ol");
		if (!list) return;
		const nodes = this.originalContent.filter(
			(node) =>
				!(node.nodeType === Node.TEXT_NODE && !node.textContent?.trim()),
		);
		list.append(...nodes);
	}
}

defineElement("z-breadcrumb", Breadcrumb);
for (const [name, ctor] of Object.entries(breadcrumbParts)) {
	defineElement(`z-${name}`, ctor);
}
export const breadcrumbSeparatorIcon = icon("chevron-right");
