<?php
require_once __DIR__ . '/database.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function app_base_path(): string {
    return rtrim(APP_BASE_URL, '/');
}
function app_url(string $path): string {
    $base = app_base_path();
    $path = '/' . ltrim($path, '/');
    if ($path === $base || str_starts_with($path, $base . '/')) return $path;
    return $base . $path;
}
function redirect(string $url): never {
    if (preg_match('#^https?://#i', $url)) {
        header('Location: ' . $url);
        exit;
    }
    header('Location: ' . app_url($url));
    exit;
}
function now(): string { return date('Y-m-d H:i:s'); }
function today(): string { return date('Y-m-d'); }
function money(float|int|string $value): string { return 'R$ ' . number_format((float)$value, 2, ',', '.'); }
function number_value($value): float { return round((float)str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', (string)$value)), 2); }
function date_br(?string $date): string { return $date ? date('d/m/Y', strtotime($date)) : ''; }
function datetime_br(?string $date): string { return $date ? date('d/m/Y H:i', strtotime($date)) : ''; }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type'=>$type,'message'=>$message]; }
function get_flashes(): array { $f=$_SESSION['flash']??[]; unset($_SESSION['flash']); return $f; }
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function check_csrf(): void { if(!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??'')) { http_response_code(419); exit('Solicitação inválida.'); } }
function log_action(string $action, ?string $entity=null, ?int $entityId=null, ?string $details=null): void {
    try { $uid = $_SESSION['user']['id'] ?? null; db()->prepare('INSERT INTO logs(usuario_id,acao,entidade,entidade_id,data_hora,detalhes) VALUES(?,?,?,?,?,?)')->execute([$uid,$action,$entity,$entityId,now(),$details]); } catch(Throwable $e) {}
}
function next_number(string $prefix, string $table, string $column='numero'): string {
    $last=(string)db()->query("SELECT $column FROM $table WHERE $column LIKE '" . $prefix . "-%' ORDER BY id DESC LIMIT 1")->fetchColumn();
    $n=$last ? ((int)substr($last, strlen($prefix)+1))+1 : 1;
    return $prefix . '-' . str_pad((string)$n, 6, '0', STR_PAD_LEFT);
}
function setting(string $key, string $default=''): string { $s=db()->prepare('SELECT valor FROM configuracoes WHERE chave=?'); $s->execute([$key]); $v=$s->fetchColumn(); return $v===false?$default:(string)$v; }
function set_setting(string $key,string $value): void { db()->prepare('INSERT INTO configuracoes(chave,valor) VALUES(?,?) ON CONFLICT(chave) DO UPDATE SET valor=excluded.valor')->execute([$key,$value]); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged(): bool { return !empty($_SESSION['user']); }
function is_internal(): bool { return is_logged() && ($_SESSION['user']['kind']??'')==='internal'; }
function is_client(): bool { return is_logged() && ($_SESSION['user']['kind']??'')==='client'; }
function require_login(): void { if(!is_logged()) redirect('/login.php'); }
function require_internal(): void { require_login(); if(!is_internal()) redirect('/cliente/index.php'); }
function require_client(): void { require_login(); if(!is_client()) redirect('/admin/index.php'); }
function require_first_access_clear(): void { if(is_internal() && !empty($_SESSION['user']['first_access'])) redirect('/admin/usuarios/alterar-senha.php'); }
function find_product(string $term): ?array {
    $sql='SELECT p.*, c.nome categoria_nome FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND (p.nome LIKE ? OR p.codigo LIKE ? OR p.codigo_barras LIKE ?) ORDER BY p.nome LIMIT 1';
    $q='%'.$term.'%'; $st=db()->prepare($sql); $st->execute([$q,$q,$q]); return $st->fetch() ?: null;
}
function stock_move(int $productId,float $qty,string $type,?string $origin,?int $originId,?int $userId,?string $obs=''): void {
    $pdo=db(); $st=$pdo->prepare('SELECT estoque FROM produtos WHERE id=?'); $st->execute([$productId]); $before=$st->fetchColumn(); if($before===false) throw new RuntimeException('Produto não encontrado.'); $before=(float)$before; $after=$before+$qty; if($after < -0.00001) throw new RuntimeException('Estoque insuficiente.');
    $pdo->prepare('UPDATE produtos SET estoque=?, atualizado_em=? WHERE id=?')->execute([$after,now(),$productId]);
    $pdo->prepare('INSERT INTO movimentacoes_estoque(produto_id,data_hora,tipo,quantidade,estoque_anterior,estoque_posterior,origem,origem_id,usuario_id,observacao) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$productId,now(),$type,$qty,$before,$after,$origin,$originId,$userId,$obs]);
}
function open_cash(): ?array { return db()->query("SELECT * FROM caixas WHERE status='Aberto' ORDER BY id DESC LIMIT 1")->fetch() ?: null; }
function add_cash_movement(int $cashId,string $type,float $value,string $desc, int $uid, ?int $saleId=null): void { db()->prepare('INSERT INTO movimentacoes_caixa(caixa_id,venda_id,tipo,valor,descricao,data_hora,usuario_id) VALUES(?,?,?,?,?,?,?)')->execute([$cashId,$saleId,$type,$value,$desc,now(),$uid]); }
function payment_cash_type(string $method): string { return match($method){ 'Dinheiro'=>'Dinheiro','PIX'=>'PIX','Débito'=>'Débito','Crédito'=>'Crédito', default=>$method }; }
function status_badge(string $status): string { $cls=preg_replace('/[^a-z0-9]+/i','-',strtolower($status)); return '<span class="badge badge-'.$cls.'">'.e($status).'</span>'; }
function post_back(string $url): never { redirect($url); }
