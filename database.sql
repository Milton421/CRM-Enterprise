
DROP TABLE IF EXISTS `interactions`;
DROP TABLE IF EXISTS `clients`;

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

-- Tabla de Seguimiento de Contactos
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

-- Clientes 
INSERT INTO `clients` (`id`, `name`, `email`, `phone`, `company`, `position`, `status`, `opportunity_value`, `industry`, `address`, `notes`, `last_contact_at`, `created_at`) VALUES
(1, 'Carlos Castillo', 'carlos.castillo@progreso.com.gt', '+502 2345 6789', 'Corporación Progreso', 'Director de TI', 'active', 55000.00, 'Construcción & Industria', 'Diagonal 6, Zona 10, Ciudad de Guatemala', 'Cliente estratégico. Renovación anual de licencias y soporte técnico enterprise.', '2026-07-30 10:30:00', '2026-06-01 09:00:00'),
(2, 'Ana Sofía Morales', 'ana.morales@bancoindustrial.com.gt', '+502 2420 3000', 'Banco Industrial Guatemala', 'Gerente de Operaciones', 'prospect', 88000.00, 'Banca & Finanzas', 'Vía 4, Zona 4, Ciudad de Guatemala', 'Propuesta entregada a comité de tecnología. En fase final de revisión presupuestaria.', '2026-07-29 16:15:00', '2026-06-15 11:20:00'),
(3, 'Alejandro Fuentes', 'afuentes@cerveceria.com.gt', '+502 2289 1000', 'Cervecería Centro Americana', 'CEO & Fundador', 'lead', 135000.00, 'Alimentos & Bebidas', '3a Avenida, Zona 2, Ciudad de Guatemala', 'Contacto inicial positivo en cumbre empresarial. Demostración técnica agendada.', '2026-07-31 09:00:00', '2026-07-10 14:30:00'),
(4, 'Mariana Arriola', 'mariana.arriola@pantaleon.com.gt', '+502 2339 9000', 'Grupo Pantaleon', 'Directora Financiera', 'inactive', 42000.00, 'Agroindustria', 'Avenida La Reforma, Zona 9, Guatemala', 'Proyecto en pausa temporal por reestructuración de infraestructura interna.', '2026-06-18 11:00:00', '2026-05-20 16:45:00'),
(5, 'David Girón', 'd.giron@tigo.com.gt', '+502 2424 0000', 'Tigo Guatemala', 'Jefe de Compras & Tecnología', 'active', 110000.00, 'Telecomunicaciones', 'Km 9.5 Carretera a El Salvador, Guatemala', 'Acuerdo multianual de servicios en la nube firmado satisfactoriamente.', '2026-07-30 15:20:00', '2026-04-12 10:00:00'),
(6, 'Gabriela Sandoval', 'g.sandoval@farmaciasbatres.com.gt', '+502 2380 0000', 'Farmacias Batres', 'Gerente de Logística', 'prospect', 75000.00, 'Salud & Farmacia', 'Calzada Atanasio Tzul, Zona 12, Guatemala', 'Revisando módulos de trazabilidad de entregas e inventario.', '2026-07-28 14:10:00', '2026-07-02 08:30:00'),
(7, 'Rodrigo Mendoza', 'rodrigo.mendoza@walmart.com', '+502 2244 5500', 'Walmart Centroamérica', 'Director de Transformación', 'lead', 160000.00, 'Retail & Comercio', 'Blvd. Los Próceres, Zona 10, Guatemala', 'Oportunidad de alta escala para migración de sistemas multicanal.', '2026-07-27 11:45:00', '2026-07-18 15:10:00'),
(8, 'Sofía Villagrán', 'svillagran@uvg.edu.gt', '+502 2507 1500', 'Universidad del Valle', 'VP de Infraestructura', 'active', 48000.00, 'Educación', '11 Calle 15-79, Zona 15, Vista Hermosa III', 'Implementación de plataforma CRM en facultades completada.', '2026-07-26 09:30:00', '2026-03-10 10:00:00'),
(9, 'Fernando Estrada', 'festrada@cempro.com.gt', '+502 2277 8000', 'Cementos Progreso S.A.', 'Gerente de Planta', 'prospect', 92000.00, 'Manufactura', 'Finca La Pedrera, Zona 6, Guatemala', 'Presentación enviada a gerencia general. Pendiente de aprobación.', '2026-07-29 17:00:00', '2026-06-25 13:00:00'),
(10, 'Lucía Paredes', 'lparedes@gyt.com.gt', '+502 2338 5000', 'Seguros G&T', 'Jefa de CRM & Atención', 'active', 64000.00, 'Aseguradora & Servicios', '6a Avenida 9-08, Zona 9, Guatemala', 'Excelente nivel de satisfacción con los tableros analíticos BI.', '2026-07-31 11:15:00', '2026-05-05 12:00:00');

INSERT INTO `interactions` (`client_id`, `type`, `subject`, `description`, `interaction_date`, `next_followup_date`, `user_name`) VALUES
(1, 'meeting', 'Reunión de Revisión Trimestral', 'Evaluación de KPIs comerciales y optimización de flujos de trabajo.', '2026-07-20 11:00:00', '2026-08-05 10:00:00', 'Laura Torres'),
(1, 'call', 'Confirmación de Términos de Contrato', 'Carlos aprobó la adenda para la nueva fase de implementación.', '2026-07-30 10:30:00', '2026-08-03 14:00:00', 'Laura Torres'),
(2, 'email', 'Envío de Propuesta Económica Revisada', 'Propuesta actualizada con el esquema de licenciamiento en la nube.', '2026-07-25 16:15:00', '2026-08-02 11:30:00', 'Miguel Ángel Rivas'),
(2, 'meeting', 'Sesión Técnica con Arquitectura de TI', 'Aclaración de requerimientos de seguridad y cifrado bancario.', '2026-07-29 16:15:00', '2026-08-04 15:00:00', 'Miguel Ángel Rivas'),
(3, 'call', 'Llamada Inicial de Calificación', 'Conversación ejecutiva con Alejandro sobre metas de crecimiento 2026.', '2026-07-31 09:00:00', '2026-08-06 10:00:00', 'Miguel Ángel Rivas'),
(5, 'meeting', 'Firma de Contrato Multianual', 'Reunión presencial en corporativo Tigo para consolidar renovación de plataforma.', '2026-07-30 15:20:00', '2026-08-15 09:00:00', 'Laura Torres'),
(6, 'note', 'Análisis de Requerimientos Logísticos', 'Se registraron las necesidades de integraciones con ERP SAP.', '2026-07-28 14:10:00', '2026-08-03 16:00:00', 'Carlos Castillo'),
(7, 'email', 'Presentación Institucional Enviada', 'Se compartió brochure y casos de éxito del sector Retail.', '2026-07-27 11:45:00', '2026-08-05 11:00:00', 'Carlos Castillo'),
(9, 'task', 'Elaboración de Caso de Negocio BI', 'Preparación de proyecciones de ROI para la directiva de Cementos Progreso.', '2026-07-29 17:00:00', '2026-08-04 09:30:00', 'Miguel Ángel Rivas'),
(10, 'call', 'Verificación de Satisfacción de Usuario', 'Lucía felicitó al equipo por los tiempos de respuesta y estabilidad de la API.', '2026-07-31 11:15:00', '2026-08-10 10:00:00', 'Laura Torres');
