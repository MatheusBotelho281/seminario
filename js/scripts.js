const carrossel = document.querySelector(".carrossel-container");
const images = document.querySelectorAll(".carrossel-container img");
const prevButton = document.getElementById("prev");
const nextButton = document.getElementById("next");
let index = 0;
let autoPlay = true;
let interval;

function updateCarousel() {
  carrossel.style.transform = `translateX(${-index * 75}em)`;
}

function nextSlide() {
  index = (index + 1) % images.length;
  updateCarousel();
}

function prevSlide() {
  index = (index - 1 + images.length) % images.length;
  updateCarousel();
}

function startAutoPlay() {
  interval = setInterval(nextSlide, 3000);
}

function stopAutoPlay() {
  clearInterval(interval);
  autoPlay = false;
}

nextButton.addEventListener("click", () => {
  nextSlide();
  stopAutoPlay();
});

prevButton.addEventListener("click", () => {
  prevSlide();
  stopAutoPlay();
});

// Função para alternar o menu
function toggleMenu() {
  var menu = document.getElementById("menu-mobile"); // Pega o elemento de id 'menu'
  menu.classList.toggle("hidden"); // Alterna a classe 'hidden' para mostrar ou esconder o menu
}
