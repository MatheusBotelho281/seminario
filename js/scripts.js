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

// --- Adicionar esta função ao seu scripts.js ---

// SIMULAÇÃO: Esta função simula o retorno de uma lista de notícias do Headless CMS
// Novo caminho para o arquivo JSON no seu FTP
const NEWS_DATA_URL = "./data/noticias.json";

// Esta função agora busca o arquivo JSON local
async function fetchLatestNews() {
  try {
    const response = await fetch(NEWS_DATA_URL);
    if (!response.ok) {
      throw new Error(`Erro ao carregar o JSON: ${response.status}`);
    }
    const newsList = await response.json();
    // Retorna apenas as 3 primeiras notícias para a home
    return newsList.slice(0, 3);
  } catch (error) {
    console.error("Falha ao carregar notícias:", error);
    return []; // Retorna array vazio em caso de erro
  }
}

// A função renderNewsCards (do passo anterior) permanece igual, mas agora usará este novo fetchLatestNews.
// ... (A função renderNewsCards deve ser mantida, pois ela monta o HTML)
function renderNewsCards() {
  const feedContainer = document.getElementById("feed-noticias");
  if (!feedContainer) return;

  fetchLatestNews()
    .then((newsList) => {
      feedContainer.innerHTML = ""; // Limpa o conteúdo

      newsList.forEach((noticia) => {
        const cardLink = document.createElement("a");
        // 2. URL Dinâmico: Passa o slug via parâmetro de URL
        cardLink.href = `./noticia.html?slug=${noticia.slug}`;
        cardLink.classList.add("card-noticia");

        cardLink.innerHTML = `
              <div class="card-noticia-imagem">
                  <img src="${noticia.imagemURL}" alt="Capa da Notícia: ${noticia.titulo}" />
              </div>
              <div class="card-noticia-conteudo">
                  <h2>${noticia.titulo}</h2>
                  <p>${noticia.resumo}</p>
              </div>
          `;
        feedContainer.appendChild(cardLink);
      });
    })
    .catch((error) => {
      console.error("Erro ao carregar notícias:", error);
      feedContainer.innerHTML =
        "<p>Não foi possível carregar as últimas notícias.</p>";
    });
}

// Chamar a função junto com o carregamento do DOM
document.addEventListener("DOMContentLoaded", renderNewsCards);
