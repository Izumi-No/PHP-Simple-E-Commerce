/** biome-ignore-all lint/style/noNonNullAssertion: test-only assertion ergonomics */
// @vitest-environment happy-dom
import { beforeAll, expect, it } from "vitest";

beforeAll(async () => {
	await import("../src/presentation/components/index");
});

it("registers every exported custom element without crashing", () => {
	const expected = [
		"z-accordion",
		"z-alert",
		"z-alert-title",
		"z-alert-description",
		"z-avatar",
		"z-badge",
		"z-breadcrumb",
		"z-button",
		"z-card",
		"z-card-header",
		"z-card-title",
		"z-card-description",
		"z-card-content",
		"z-card-footer",
		"z-checkbox",
		"z-dialog",
		"z-dropdown",
		"z-dropdown-item",
		"z-dropdown-label",
		"z-dropdown-separator",
		"z-input",
		"z-label",
		"z-pagination",
		"z-progress",
		"z-radio-group",
		"z-radio",
		"z-select",
		"z-separator",
		"z-sheet",
		"z-skeleton",
		"z-slider",
		"z-switch",
		"z-table",
		"z-tabs",
		"z-textarea",
		"z-toaster",
		"z-tooltip",
		"z-product-card",
		"z-price",
		"z-quantity-input",
		"z-empty-state",
	];

	for (const name of expected) {
		expect(
			customElements.get(name),
			`<${name}> must be registered`,
		).toBeTruthy();
	}
});

it("button renders a native control with variant classes", () => {
	const el = document.createElement("z-button");
	el.setAttribute("variant", "destructive");
	el.textContent = "Delete";
	document.body.appendChild(el);

	const control = el.querySelector("button")!;
	expect(control).toBeTruthy();
	expect(control.className).toContain("bg-destructive");
	expect(control.textContent).toContain("Delete");
});

it("input forwards value + x-model to the native input", () => {
	const el = document.createElement("z-input");
	el.setAttribute("name", "email");
	el.setAttribute("model", "form.email");
	document.body.appendChild(el);

	const input = el.querySelector("input")!;
	expect(input.name).toBe("email");
	expect(input.getAttribute("x-model")).toBe("form.email");
	expect(el.hasAttribute("x-model")).toBe(false);
});

it("tabs build triggers + panels from z-tab children with safe expressions", () => {
	const tabs = document.createElement("z-tabs");
	tabs.setAttribute("value", "a");
	tabs.innerHTML = `
		<z-tab value="a" label="Alpha">AAA</z-tab>
		<z-tab value="b" label="Beta">BBB</z-tab>
	`;
	document.body.appendChild(tabs);

	const triggers = tabs.querySelectorAll("[data-slot=tabs-trigger]");
	const panels = tabs.querySelectorAll("[data-slot=tabs-content]");
	expect(triggers).toHaveLength(2);
	expect(panels).toHaveLength(2);
	expect(triggers[0].getAttribute(":data-state")).toContain("'a'");
	expect(tabs.getAttribute("x-data")).toBe("{ value: 'a' }");

	// no broken quoted attributes (the JSON.stringify regression)
	expect(tabs.innerHTML).not.toContain('value === "');
});

it("accordion supports single + collapsible via x-modelable", () => {
	const acc = document.createElement("z-accordion");
	acc.setAttribute("type", "single");
	acc.setAttribute("collapsible", "");
	acc.innerHTML = `<z-accordion-item value="x" trigger="T">q</z-accordion-item>`;
	document.body.appendChild(acc);

	expect(acc.querySelector("[data-slot=accordion-trigger]")).toBeTruthy();
	expect(acc.getAttribute("x-modelable")).toBe("value");
});

it("empty custom element name prefix violation never aborts boot", async () => {
	const { defineElement } = await import("../src/presentation/lib/base");
	const ok = defineElement("card", HTMLElement); // "card" without hyphen
	expect(ok).toBe(false);
});

it("product card adds to the cart store", async () => {
	const { useCart } = await import(
		"../src/presentation/components/marketplace/cart/index"
	);

	const card = document.createElement("z-product-card");
	card.setAttribute("id", "p1");
	card.setAttribute("name", "Mouse");
	card.setAttribute("price", "100");
	document.body.appendChild(card);

	card.querySelector("[data-add-to-cart]")!.dispatchEvent(new Event("click"));

	expect(useCart().items).toHaveLength(1);
	expect(useCart().subtotal).toBe(100);
});
