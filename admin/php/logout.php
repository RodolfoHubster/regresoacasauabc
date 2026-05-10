<?php
session_start();
// Destruye todas las variables de sesión
$_SESSION = array();
session_destroy();

// Te manda de regreso al login
header("Location: ../login.html");
exit();
?>