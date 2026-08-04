<div align="center">

# CRM Enterprise

**Plataforma Web de Gestión Estratégica de Clientes, Business Intelligence y Seguimiento Comercial**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/es/docs/Web/JavaScript)
[![Chart.js](https://img.shields.io/badge/Chart.js-v4.4-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)](https://www.chartjs.org/)
[![Demo en Vivo](https://img.shields.io/badge/Demo-Sitio_Web-00C853?style=for-the-badge&logo=googlechrome&logoColor=white)](https://crmenterprise.site.je/)

---

</div>

## Acerca del Proyecto

**CRM Enterprise** es una solución integral orientada a la administración estratégica de prospectos, clientes y oportunidades de negocio. La plataforma combina la gestión operativa de contactos con herramientas avanzadas de **Business Intelligence (BI)**, permitiendo a los equipos comerciales medir el rendimiento del pipeline de ventas en tiempo real y tomar decisiones respaldadas por datos.

El sistema fue diseñado sobre una arquitectura ligera y desacoplada mediante servicios RESTful, ofreciendo una interfaz ejecutiva optimizada para velocidad, respuesta adaptativa y alto rendimiento en servidores web.

---

## Módulos y Funcionalidades

### 1. Dashboard de Business Intelligence (BI)
* **Indicadores Clave (KPIs)**: Total de Clientes, Pipeline Activo en Quetzales (Q), Ticket Promedio, Tasa de Cierre (%) y Leads Activos.
* **Analítica Visual en Tiempo Real**: Gráficos interactivos en Chart.js para distribución por etapa comercial (Doughnut), oportunidades por sector industrial (Bar Chart) y canales de interacción del equipo (Polar Area).

### 2. Gestión Operativa de Cartera (CRUD)
* **Control de Clientes**: Creación, actualización, consulta detallada y eliminación de registros.
* **Búsqueda e Integración**: Búsqueda instantánea en tiempo real, filtros combinados por estado e industria, y acciones directas para contacto vía WhatsApp Web (`wa.me`) y Correo Electrónico (`mailto:`).

### 3. Bitácora de Seguimientos
* **Historial Cronológico**: Registro de llamadas, reuniones, correos, notas y tareas asociadas a cada cliente.
* **Compromisos de Equipo**: Programación de próximas actividades con asignación de asesor comercial responsable.

### 4. Motor de Reportes Ejecutivos
* Generación de informes imprimibles en HTML/PDF accesibles desde `api/export.php`, optimizados con reglas de impresión apaisada (*Landscape*) para encajar sin cortes de datos.

---

## Stack Tecnológico

| Capa / Componente | Tecnología | Descripción |
| :--- | :--- | :--- |
| **Backend & API** | PHP 8.x + PDO | Controladores RESTful con consultas preparadas SQL |
| **Base de Datos** | MySQL 5.7+ / MariaDB | Modelo relacional InnoDB con índices de búsqueda |
| **Frontend** | JavaScript (ES6+) | Lógica asíncrona mediante Fetch API nativo |
| **Visualización** | Chart.js v4.4 | Gráficos estadísticos dinámicos e interactivos |

---

## Estructura Completa del Proyecto

```text
crmEnterprise/
├── api/
│   ├── clients.php        # Controlador RESTful para CRUD y cambio rápido de etapa
│   ├── export.php         # Generación del Informe Ejecutivo imprimible (HTML / PDF)
│   ├── interactions.php   # Controlador RESTful de historial y seguimiento comercial
│   └── stats.php          # API de cálculo de KPIs y datos para gráficos analíticos
├── assets/
│   ├── css/
│   │   └── style.css      # Sistema de diseño de interfaz 
│   ├── img/
│   │   └── favicon.svg    # Logotipo e isotipo vectorial del proyecto
│   └── js/
│       ├── app.js         # Controlador cliente JS (DOM, AJAX Fetch, Modales, Validaciones)
│       └── charts.js      # Módulo de renderizado de gráficos BI con Chart.js
├── config/
│   ├── config.example.php # Plantilla guía de configuración (Segura para Git)
│   ├── config.php         # Configuración con credenciales reales (Ignorado en Git)
│   └── db.php             # Conexión PDO con manejo seguro de excepciones
├── database.sql           # Script DDL de la base de datos y dataset inicial
├── index.php              # Dashboard principal y punto de entrada de la aplicación
├── setup.php              # Asistente gráfico de instalación e inicialización
├── .gitignore             # Reglas de exclusión de archivos sensibles para Git
└── README.md              # Documentación técnica del proyecto
```

---

## Comandos e Instrucciones de Instalación

### 1. Requisitos del Sistema
* Servidor Web (Apache 2.4+ / Nginx)
* PHP 8.0+ con la extensión `pdo_mysql` habilitada
* Base de Datos MySQL 5.7+ o MariaDB 10.4+

### 2. Clonación y Descarga del Código

```bash
# Clonar el repositorio desde GitHub
git clone https://github.com/TU_USUARIO/crmEnterprise.git

# Acceder al directorio del proyecto
cd crmEnterprise
```

### 3. Configuración de Credenciales de Base de Datos

Duplica la plantilla `config/config.example.php` para crear tu archivo `config/config.php`:

```bash
# En sistemas Linux / macOS / Bash:
cp config/config.example.php config/config.php

# En Windows (PowerShell):
Copy-Item config/config.example.php config/config.php
```

Edita el archivo `config/config.php` asignando los parámetros correspondientes a tu servidor:

```php
<?php
// config/config.php
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_USER')) define('DB_USER', 'tu_usuario_mysql');
if (!defined('DB_PASS')) define('DB_PASS', 'tu_password_mysql');
if (!defined('DB_NAME')) define('DB_NAME', 'crm_db');
```

### 4. Inicialización de la Base de Datos

#### Opción A: Mediante la interfaz gráfica del instalador
Accede desde tu navegador a la ruta del asistente:
```http
http://localhost/crmEnterprise/setup.php
```

#### Opción B: Mediante Consola de Comandos (MySQL CLI)
```bash
mysql -u tu_usuario_mysql -p < database.sql
```

#### Opción C: Mediante phpMyAdmin / Panel de Hosting
1. Selecciona o crea tu base de datos en phpMyAdmin.
2. Ve a la pestaña **Importar**.
3. Selecciona el archivo `database.sql` y haz clic en **Continuar**.
