import { createStyledElement, defineElement } from "../../../lib/base";

export const Skeleton = createStyledElement(
	"animate-pulse rounded-md bg-accent",
);

defineElement("z-skeleton", Skeleton);
