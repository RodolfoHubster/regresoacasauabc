<?php
require 'vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig('credentials.json');
$client->setScopes([Google\Service\Gmail::GMAIL_SEND]);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Primera ejecución: genera la URL de autorización
if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    echo "<a href='$authUrl'>Autorizar aplicación</a>";
} else {
    // Intercambia el código por tokens
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    file_put_contents('token.json', json_encode($token));
    echo "Token guardado en token.json";
}