<?php
// admin-dashboard.php
session_start();
// Proteção: Redireciona para o login se não estiver autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Administração de Notícias</title>
    <link rel="stylesheet" href="./css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    </head>
<body>
    <main>
        <div id="dashboard-container">
            <div class="dashboard-header">
                <h1>Painel de Notícias</h1>
                <a href="admin.php" class="btn-create">
                    <i class="fas fa-plus"></i> Criar Nova Notícia
                </a>
            </div>
            <p style="margin-top: 10px;">Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['username']); ?>! | <a href="logout.php">Sair</a></p>
            
            <p id="status-message" style="margin-top: 15px; font-weight: bold;"></p>

            <div id="news-list-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="news-table-body">
                        <tr><td colspan="5" style="text-align: center;">Carregando notícias...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const NEWS_DATA_URL = './data/noticias.json';
        const DELETE_ENDPOINT = 'delete_news.php';
        const UPDATE_ENDPOINT = 'update_news.php';
        const newsTableBody = document.getElementById('news-table-body');
        const statusMessage = document.getElementById('status-message');

        // Função auxiliar para formatar a data
        function formatAdminDate(dateString) {
            const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
            return new Date(dateString).toLocaleDateString('pt-BR', options);
        }

        // Função principal para carregar e renderizar a lista
        async function loadNewsList() {
            newsTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Carregando notícias...</td></tr>';
            
            try {
                const response = await fetch(NEWS_DATA_URL);
                if (!response.ok) throw new Error(`Erro ao carregar o JSON: ${response.status}`);
                
                let newsList = await response.json();
                
                // Ordenar pela data de publicação (mais recente primeiro)
                newsList.sort((a, b) => new Date(b.dataPublicacao) - new Date(a.dataPublicacao));

                newsTableBody.innerHTML = '';
                
                if (newsList.length === 0) {
                    newsTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Nenhuma notícia encontrada.</td></tr>';
                    return;
                }

                newsList.forEach((noticia, index) => {
                    const statusText = noticia.status === 'published' ? 'Publicado' : 'Oculto';
                    const statusClass = noticia.status === 'published' ? 'status-published' : 'status-hidden';
                    const toggleText = noticia.status === 'published' ? 'Ocultar' : 'Publicar';
                    const toggleClass = noticia.status === 'published' ? 'btn-hide' : 'btn-show';

                    const row = newsTableBody.insertRow();
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${formatAdminDate(noticia.dataPublicacao)}</td>
                        <td><a href="./noticia.html?slug=${noticia.slug}" target="_blank">${noticia.titulo}</a></td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>
                            <button class="btn-edit" onclick="editNews('${noticia.slug}')">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="${toggleClass}" onclick="toggleStatus('${noticia.slug}', '${noticia.status}')">
                                <i class="fas fa-eye${noticia.status === 'published' ? '-slash' : ''}"></i> ${toggleText}
                            </button>
                            <button class="btn-delete" onclick="deleteNews('${noticia.slug}')">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </td>
                    `;
                });

            } catch (error) {
                console.error('Falha ao carregar notícias:', error);
                newsTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Erro ao carregar notícias.</td></tr>';
            }
        }
        
        // Redireciona para a página de edição (admin.php)
        function editNews(slug) {
            window.location.href = `admin.php?slug=${slug}`;
        }

        // --- Funções de Ação (Toggle e Delete) ---
        
        async function toggleStatus(slug, currentStatus) {
            if (!confirm(`Tem certeza que deseja ${currentStatus === 'published' ? 'OCULTAR' : 'PUBLICAR'} esta notícia?`)) return;

            const newStatus = currentStatus === 'published' ? 'hidden' : 'published';
            
            try {
                const response = await fetch(UPDATE_ENDPOINT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slug: slug, status: newStatus, action: 'status' })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    statusMessage.style.color = 'green';
                    statusMessage.textContent = `✅ Status alterado com sucesso!`;
                    loadNewsList(); // Recarrega a lista
                } else {
                    statusMessage.style.color = 'red';
                    statusMessage.textContent = `❌ Erro ao alterar status: ${result.message || 'Erro desconhecido.'}`;
                }
            } catch (error) {
                statusMessage.style.color = 'red';
                statusMessage.textContent = '❌ Erro de rede ao alterar status.';
            }
        }

        async function deleteNews(slug) {
            if (!confirm(`ATENÇÃO: Você tem certeza que deseja EXCLUIR PERMANENTEMENTE a notícia com o slug: ${slug}?`)) return;

            try {
                const response = await fetch(DELETE_ENDPOINT, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slug: slug })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    statusMessage.style.color = 'green';
                    statusMessage.textContent = `✅ Notícia excluída com sucesso.`;
                    loadNewsList(); // Recarrega a lista
                } else {
                    statusMessage.style.color = 'red';
                    statusMessage.textContent = `❌ Erro ao excluir: ${result.message || 'Erro desconhecido.'}`;
                }
            } catch (error) {
                statusMessage.style.color = 'red';
                statusMessage.textContent = '❌ Erro de rede ao excluir.';
            }
        }

        document.addEventListener('DOMContentLoaded', loadNewsList);
    </script>
</body>
</html>