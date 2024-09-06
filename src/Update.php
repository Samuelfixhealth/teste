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
    // Verifica se o arquivo foi realmente carregado
    if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $_SESSION['file_name'] = $_FILES['file']['name'];
        $_SESSION['file_tmp_path'] = $_FILES['file']['tmp_name'];

        // Log para verificação do caminho temporário
        error_log("Caminho temporário do arquivo: " . $_SESSION['file_tmp_path']);
        
        // Redireciona para autenticação se não houver access_token
        if (!isset($_SESSION['access_token'])) {
            $authUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize?client_id=$clientId&response_type=code&redirect_uri=$redirectUri&response_mode=query&scope=offline_access Files.ReadWrite.All";
            header('Location: ' . $authUrl);
            exit();
        }
    } else {
        echo "Erro: Nenhum arquivo foi enviado.";
        exit();
    }
}

// Caso já tenha o token de acesso, realiza o upload
if (isset($_SESSION['access_token'])) {
    if (isset($_SESSION['file_name']) && isset($_SESSION['file_tmp_path'])) {
        $fileName = $_SESSION['file_name'];
        $fileTmpPath = $_SESSION['file_tmp_path'];

        // Verifica se o caminho do arquivo temporário existe
        if (file_exists($fileTmpPath)) {
            // Log para verificar se o arquivo existe no caminho temporário
            error_log("Arquivo temporário encontrado: " . $fileTmpPath);
            
            $fileData = file_get_contents($fileTmpPath);

            $graph = new Graph();
            $graph->setAccessToken($_SESSION['access_token']);

            try {
                // Realiza o upload do arquivo para o Microsoft Graph API
                $response = $graph->createRequest("PUT", "/me/drive/root:/".basename($fileName).":/content")
                    ->upload($fileData);

                echo "Arquivo enviado com sucesso! ID: " . $response->getId();
            } catch (Exception $e) {
                // Log do erro com detalhes
                error_log("Erro ao enviar o arquivo para o Microsoft Graph: " . $e->getMessage());
                echo "Erro ao enviar o arquivo: " . $e->getMessage();
            }

            // Limpar a sessão de arquivos
            unset($_SESSION['file_name'], $_SESSION['file_tmp_path']);
        } else {
            // Log para o caso de o caminho temporário ser inválido
            error_log("Erro: Caminho do arquivo temporário inválido: " . $fileTmpPath);
            echo "Erro: Caminho do arquivo temporário inválido.";
        }
    } else {
        echo "Nenhum arquivo foi encontrado para upload.";
    }
}
