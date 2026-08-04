# CRM Enterprise - Plataforma de Gestión de Clientes, Business Intelligence y Seguimientos Comerciales

## 1. Resumen Ejecutivo

**CRM Enterprise** es una plataforma web de nivel empresarial orientada a la gestión estratégica de clientes (CRM), análisis interactivo de ventas (Business Intelligence) y coordinación operativa de equipos comerciales. El sistema está desarrollado bajo una arquitectura web ligera y de alto rendimiento utilizando **PHP (PDO)**, **MySQL**, **JavaScript Vanilla (ES6+)** y **Chart.js**.

La plataforma permite centralizar el ciclo de vida de los clientes (desde lead hasta cliente activo), registrar bitácoras interactivas de seguimiento comercial, visualizar métricas operativas en tiempo real y generar informes ejecutivos preparados para exportación en formato PDF o impresión.

---

## 2. Arquitectura del Sistema

El sistema implementa una arquitectura desacoplada basada en servicios HTTP RESTful y consumo asíncrono mediante `Fetch API`.

* **Capa de Presentación (Frontend)**: Construida sobre HTML5 semántico, CSS3 sin dependencias de frameworks pesados y JavaScript Vanilla modularizado. Maneja el estado de la interfaz en tiempo real, renders dinámicos de datos y gráficos analíticos.
* **Capa de Negocio y API (Backend)**: Desarrollada en PHP 8.x estructurado mediante patrones orientados a la seguridad, control de excepciones y procesamiento de peticiones en formato JSON.
* **Capa de Persistencia (Database)**: Base de datos relacional MySQL 5.7+ / MariaDB con aislamiento de conexiones mediante el patrón **PDO (PHP Data Objects)**, consultas preparadas (*Prepared Statements*) y restricciones de integridad referencial.

---

## 3. Módulos y Funcionalidades Principales

### 3.1 Dashboard de Business Intelligence (BI)
* **Indicadores Clave de Rendimiento (KPIs)**:
  * Total de clientes registrados en cartera.
  * Valor acumulado del Pipeline activo (expresado en moneda Quetzales, Q).
  * Ticket promedio por cliente.
  * Tasa de conversión global (% de cierre).
  * Leads en etapa de calificación.
* **Módulo de Analítica Visual (Chart.js v4.4)**:
  1. *Distribución por Etapas*: Gráfico tipo Rosca (*Doughnut*) que visualiza el estado del pipeline comercial.
  2. *Oportunidades por Sector Industrial*: Gráfico de Barras (*Bar Chart*) para segmentación de ingresos según la industria.
  3. *Canales de Contacto del Equipo*: Gráfico de Área Polar (*Polar Area*) que categoriza la frecuencia de interacciones realizadas.

### 3.2 Gestión Operativa de Cartera (CRUD de Clientes)
* **Control Completo de Registros**: Creación, edición, consulta detallada y eliminación de registros de clientes.
* **Motor de Búsqueda y Filtrado Dinámico**: Filtrado en tiempo real sin recarga de página por término clave (nombre, correo, empresa), estado operativo (*Lead, Prospecto, Activo, Inactivo*) y sector industrial.
* **Ordenamiento Dinámico**: Clasificación alfabética, por valor de oportunidad económica y por fecha de último contacto.
* **Acciones Directas de Contacto**: Integración con protocolos de comunicación rápida para WhatsApp Web (`wa.me`) y correo electrónico (`mailto:`), además de selector rápido de etapa en línea (*Inline Stage Switcher*).

### 3.3 Bitácora de Seguimientos e Interacciones
* **Historial Cronológico de Contactos**: Registro detallado de tipos de interacción: Llamadas, Reuniones, Correos, Notas y Tareas.
* **Línea de Tiempo (*Timeline*)**: Visualización ordenada de las actividades previas realizadas por los asesores comerciales.
* **Programación de Compromisos Futuros**: Control de fechas y horas para próximos seguimientos, asociando el usuario comercial responsable.

### 3.4 Motor de Reportes Ejecutivos (HTML / PDF)
* Vista de reporte imprimible accesible a través del endpoint `api/export.php`.
* Estilos optimizados mediante reglas de medios de impresión (`@page { size: landscape; margin: 6mm; }`) configurados para ajustar la tabla general y resumen analítico en una hoja PDF apaisada sin recortes de información.

---

## 4. Modelo de Datos y Esquema SQL

La base de datos relacional consta de dos tablas principales interconectadas mediante llaves foráneas con eliminación en cascada (`ON DELETE CASCADE`).

```sql
-- (Opcional en local, omitir en hosting):
-- CREATE DATABASE IF NOT EXISTS `crm_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `crm_db`;

