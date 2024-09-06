<?php

require '../vendor/autoload.php';

use Dotenv\Dotenv;

// Carrega as variáveis do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();

$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$tenantId = $_ENV['TENANT_ID'];
$redirectUri = $_ENV['REDIRECT_URI'];

dd($_FILES);

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Solicitar o token de acesso usando o código de autorização
    $tokenRequestUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
    
    $tokenRequestData = [
        "client_id" => $clientId,
        "scope" => "https://graph.microsoft.com/.default",
        "code" => $code,
        "redirect_uri" => $redirectUri,
        "grant_type" => "authorization_code",
        "client_secret" => $clientSecret,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $tokenRequestUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($tokenRequestData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_VERBOSE => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Erro CURL: ' . curl_error($curl);
    } else {
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode == 200) {
            $responseData = json_decode($response, true);

            if (isset($responseData['access_token'])) {
                $_SESSION['access_token'] = $responseData['access_token'];
                // Redirecionar para a página de upload após receber o token
                header('Location: Update.php');
                exit();
            } else {
                echo "Erro ao obter o token de acesso: " . json_encode($responseData);
            }
        } else {
            echo "Erro na solicitação do token: HTTP $httpCode - " . $response;
        }
    }
} else {
    echo "Código de autorização não encontrado.";
}
