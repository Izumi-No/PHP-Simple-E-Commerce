import Alpine from 'alpinejs'

window.Alpine = Alpine;

Alpine.start()

async function main(){
const response = await fetch("http://localhost:8000/products");
const products = await response.json();
console.log(products);
}
main();
