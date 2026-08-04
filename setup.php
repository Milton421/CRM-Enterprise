<?php

$configFile = __DIR__ . '/config/config.php';

$currentHost = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
$currentPort = defined('DB_PORT') ? DB_PORT : '3306';
$currentUser = defined('DB_USER') ? DB_USER : 'root';
$currentPass = defined('DB_PASS') ? DB_PASS : '';
$currentName = defined('DB_NAME') ? DB_NAME : 'crm_db';

if (file_exists($configFile)) {
    require_once $configFile;
    $currentHost = DB_HOST;
    $currentPort = DB_PORT;
    $currentUser = DB_USER;
    $currentPass = DB_PASS;
    $currentName = DB_NAME;
}

$message = '';
$error = '';
$setupCompleted = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || (php_sapi_name() === 'cli')) {
    $host = trim($_POST['db_host'] ?? $currentHost);
    $port = trim($_POST['db_port'] ?? $currentPort);
    $user = trim($_POST['db_user'] ?? $currentUser);
    $pass = $_POST['db_pass'] ?? $currentPass;
    $name = trim($_POST['db_name'] ?? $currentName);

    if (empty($host) || empty($user) || empty($name)) {
        $error = "Por favor complete los campos obligatorios (Host, Usuario y Nombre de Base de Datos).";
    } else {
        try {
            $dsnWithoutDB = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdoServer = new PDO($dsnWithoutDB, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);

            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace("`", "", $name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdoServer->exec("USE `" . str_replace("`", "", $name) . "`;");

            $sqlPath = __DIR__ . '/database.sql';
            if (!file_exists($sqlPath)) {
                throw new Exception("El archivo de esquema database.sql no fue encontrado en la raíz.");
            }

            $sql = file_get_contents($sqlPath);
            $pdoServer->exec($sql);

            $configContent = "<?php\n" .
                "/**\n" .
                " * Configuración de Conexión a la Base de Datos MySQL\n" .
                " * Generado automáticamente por Instalador CRM\n" .
                " */\n\n" .
                "define('DB_HOST', " . var_export($host, true) . ");\n" .
                "define('DB_PORT', " . var_export($port, true) . ");\n" .
                "define('DB_USER', " . var_export($user, true) . ");\n" .
                "define('DB_PASS', " . var_export($pass, true) . ");\n" .
                "define('DB_NAME', " . var_export($name, true) . ");\n";

            file_put_contents($configFile, $configContent);

            $message = "¡Conexión exitosa! La base de datos '{$name}' ha sido configurada e inicializada correctamente.";
            $setupCompleted = true;
            $currentHost = $host;
            $currentPort = $port;
            $currentUser = $user;
            $currentPass = $pass;
            $currentName = $name;

        } catch (Exception $e) {
            $error = "Error al conectar o inicializar la base de datos: " . $e->getMessage();
        }
    }
}

if (php_sapi_name() === 'cli') {
    if ($error) {
        echo "[ERROR] $error\n";
        exit(1);
    }
    echo "[ÉXITO] $message\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Conexión BD - CRM Enterprise</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 0; }
        body { background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px 16px; }
        .setup-container { width: 100%; max-width: 520px; background: #1e293b; border: 1px solid #334155; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); overflow: hidden; }
        .setup-header { background: linear-gradient(135deg, #7c3aed, #4f46e5); padding: 28px 24px; text-align: center; }
        .setup-header h1 { font-size: 1.4rem; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; }
        .setup-header p { font-size: 0.85rem; color: #e2e8f0; margin-top: 4px; opacity: 0.9; }
        .setup-body { padding: 28px 24px; }
        .form-group { margin-bottom: 18px; }
        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }
        label { display: block; font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        input.form-control { width: 100%; padding: 11px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #f8fafc; font-size: 0.9rem; outline: none; transition: border-color 0.2s; }
        input.form-control:focus { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2); }
        .alert { padding: 14px 16px; border-radius: 10px; font-size: 0.86rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 10px; line-height: 1.4; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }
        .btn-submit { width: 100%; padding: 12px; background: linear-gradient(135deg, #7c3aed, #6366f1); border: none; border-radius: 8px; color: #ffffff; font-weight: 700; font-size: 0.92rem; cursor: pointer; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; margin-top: 8px; }
        .btn-submit:hover { opacity: 0.92; }
        .btn-secondary { background: #334155; color: #f8fafc; margin-top: 10px; }
        .btn-secondary:hover { background: #475569; }
        .footer-note { text-align: center; font-size: 0.76rem; color: #64748b; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>⚙️ Configuración de Conexión MySQL</h1>
            <p>Ingresa los parámetros de conexión de tu servidor de hosting o base de datos local</p>
        </div>
        <div class="setup-body">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
                <a href="index.php" class="btn-submit">
                    🚀 Lanzar CRM Enterprise
                </a>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group" style="flex:2;">
                            <label for="db_host">Host / Servidor MySQL *</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" value="<?php echo htmlspecialchars($currentHost); ?>" placeholder="localhost o 127.0.0.1" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label for="db_port">Puerto *</label>
                            <input type="text" class="form-control" id="db_port" name="db_port" value="<?php echo htmlspecialchars($currentPort); ?>" placeholder="3306" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="db_user">Usuario de MySQL *</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" value="<?php echo htmlspecialchars($currentUser); ?>" placeholder="root o usuario_cpanel" required>
                    </div>

                    <div class="form-group">
                        <label for="db_pass">Contraseña de MySQL</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($currentPass); ?>" placeholder="Contraseña de la BD">
                    </div>

                    <div class="form-group">
                        <label for="db_name">Nombre de la Base de Datos *</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" value="<?php echo htmlspecialchars($currentName); ?>" placeholder="crm_db" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        💾 Probar y Guardar Conexión BD
                    </button>
                    <a href="index.php" class="btn-submit btn-secondary">
                        Ir al CRM
                    </a>
                </form>
            <?php endif; ?>
            <div class="footer-note">
                CRM Enterprise &copy; 2026 - Conexión Segura PDO
            </div>
        </div>
    </div>
</body>
</html>
