LIVRARIA NOSSA SENHORA DO AMOR DIVINO
=====================================
Versão 1.0.0 — PHP 8+ / SQLite / PDO

1. REQUISITOS
- PHP 8.0 ou superior.
- Extensão PDO habilitada.
- Extensão PDO_SQLite habilitada.
- Apache é recomendado; o projeto inclui .htaccess para proteção adicional.
- A pasta database/ precisa ser gravável pelo PHP na primeira execução.

2. INSTALAÇÃO
1) Faça upload de todo o conteúdo para a pasta pública da hospedagem.
2) Garanta permissão de escrita em database/.
3) Acesse a URL do projeto.
4) O banco SQLite, as tabelas, índices, categorias básicas e usuário inicial serão criados automaticamente.
5) O projeto não depende de Node, npm, Composer, frameworks ou serviços externos.

3. ACESSO INICIAL
Login: admin
Senha: admin123
Esse usuário é apenas um Usuário da Livraria, sem função ou permissão administrativa diferenciada.
No primeiro acesso a alteração da senha é obrigatória.

4. SEGURANÇA E BACKUP
- Use HTTPS na hospedagem.
- Não exponha a pasta database/ diretamente.
- O .htaccess impede acesso HTTP ao SQLite e às configurações em Apache.
- Faça backup copiando database/livraria.sqlite com o PHP parado ou fora do horário de escrita, quando possível.
- Nunca envie o banco a terceiros sem considerar os dados pessoais nele contidos.

5. USO BÁSICO
- Cadastre Usuários da Livraria em Usuários.
- Cadastre clientes em Clientes.
- Cadastre categorias e produtos.
- Informe estoque inicial pelo módulo Estoque.
- Abra o caixa antes de operar vendas em dinheiro.
- Use o PDV para vendas e gere o comprovante interno.
- Crie encomendas no módulo Encomendas ou no portal do cliente.
- Compras finalizadas aumentam estoque e podem gerar Contas a Pagar.
- Relatórios e Exportação permitem análise e backup lógico.

6. CONTAS A PAGAR
Compras parceladas geram registros na tabela parcelas com tipo Pagar.
O Financeiro permite criar e baixar lançamentos adicionais.

7. CONTAS A RECEBER
O sistema registra entradas financeiras de vendas e permite lançamentos financeiros manuais. Não há integração bancária externa.

8. IMPRESSÃO
As páginas de venda, encomenda, relatórios, caixa e financeiro possuem modo de impressão via CSS.

9. CSV
Arquivos exportados usam UTF-8 com BOM para facilitar abertura em Excel em português.

10. BACKUP E ATUALIZAÇÃO
Antes de atualizar arquivos, faça cópia de database/livraria.sqlite. O sistema usa CREATE TABLE IF NOT EXISTS e não apaga registros existentes.

11. FUSO HORÁRIO
America/Sao_Paulo.

12. OBSERVAÇÃO FISCAL
O comprovante de venda é interno e não substitui documento fiscal. A emissão fiscal não faz parte deste projeto.

13. INSTALAÇÃO EM SUBPASTA
O código calcula automaticamente a pasta-base quando instalado abaixo do DOCUMENT_ROOT, por exemplo em /livraria, para os redirecionamentos e recursos principais.
