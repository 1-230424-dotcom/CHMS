<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid CSRF token.'); } }
function flash(string $type, string $message): void { $_SESSION['flash'][] = [$type, $message]; }
function flashes(): string { $out=''; foreach ($_SESSION['flash'] ?? [] as [$type,$msg]) $out .= '<div class="alert alert-' . e($type) . ' alert-dismissible fade show">' . e($msg) . '<button class="btn-close" data-bs-dismiss="alert"></button></div>'; unset($_SESSION['flash']); return $out; }
function audit(string $action, string $entity='', ?int $entityId=null, string $description=''): void { try { $s=db()->prepare('INSERT INTO audit_logs(user_id,action,entity,entity_id,description,ip_address) VALUES(?,?,?,?,?,?)'); $s->execute([$_SESSION['user_id'] ?? null,$action,$entity,$entityId,$description,$_SERVER['REMOTE_ADDR'] ?? 'unknown']); } catch (Throwable $e) {} }
function json_response(array $data, int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data); exit; }
function post(string $key, mixed $default=null): mixed { return $_POST[$key] ?? $default; }
function get(string $key, mixed $default=null): mixed { return $_GET[$key] ?? $default; }
function active_services(bool $publicOnly=false): array { $sql='SELECT * FROM services WHERE status="Active"'; if($publicOnly)$sql.=' AND public_booking_enabled=1'; $sql.=' ORDER BY name'; return db()->query($sql)->fetchAll(); }
function all_barangays(): array { return ['Aplaya','Balibago','Caingin','Dila','Dita','Don Jose','Ibaba','Kanluran','Labas','Malitlit','Malusak','Market Area','Pooc','Pulong Santa Cruz','Sinalhan','Tagapo','Tatlong Kahoy','Sto. Domingo']; }
function setting(string $key, mixed $default=null): mixed { $s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?'); $s->execute([$key]); $v=$s->fetchColumn(); return $v===false?$default:$v; }
