<?php
session_start();

// 1. Verificar si el usuario está logeado
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    header("Location: login.html");
    exit();
}

// 2. Configurar el tiempo máximo de inactividad (10 minutos = 600 segundos)
$tiempo_maximo = 600; 

// 3. Calcular el tiempo transcurrido
if (isset($_SESSION['ultimo_acceso'])) {
    $vida_sesion = time() - $_SESSION['ultimo_acceso'];
    
    if ($vida_sesion > $tiempo_maximo) {
        // Si superó el tiempo, destruimos la sesión y mandamos al login con un mensaje
        session_unset();
        session_destroy();
        header("Location: login.html?error=timeout");
        exit();
    }
}

// 4. Actualizar la hora de último acceso
// Esto hace que el temporizador se reinicie cada vez que el usuario carga una página
$_SESSION['ultimo_acceso'] = time();
?>