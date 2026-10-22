import { createStyledElement, defineElement } from "../../../lib/base";

export const cardParts = {
	card: createStyledElement(
		"flex flex-col gap-6 rounded-xl border bg-card py-6 text-card-foreground shadow-sm",
	),
	"card-header": createStyledElement(
		"grid auto-rows-min items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto]",
	),
	"card-title": createStyledElement("leading-none font-semibold"),
	"card-description": createStyledElement("text-sm text-muted-foreground"),
	"card-action": createStyledElement(
		"col-start-2 row-span-2 row-start-1 self-start justify-self-end",
	),
	"card-content": createStyledElement("px-6"),
	"card-footer": createStyledElement("flex items-center px-6"),
};

export type CardPart = keyof typeof cardParts;

for (const [name, ctor] of Object.entries(cardParts)) {
	defineElement(`z-${name}`, ctor);
}
