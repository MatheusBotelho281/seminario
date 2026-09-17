<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if (!extension_loaded('pdo_sqlite')) { throw new RuntimeException('O servidor precisa ter a extensão PDO_SQLite habilitada.'); }
    if (!extension_loaded('pdo_sqlite')) { throw new RuntimeException('O servidor precisa ter a extensão PDO_SQLite habilitada.'); }
    if ($pdo instanceof PDO) return $pdo;
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    initialize_database($pdo);
    return $pdo;
}

function initialize_database(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    login TEXT NOT NULL UNIQUE,
    email TEXT,
    senha_hash TEXT NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'interno' CHECK(tipo='interno'),
    ativo INTEGER NOT NULL DEFAULT 1,
    primeiro_acesso INTEGER NOT NULL DEFAULT 0,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cpf_cnpj TEXT,
    telefone TEXT,
    email TEXT,
    endereco TEXT,
    login TEXT NOT NULL UNIQUE,
    senha_hash TEXT NOT NULL,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS produtos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT NOT NULL UNIQUE,
    codigo_barras TEXT,
    nome TEXT NOT NULL,
    categoria_id INTEGER,
    descricao TEXT,
    custo REAL NOT NULL DEFAULT 0,
    preco REAL NOT NULL DEFAULT 0,
    estoque REAL NOT NULL DEFAULT 0,
    estoque_minimo REAL NOT NULL DEFAULT 0,
    unidade TEXT NOT NULL DEFAULT 'UN',
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL,
    FOREIGN KEY(categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS fornecedores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    cpf_cnpj TEXT,
    telefone TEXT,
    email TEXT,
    endereco TEXT,
    contato TEXT,
    observacoes TEXT,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    atualizado_em TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS vendas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero TEXT NOT NULL UNIQUE,
    cliente_id INTEGER,
    usuario_id INTEGER NOT NULL,
    data_venda TEXT NOT NULL,
    subtotal REAL NOT NULL DEFAULT 0,
    desconto REAL NOT NULL DEFAULT 0,
    total REAL NOT NULL DEFAULT 0,
    forma_pagamento TEXT NOT NULL,
    parcelas INTEGER NOT NULL DEFAULT 1,
    valor_recebido REAL NOT NULL DEFAULT 0,
    troco REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Concluida',
    origem_encomenda_id INTEGER,
    observacoes TEXT,
    FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY(origem_encomenda_id) REFERENCES encomendas(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS venda_itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    venda_id INTEGER NOT NULL,
    produto_id INTEGER NOT NULL,
    quantidade REAL NOT NULL,
    preco_unitario REAL NOT NULL,
    subtotal REAL NOT NULL,
    FOREIGN KEY(venda_id) REFERENCES vendas(id) ON DELETE CASCADE,
    FOREIGN KEY(produto_id) REFERENCES produtos(id)
);
CREATE TABLE IF NOT EXISTS encomendas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero TEXT NOT NULL UNIQUE,
    cliente_id INTEGER NOT NULL,
    usuario_id INTEGER,
    origem TEXT NOT NULL DEFAULT 'portal',
    data_hora TEXT NOT NULL,
    subtotal REAL NOT NULL DEFAULT 0,
    desconto REAL NOT NULL DEFAULT 0,
    total REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Aberta',
    observacoes TEXT,
    FOREIGN KEY(cliente_id) REFERENCES clientes(id),
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS encomenda_itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    encomenda_id INTEGER NOT NULL,
    produto_id INTEGER NOT NULL,
    quantidade REAL NOT NULL,
    preco_unitario REAL NOT NULL,
    subtotal REAL NOT NULL,
    FOREIGN KEY(encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE,
    FOREIGN KEY(produto_id) REFERENCES produtos(id)
);
CREATE TABLE IF NOT EXISTS encomenda_status_historico (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    encomenda_id INTEGER NOT NULL,
    status_anterior TEXT,
    status_novo TEXT NOT NULL,
    usuario_id INTEGER,
    data_hora TEXT NOT NULL,
    FOREIGN KEY(encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    produto_id INTEGER NOT NULL,
    data_hora TEXT NOT NULL,
    tipo TEXT NOT NULL,
    quantidade REAL NOT NULL,
    estoque_anterior REAL NOT NULL,
    estoque_posterior REAL NOT NULL,
    origem TEXT,
    origem_id INTEGER,
    usuario_id INTEGER,
    observacao TEXT,
    FOREIGN KEY(produto_id) REFERENCES produtos(id),
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS caixas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_abertura_id INTEGER NOT NULL,
    abertura_em TEXT NOT NULL,
    valor_inicial REAL NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Aberto',
    fechamento_em TEXT,
    usuario_fechamento_id INTEGER,
    valor_informado REAL,
    valor_esperado REAL,
    diferenca REAL,
    FOREIGN KEY(usuario_abertura_id) REFERENCES usuarios(id),
    FOREIGN KEY(usuario_fechamento_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS movimentacoes_caixa (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    caixa_id INTEGER NOT NULL,
    venda_id INTEGER,
    tipo TEXT NOT NULL,
    valor REAL NOT NULL,
    descricao TEXT,
    data_hora TEXT NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY(caixa_id) REFERENCES caixas(id) ON DELETE CASCADE,
    FOREIGN KEY(venda_id) REFERENCES vendas(id) ON DELETE SET NULL,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
);
CREATE TABLE IF NOT EXISTS compras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fornecedor_id INTEGER NOT NULL,
    numero_documento TEXT,
    data_compra TEXT NOT NULL,
    subtotal REAL NOT NULL DEFAULT 0,
    desconto REAL NOT NULL DEFAULT 0,
    frete REAL NOT NULL DEFAULT 0,
    total REAL NOT NULL DEFAULT 0,
    parcelado INTEGER NOT NULL DEFAULT 0,
    observacoes TEXT,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY(fornecedor_id) REFERENCES fornecedores(id),
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
);
CREATE TABLE IF NOT EXISTS compra_itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    compra_id INTEGER NOT NULL,
    produto_id INTEGER NOT NULL,
    quantidade REAL NOT NULL,
    custo_unitario REAL NOT NULL,
    subtotal REAL NOT NULL,
    FOREIGN KEY(compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY(produto_id) REFERENCES produtos(id)
);
CREATE TABLE IF NOT EXISTS parcelas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fornecedor_id INTEGER,
    compra_id INTEGER,
    numero_parcela INTEGER NOT NULL,
    quantidade_parcelas INTEGER NOT NULL,
    valor REAL NOT NULL,
    vencimento TEXT NOT NULL,
    pagamento TEXT,
    status TEXT NOT NULL DEFAULT 'Pendente',
    tipo TEXT NOT NULL DEFAULT 'Pagar',
    observacao TEXT,
    FOREIGN KEY(fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL,
    FOREIGN KEY(compra_id) REFERENCES compras(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS movimentacoes_financeiras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo TEXT NOT NULL,
    categoria TEXT,
    descricao TEXT NOT NULL,
    valor REAL NOT NULL,
    vencimento TEXT,
    data_pagamento TEXT,
    status TEXT NOT NULL DEFAULT 'Pendente',
    usuario_id INTEGER NOT NULL,
    origem TEXT,
    referencia_id INTEGER,
    observacao TEXT,
    criado_em TEXT NOT NULL,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
);
CREATE TABLE IF NOT EXISTS descontos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    tipo TEXT NOT NULL CHECK(tipo IN ('percentual','fixo','produto','categoria')),
    valor REAL NOT NULL DEFAULT 0,
    produto_id INTEGER,
    categoria_id INTEGER,
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT NOT NULL,
    FOREIGN KEY(produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
    FOREIGN KEY(categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    acao TEXT NOT NULL,
    entidade TEXT,
    entidade_id INTEGER,
    data_hora TEXT NOT NULL,
    detalhes TEXT,
    FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS configuracoes (
    chave TEXT PRIMARY KEY,
    valor TEXT NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS idx_produtos_barcode ON produtos(codigo_barras);
CREATE INDEX IF NOT EXISTS idx_produtos_nome ON produtos(nome);
CREATE INDEX IF NOT EXISTS idx_usuarios_login ON usuarios(login);
CREATE INDEX IF NOT EXISTS idx_clientes_login ON clientes(login);
CREATE INDEX IF NOT EXISTS idx_encomendas_status ON encomendas(status);
CREATE INDEX IF NOT EXISTS idx_vendas_data ON vendas(data_venda);
CREATE INDEX IF NOT EXISTS idx_financeiro_data ON movimentacoes_financeiras(vencimento, data_pagamento);
CREATE INDEX IF NOT EXISTS idx_estoque_produto ON movimentacoes_estoque(produto_id, data_hora);
SQL;
    $pdo->exec($sql);
    $defaults = [
        'nome_livraria' => APP_NAME,
        'endereco_livraria' => 'Estrada União e Indústria, 3441 – Corrêas, Petrópolis, RJ',
        'telefone_livraria' => '(24) 2221-2187 / (24) 2221-1459',
        'email_livraria' => 'contato@seminario.com.br',
        'rodape_livraria' => 'Livraria Nossa Senhora do Amor Divino',
        'moeda' => 'R$',
        'comprovante_dados' => 'Documento interno da livraria. Não possui valor fiscal.',
    ];
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO configuracoes(chave, valor) VALUES(?, ?)');
    foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);
    $count = (int)$pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO categorias(nome, criado_em) VALUES(?, ?)');
        foreach (['Livros','Artigos religiosos','Papelaria','Imagens','Terços','Crucifixos','Outros'] as $c) $stmt->execute([$c, date('Y-m-d H:i:s')]);
    }
    $admin = $pdo->query("SELECT id FROM usuarios WHERE login='admin' LIMIT 1")->fetchColumn();
    if (!$admin) {
        $stmt = $pdo->prepare('INSERT INTO usuarios(nome, login, email, senha_hash, tipo, ativo, primeiro_acesso, criado_em, atualizado_em) VALUES(?,?,?,?,?,?,?,?,?)');
        $now = date('Y-m-d H:i:s');
        $stmt->execute(['Usuário da Livraria','admin','',password_hash('admin123', PASSWORD_DEFAULT),'interno',1,1,$now,$now]);
    }
}
