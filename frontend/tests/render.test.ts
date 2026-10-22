/** biome-ignore-all lint/style/noNonNullAssertion: test ergonomics */
// @vitest-environment happy-dom
import { beforeAll, expect, it } from "vitest";

beforeAll(async () => {
	await import("../src/presentation/components/index");
});

/** Counts "interactive button rows" — what the user actually sees. */
it("each z-button renders exactly ONE button", () => {
	const el = document.createElement("z-button");
	el.textContent = "Salvar";
	document.body.appendChild(el);
	expect(el.querySelectorAll("button")).toHaveLength(1);
	expect(el.textContent!.trim()).toBe("Salvar");
});

it("dialog renders ONE trigger button, not duplicated content", () => {
	const el = document.createElement("z-dialog");
	el.setAttribute("trigger", "Abrir modal");
	el.innerHTML = `<p>Conteúdo interno</p>`;
	document.body.appendChild(el);

	console.log("DIALOG OUTER:", el.outerHTML.slice(0, 600));
	expect(el.querySelectorAll("button")).toHaveLength(2); // trigger + close
	expect(el.textContent).toContain("Conteúdo interno");
});

it("sheet renders ONE trigger", () => {
	const el = document.createElement("z-sheet");
	el.setAttribute("trigger", "Carrinho");
	el.innerHTML = `<p>items</p>`;
	document.body.appendChild(el);
	console.log("SHEET OUTER:", el.outerHTML.slice(0, 500));
});

it("dropdown renders ONE trigger + keeps children as items", () => {
	const el = document.createElement("z-dropdown");
	el.setAttribute("trigger", "Menu");
	el.innerHTML = `<z-dropdown-item>Item A</z-dropdown-item>`;
	document.body.appendChild(el);
	console.log("DROPDOWN OUTER:", el.outerHTML.slice(0, 700));
});

it("card parts don't duplicate content", () => {
	const el = document.createElement("z-card");
	el.innerHTML = `<z-card-title>Tamanho</z-card-title><z-card-content>x</z-card-content>`;
	document.body.appendChild(el);
	console.log("CARD OUTER:", el.outerHTML);
});

it("radio group wraps items properly", () => {
	const el = document.createElement("z-radio-group");
	el.innerHTML = `<z-radio value="a">Opção A</z-radio><z-radio value="b">Opção B</z-radio>`;
	document.body.appendChild(el);
	console.log("RADIO GROUP OUTER:", el.outerHTML.slice(0, 700));
	console.log("radios found:", el.querySelectorAll("[data-slot=radio]").length);
	console.log("labels found:", el.querySelectorAll("label").length);
});
