<?php
require '../vendor/autoload.php';

session_start();

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

use Dotenv\Dotenv; 

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];
$tenantId = $_ENV['TENANT_ID'];
$redirectUri = $_ENV['REDIRECT_URI'];

// Verifica se o arquivo foi enviado antes da autenticação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $_SESSION['file_name'] = $_FILES['file']['name'];
    $_SESSION['file_tmp_path'] = $_FILES['file']['tmp_name'];

    // Redireciona para autenticação se não houver access_token
    if (!isset($_SESSION['access_token'])) {
        $authUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize?client_id=$clientId&response_type=code&redirect_uri=$redirectUri&response_mode=query&scope=offline_access Files.ReadWrite.All";
        header('Location: ' . $authUrl);
        exit();
    }
}

// Caso já tenha o token de acesso, realiza o upload
if (isset($_SESSION['access_token'])) {
    if (isset($_SESSION['file_name']) && isset($_SESSION['file_tmp_path'])) {
        $fileName = $_SESSION['file_name'];
        $fileTmpPath = $_SESSION['file_tmp_path'];
        $fileData = file_get_contents($fileTmpPath);

        $graph = new Graph();
        $graph->setAccessToken($_SESSION['access_token']);

        try {
            $response = $graph->createRequest("PUT", "/me/drive/root:/".basename($fileName).":/content")
                ->upload($fileData);

            echo "Arquivo enviado com sucesso! ID: " . $response->getId();
        } catch (Exception $e) {
            echo "Erro ao enviar o arquivo: " . $e->getMessage();
        }

        // Limpar a sessão de arquivos
        unset($_SESSION['file_name'], $_SESSION['file_tmp_path']);
    } else {
        echo "Nenhum arquivo foi encontrado para upload.";
    }
}