-- Tabla de Clientes
CREATE TABLE `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `company` VARCHAR(150) DEFAULT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('lead', 'prospect', 'active', 'inactive') NOT NULL DEFAULT 'lead',
  `opportunity_value` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `industry` VARCHAR(100) DEFAULT 'General',
  `address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `last_contact_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_email` (`email`),
  INDEX `idx_name` (`name`),
  INDEX `idx_company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Interacciones
CREATE TABLE `interactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `type` ENUM('call', 'meeting', 'email', 'note', 'task') NOT NULL DEFAULT 'note',
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `interaction_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `next_followup_date` DATETIME DEFAULT NULL,
  `user_name` VARCHAR(100) DEFAULT 'Asesor Comercial',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_interaction_date` (`interaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. Especificación de la API RESTful

| Método HTTP | Endpoint | Descripción | Parámetros de Petición / Body |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/clients.php` | Lista de clientes paginados con filtros | `page`, `q`, `status`, `industry`, `sort` |
| `POST` | `/api/clients.php` | Registro de nuevo cliente | JSON Body: `name`, `email`, `phone`, `company`, `opportunity_value`, `industry`, etc. |
| `PUT` | `/api/clients.php` | Edición de datos de cliente o cambio de etapa | JSON Body: `id`, `status_only` (opcional), campos modificados |
| `DELETE` | `/api/clients.php` | Eliminación de registro de cliente | JSON Body: `id` |
| `GET` | `/api/stats.php` | Métrica de KPIs y conjuntos de datos para gráficos | Ninguno |
| `GET` | `/api/interactions.php` | Historial de interacciones asociadas | `client_id`, `upcoming` (opcional) |
| `POST` | `/api/interactions.php` | Registro de nueva actividad o compromiso | JSON Body: `client_id`, `type`, `subject`, `description`, `next_followup_date` |
| `GET` | `/api/export.php` | Generación de informe PDF / HTML imprimible | `q`, `status`, `industry` |

---

## 6. Configuración de Seguridad y Credenciales

Por buenas prácticas de seguridad en entornos de producción, el repositorio no incluye credenciales de acceso a la base de datos ni claves privadas.

### 6.1 Gestión de Variables de Conexión
Los parámetros de acceso se gestionan mediante el archivo de configuración localizado en `config/config.php`. En un servidor de producción o hosting de internet, se deben definir las constantes correspondientes con los accesos otorgados por el administrador del servidor:

* `DB_HOST`: Host o dirección IP del servidor de base de datos MySQL (ej. `localhost` o IP privada).
* `DB_PORT`: Puerto de conexión MySQL (por defecto `3306`).
* `DB_USER`: Usuario autorizado con privilegios sobre la base de datos del sistema.
* `DB_PASS`: Contraseña segura del usuario de base de datos.
* `DB_NAME`: Nombre asignado a la base de datos en el servidor de producción.

```php
<?php
// Archivo: config/config.php (Ejemplo de estructura de parámetros)
if (!defined('DB_HOST')) define('DB_HOST', 'TU_HOST_DE_PRODUCCION');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_USER')) define('DB_USER', 'TU_USUARIO_BD');
if (!defined('DB_PASS')) define('DB_PASS', 'TU_PASSWORD_SEGURO');
if (!defined('DB_NAME')) define('DB_NAME', 'TU_NOMBRE_BD');
```

> **Nota de Seguridad**: Se recomienda excluir el archivo `config/config.php` del control de versiones mediante `.gitignore` para evitar la exposición accidental de credenciales en repositorios públicos.

---

## 7. Despliegue e Instalación en Servidor de Producción

### 7.1 Requisitos del Servidor
* **Servidor Web**: Apache 2.4+ / Nginx.
* **Entorno PHP**: Versión 8.0 o superior con las extensiones `pdo_mysql`, `json` y `mbstring` habilitadas.
* **Base de Datos**: MySQL 5.7+ o MariaDB 10.4+.
* **Protocolo de Seguridad**: Certificado SSL/TLS (HTTPS) recomendado.

### 7.2 Pasos de Despliegue en Servidor

1. **Carga de Archivos**: Subir los archivos del proyecto al directorio raíz del servidor web (`public_html`, `www` o la ruta correspondiente a su VirtualHost).
2. **Configuración de Credenciales**: Crear o actualizar el archivo `config/config.php` asignando el host, usuario, contraseña y nombre de base de datos correspondientes a su entorno servidor.
3. **Inicialización de Base de Datos**:
   * Opción A (Asistente Web): Acceder desde el navegador a la ruta del servidor `https://tu-dominio.com/setup.php` y hacer clic en **Inicializar Base de Datos y Cargar Datos**.
   * Opción B (Importación Directa): Importar el archivo `database.sql` directamente a través de phpMyAdmin, MySQL CLI o el panel de administración del proveedor de hosting (cPanel/Plesk).
4. **Desactivación del Asistente**: Por motivos de seguridad, una vez instalada la base de datos en producción, se recomienda restringir o eliminar el archivo `setup.php`.
5. **Verificación**: Navegar a la URL principal del dominio para confirmar el acceso correcto al dashboard.

---

## 8. Estructura del Proyecto

```
crmEnterprise/
├── api/
│   ├── clients.php        # Controlador RESTful para operaciones CRUD de clientes
│   ├── export.php         # Generador de reportes imprimibles en HTML / PDF
│   ├── interactions.php   # Controlador RESTful de bitácora e interacciones
│   └── stats.php          # Motor de cálculo de KPIs y métricas analíticas
├── assets/
│   ├── css/
│   │   └── style.css      # Sistema de diseño de interfaz (Obsidian Wallet UI)
│   ├── img/
│   │   └── favicon.svg    # Logotipo e isotipo vectorial del proyecto
│   └── js/
│       ├── app.js         # Controlador JavaScript cliente (DOM, AJAX Fetch, Modales)
│       └── charts.js      # Módulo de inicialización y renderizado de Chart.js
├── config/
│   ├── config.php         # Parámetros de configuración de la base de datos (Excluido de credenciales reales)
│   └── db.php             # Conexión PDO con manejo seguro de excepciones
├── database.sql           # Script SQL de estructura de datos e índices
├── index.php              # Dashboard principal y punto de entrada de la aplicación
├── setup.php              # Instalador web para inicialización inicial
└── README.md              # Documentación técnica del proyecto
```

---

## 9. Licencia

Este proyecto está bajo la Licencia **MIT**. Libre para uso, modificación y distribución académica, comercial o personal.
# CRM-Enterprise
