<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
function login_user(array $user): void { session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['last_activity']=time(); }
function current_user(): ?array { static $user=null; if(empty($_SESSION['user_id'])) return null; if(time()-($_SESSION['last_activity']??0)>SESSION_TIMEOUT){ logout_user(); return null; } $_SESSION['last_activity']=time(); if($user===null){$s=db()->prepare('SELECT u.*,r.name role_name,s.name service_name FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN services s ON s.id=u.service_id WHERE u.id=? AND u.status="Active"');$s->execute([$_SESSION['user_id']]);$user=$s->fetch() ?: null;} return $user; }
function logout_user(): void { if(isset($_SESSION['user_id'])) audit('LOGOUT','users',(int)$_SESSION['user_id'],'User logged out'); $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>$p['httponly'],'samesite'=>'Lax']); } session_destroy(); }
function require_login(): array { $u=current_user(); if(!$u) redirect(BASE_URL.'/login.php'); return $u; }
