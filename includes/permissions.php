<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
function has_permission(string $permission, ?int $userId=null): bool { $uid=$userId ?? (int)($_SESSION['user_id']??0); if(!$uid)return false; $s=db()->prepare('SELECT COUNT(*) FROM users u JOIN role_permissions rp ON rp.role_id=u.role_id JOIN permissions p ON p.id=rp.permission_id WHERE u.id=? AND p.code=? AND u.status="Active"');$s->execute([$uid,$permission]);return (bool)$s->fetchColumn(); }
function require_permission(string $permission): void { require_login(); if(!has_permission($permission)){http_response_code(403); exit('403 Forbidden: You do not have permission to access this module.');} }
function can(string $permission): bool { return has_permission($permission); }
