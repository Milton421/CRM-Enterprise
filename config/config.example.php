<?php
/**
 * Plantilla de Configuración de Base de Datos para Producción / Desarrollo.
 * Copia este archivo como `config.php` y define tus credenciales reales.
 */

if (!defined('DB_HOST')) define('DB_HOST', 'TU_HOST_DE_BASE_DE_DATOS'); // Ej. localhost o sqlXXX.tu-hosting.com
if (!defined('DB_PORT')) define('DB_PORT', '3306');                     // Puerto MySQL (por defecto 3306)
if (!defined('DB_USER')) define('DB_USER', 'TU_USUARIO_BD');             // Usuario de MySQL
if (!defined('DB_PASS')) define('DB_PASS', 'TU_CONTRASEÑA_BD');          // Contraseña de MySQL
if (!defined('DB_NAME')) define('DB_NAME', 'TU_NOMBRE_BD');              // Nombre de la Base de Datos
