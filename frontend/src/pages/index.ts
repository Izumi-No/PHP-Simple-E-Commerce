import "../presentation/components/index";
import { cartService, productService, theme } from "../composition";
import { toast } from "../presentation/components/ui/toast/index";
import { startAlpine } from "../presentation/lib/alpine";
import "./home";

function bootstrap(): void {
	startAlpine();
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", bootstrap, { once: true });
} else {
	bootstrap();
}

declare global {
	interface Window {
		toast: typeof toast;
		theme: typeof theme;
		productService: typeof productService;
		cartService: typeof cartService;
	}
}

window.toast = toast;
window.theme = theme;
window.productService = productService;
window.cartService = cartService;
