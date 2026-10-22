/** biome-ignore-all lint/style/noNonNullAssertion: test ergonomics */
// @vitest-environment happy-dom
import { beforeAll, describe, expect, it } from "vitest";

beforeAll(async () => {
	await import("../src/presentation/components/index");
});

describe("no duplicate markup after projection", () => {
	it("z-button renders exactly one native button", () => {
		const el = document.createElement("z-button");
		el.textContent = "Click";
		document.body.appendChild(el);
		expect(el.querySelectorAll("button")).toHaveLength(1);
	});

	it("radio group renders one control per z-radio", () => {
		const el = document.createElement("z-radio-group");
		el.innerHTML = `<z-radio value="a">A</z-radio><z-radio value="b">B</z-radio><z-radio value="c">C</z-radio>`;
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-slot=radio]")).toHaveLength(3);
		expect(el.querySelectorAll("label")).toHaveLength(3);
	});

	it("switch renders exactly one track", () => {
		const el = document.createElement("z-switch");
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-slot=switch]")).toHaveLength(1);
	});

	it("tabs render one trigger and one panel per z-tab", () => {
		const el = document.createElement("z-tabs");
		el.innerHTML = `
			<z-tab value="a" label="A">aa</z-tab>
			<z-tab value="b" label="B">bb</z-tab>`;
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-slot=tabs-trigger]")).toHaveLength(2);
		expect(el.querySelectorAll("[data-slot=tabs-content]")).toHaveLength(2);
	});

	it("accordion renders one trigger per item", () => {
		const el = document.createElement("z-accordion");
		el.innerHTML = `
			<z-accordion-item value="1" trigger="One">x</z-accordion-item>
			<z-accordion-item value="2" trigger="Two">y</z-accordion-item>`;
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-slot=accordion-trigger]")).toHaveLength(
			2,
		);
	});

	it("dialog with trigger has trigger + close only", () => {
		const el = document.createElement("z-dialog");
		el.setAttribute("trigger", "Open");
		el.innerHTML = "<p>hi</p>";
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-slot=dialog-trigger]")).toHaveLength(1);
		expect(el.querySelectorAll("[data-slot=dialog-close]")).toHaveLength(1);
	});

	it("product card renders a single add-to-cart button", () => {
		const el = document.createElement("z-product-card");
		el.setAttribute("id", "1");
		el.setAttribute("name", "X");
		el.setAttribute("price", "10");
		document.body.appendChild(el);
		expect(el.querySelectorAll("[data-add-to-cart]")).toHaveLength(1);
	});

	it("moving a rendered element does not duplicate its content", () => {
		const el = document.createElement("z-button");
		el.textContent = "Mover";
		document.body.appendChild(el);

		const target = document.createElement("div");
		document.body.appendChild(target);
		target.appendChild(el); // move it

		expect(el.querySelectorAll("button")).toHaveLength(1);
		expect(el.textContent!.trim()).toBe("Mover");
	});
});
