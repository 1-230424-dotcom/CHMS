<?php require_once __DIR__.'/includes/auth.php'; if(current_user()) redirect(BASE_URL.'/dashboard.php'); redirect(BASE_URL.'/login.php');
