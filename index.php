<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivo para OneDrive</title>
</head>
<body>
    <h1>Upload de Arquivo para OneDrive</h1>
    <h1>commit</h1>
    <form action="src/Update.php" method="post" enctype="multipart/form-data">
        <label for="file">Escolha um arquivo para enviar:</label>
        <input type="file" name="file" id="file" required>
        <button type="submit">Fazer Upload</button>
    </form>
</body>
</html>