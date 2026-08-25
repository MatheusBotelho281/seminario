const areaUpload = document.getElementById('area-upload');
const inputArquivo = document.getElementById('input-arquivo');
const barraContainer = document.getElementById('barra-progresso-container');
const barra = document.getElementById('barra-progresso');
const mensagem = document.getElementById('mensagem');
const corpoTabela = document.getElementById('corpo-tabela');
const btnAtualizar = document.getElementById('btn-atualizar');

// Clique na área abre o seletor de arquivo
areaUpload.addEventListener('click', () => inputArquivo.click());

inputArquivo.addEventListener('change', () => {
    if (inputArquivo.files.length > 0) {
        enviarArquivo(inputArquivo.files[0]);
    }
});

// Arrastar e soltar
['dragenter', 'dragover'].forEach(evento => {
    areaUpload.addEventListener(evento, (e) => {
        e.preventDefault();
        areaUpload.classList.add('arrastando');
    });
});

['dragleave', 'drop'].forEach(evento => {
    areaUpload.addEventListener(evento, (e) => {
        e.preventDefault();
        areaUpload.classList.remove('arrastando');
    });
});

areaUpload.addEventListener('drop', (e) => {
    const arquivos = e.dataTransfer.files;
    if (arquivos.length > 0) {
        enviarArquivo(arquivos[0]);
    }
});

function mostrarMensagem(texto, tipo) {
    mensagem.textContent = texto;
    mensagem.className = 'mensagem ' + tipo;
    mensagem.hidden = false;
}

function enviarArquivo(arquivo) {
    const dados = new FormData();
    dados.append('arquivo', arquivo);

    mensagem.hidden = true;
    barraContainer.hidden = false;
    barra.style.width = '0%';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php');

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const porcentagem = Math.round((e.loaded / e.total) * 100);
            barra.style.width = porcentagem + '%';
        }
    });

    xhr.onload = () => {
        barraContainer.hidden = true;
        try {
            const resposta = JSON.parse(xhr.responseText);
            if (resposta.ok) {
                mostrarMensagem('✅ ' + resposta.mensagem, 'sucesso');
                carregarListagem();
            } else {
                mostrarMensagem('⚠️ ' + resposta.mensagem, 'erro');
            }
        } catch (erro) {
            mostrarMensagem('⚠️ Ocorreu um erro inesperado ao enviar o arquivo.', 'erro');
        }
        inputArquivo.value = '';
    };

    xhr.onerror = () => {
        barraContainer.hidden = true;
        mostrarMensagem('⚠️ Falha de conexão ao enviar o arquivo.', 'erro');
    };

    xhr.send(dados);
}

function carregarListagem() {
    corpoTabela.innerHTML = '<tr><td colspan="3">Carregando...</td></tr>';

    fetch('list.php')
        .then(resp => resp.json())
        .then(resposta => {
            if (!resposta.ok) {
                corpoTabela.innerHTML = '<tr><td colspan="3">Erro: ' + resposta.mensagem + '</td></tr>';
                return;
            }

            if (resposta.arquivos.length === 0) {
                corpoTabela.innerHTML = '<tr><td colspan="3">Nenhum arquivo enviado ainda.</td></tr>';
                return;
            }

            corpoTabela.innerHTML = '';
            resposta.arquivos.forEach(arq => {
                const linha = document.createElement('tr');
                linha.innerHTML = `
                    <td>${arq.nome}</td>
                    <td>${arq.tamanho}</td>
                    <td>${arq.data}</td>
                `;
                corpoTabela.appendChild(linha);
            });
        })
        .catch(() => {
            corpoTabela.innerHTML = '<tr><td colspan="3">Não foi possível carregar a lista.</td></tr>';
        });
}

btnAtualizar.addEventListener('click', carregarListagem);

// Carrega a lista assim que a página abre
carregarListagem();
