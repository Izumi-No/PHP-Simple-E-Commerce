import { BaseElement, defineElement } from "../../../lib/base";
import { type IconName, icon } from "../../../lib/icons";
import { escapeHtml } from "../../../lib/utils";

export class EmptyState extends BaseElement {
	static observedAttributes = ["icon", "title", "description"];

	protected render(): void {
		const iconName = (this.getAttribute("icon") ?? "package") as IconName;
		const title = this.getAttribute("title") ?? "";
		const description = this.getAttribute("description") ?? "";

		this.innerHTML = `
			<div data-slot="empty-state" class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed p-10 text-center">
				<div class="grid size-12 place-items-center rounded-full bg-muted text-muted-foreground">
					${icon(iconName, { class: "size-6" })}
				</div>
				${title ? `<h3 class="font-medium">${escapeHtml(title)}</h3>` : ""}
				${
					description
						? `<p class="max-w-sm text-sm text-muted-foreground">${escapeHtml(
								description,
							)}</p>`
						: ""
				}
				<span data-content class="contents"></span>
			</div>`;
	}
}

defineElement("z-empty-state", EmptyState);
