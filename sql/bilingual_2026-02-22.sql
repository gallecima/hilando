# ************************************************************
# Sequel Ace SQL dump
# Versión 20095
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Equipo: localhost (MySQL 5.5.5-10.4.28-MariaDB)
# Base de datos: bilingual
# Tiempo de generación: 2026-02-22 14:34:21 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla activities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `activities`;

CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(32) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_user_id_foreign` (`user_id`),
  KEY `activities_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activities_category_type_index` (`category`,`type`),
  KEY `activities_occurred_at_index` (`occurred_at`),
  CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla afip_invoices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `afip_invoices`;

CREATE TABLE `afip_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `cbte_tipo` varchar(3) NOT NULL,
  `pto_vta` int(10) unsigned NOT NULL,
  `cbte_numero` bigint(20) unsigned DEFAULT NULL,
  `concepto` varchar(2) NOT NULL DEFAULT '1',
  `doc_tipo` varchar(2) DEFAULT NULL,
  `doc_nro` varchar(20) DEFAULT NULL,
  `importe_total` decimal(15,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `cae` varchar(14) DEFAULT NULL,
  `cae_vencimiento` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afip_invoices_order_id_index` (`order_id`),
  KEY `afip_invoices_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla attribute_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attribute_category`;

CREATE TABLE `attribute_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_category_category_id_foreign` (`category_id`),
  KEY `attribute_category_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `attribute_category_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attribute_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla attribute_product
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attribute_product`;

CREATE TABLE `attribute_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned NOT NULL,
  `stock` int(10) unsigned DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_product_product_id_foreign` (`product_id`),
  KEY `attribute_product_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `attribute_product_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attribute_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla attribute_values
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attribute_values`;

CREATE TABLE `attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint(20) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_values_slug_unique` (`slug`),
  KEY `attribute_values_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `attribute_values` WRITE;
/*!40000 ALTER TABLE `attribute_values` DISABLE KEYS */;

INSERT INTO `attribute_values` (`id`, `attribute_id`, `value`, `slug`, `is_active`, `created_at`, `updated_at`)
VALUES
	(1,1,'220v','voltajes-220v',1,'2025-06-30 21:22:09','2025-07-01 12:42:24'),
	(2,1,'110v','voltajes-110v',1,'2025-06-30 21:22:09','2025-07-01 12:42:24'),
	(3,2,'2500w','potencia-2500w',1,'2025-07-08 16:56:30','2025-07-08 16:56:30'),
	(4,2,'3000w','potencia-3000w',1,'2025-07-08 16:56:30','2025-07-08 16:56:30'),
	(5,3,'Negro','color-negro',1,'2025-07-10 13:37:46','2025-07-10 13:37:46'),
	(6,3,'Blanco','color-blanco',1,'2025-07-10 13:37:46','2025-07-10 13:37:46'),
	(7,3,'Rojo','color-rojo',1,'2025-07-21 13:53:55','2025-07-21 13:53:55'),
	(8,4,'Jean','material-jean',1,'2025-10-27 17:18:47','2025-10-27 17:18:47'),
	(9,4,'Bengalina elastizada','material-bengalina-elastizada',1,'2025-10-27 17:18:47','2025-10-27 17:18:47'),
	(12,6,'Salta en la Historia Política y Cultural de la Argentina','coleccion-salta-en-la-historia-politica-y-cultural-de-la-argentina',1,'2025-11-05 16:42:16','2025-11-05 16:44:22'),
	(13,7,'71','cantidad-de-paginas-71',1,'2025-11-05 16:43:37','2025-11-05 16:43:37'),
	(14,7,'54','cantidad-de-paginas-54',1,'2025-11-05 16:43:37','2025-11-18 19:06:09'),
	(15,8,'Tapa Rústica','tipo-de-encuadernacion-tapa-rustica',1,'2025-11-05 16:45:17','2025-11-05 16:45:17'),
	(16,9,'9789506233211','isbn-9789506233211',1,'2025-11-05 16:47:20','2025-11-05 16:47:20'),
	(18,10,'AUDI','marca-audi',1,'2025-11-07 14:50:01','2025-11-07 14:50:01'),
	(19,10,'FERRARI','marca-ferrari',1,'2025-11-07 14:50:01','2025-11-07 14:50:01'),
	(20,10,'MINI COOPER','marca-mini-cooper',1,'2025-11-07 14:50:59','2025-11-07 14:50:59'),
	(22,10,'PORSCHE','marca-porsche',1,'2025-11-07 14:53:34','2025-11-07 14:53:34'),
	(23,11,'1:36','escala-136',1,'2025-11-25 21:46:30','2025-11-25 21:46:30');

/*!40000 ALTER TABLE `attribute_values` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla attributes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attributes`;

CREATE TABLE `attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `has_stock_price` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `attributes` WRITE;
/*!40000 ALTER TABLE `attributes` DISABLE KEYS */;

INSERT INTO `attributes` (`id`, `name`, `slug`, `is_active`, `has_stock_price`, `created_at`, `updated_at`)
VALUES
	(1,'Voltajes','voltajes',1,0,'2025-06-30 21:07:30','2025-07-21 23:27:35'),
	(2,'Potencia','potencia',1,0,'2025-07-08 16:56:30','2025-07-08 16:56:30'),
	(3,'Color','color',1,1,'2025-07-10 13:37:46','2025-07-21 13:54:37'),
	(4,'Material','material',1,0,'2025-10-27 17:18:47','2025-10-27 17:18:47'),
	(6,'Colección','coleccion',1,0,'2025-11-05 16:42:16','2025-11-05 16:42:16'),
	(7,'Cantidad de páginas','cantidad-de-paginas',1,1,'2025-11-05 16:43:37','2025-11-10 19:55:42'),
	(8,'Tipo de encuadernación','tipo-de-encuadernacion',1,0,'2025-11-05 16:45:17','2025-11-05 16:45:17'),
	(9,'ISBN','isbn',1,0,'2025-11-05 16:47:20','2025-11-05 16:51:06'),
	(10,'Marca','marca',1,0,'2025-11-07 14:50:01','2025-11-25 21:47:37'),
	(11,'Escala','escala',1,0,'2025-11-25 21:46:30','2025-11-25 21:46:30');

/*!40000 ALTER TABLE `attributes` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla blog_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `blog_categories`;

CREATE TABLE `blog_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `blog_categories` WRITE;
/*!40000 ALTER TABLE `blog_categories` DISABLE KEYS */;

INSERT INTO `blog_categories` (`id`, `nombre`, `slug`, `activo`, `created_at`, `updated_at`)
VALUES
	(1,'Beneficios','beneficios',1,'2025-07-17 22:06:35','2025-11-10 20:36:43'),
	(2,'Textos Fijos','textos-fijos',1,'2025-07-20 13:33:25','2025-07-20 13:33:33');

/*!40000 ALTER TABLE `blog_categories` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla blog_post_product
# ------------------------------------------------------------

DROP TABLE IF EXISTS `blog_post_product`;

CREATE TABLE `blog_post_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blog_post_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_post_product_blog_post_id_product_id_unique` (`blog_post_id`,`product_id`),
  KEY `blog_post_product_product_id_foreign` (`product_id`),
  CONSTRAINT `blog_post_product_blog_post_id_foreign` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_post_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla blog_posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `blog_posts`;

CREATE TABLE `blog_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `bajada` varchar(255) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `blog_category_id` bigint(20) unsigned NOT NULL,
  `imagen_destacada` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_posts_user_id_foreign` (`user_id`),
  KEY `blog_posts_blog_category_id_foreign` (`blog_category_id`),
  CONSTRAINT `blog_posts_blog_category_id_foreign` FOREIGN KEY (`blog_category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blog_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `blog_posts` WRITE;
/*!40000 ALTER TABLE `blog_posts` DISABLE KEYS */;

INSERT INTO `blog_posts` (`id`, `titulo`, `slug`, `bajada`, `descripcion`, `fecha`, `user_id`, `blog_category_id`, `imagen_destacada`, `activo`, `created_at`, `updated_at`)
VALUES
	(3,'Términos y condiciones','terminos-y-condiciones','Suscribite a los productos de LA NACION y accedé a exclusivos beneficios con tu Tarjeta CLUB LA NACION en todo el país.','El CLUB LA NACION premia la fidelidad de los socios (en adelante, los \"Socios\") que se suscriban a alguno/s de los productos editoriales en su versión impresa o digital, tales como: Diario La NACION, Revista Rolling Stone, Revista Living, Revista Lugares, Revista Jardín, Revista Ohlala!, Revista ¡HOLA! Argentina, posibilitando a los mismos asociarse al CLUB LA NACION y recibir la Tarjeta CLUB LA NACION (en adelante la \"Tarjeta\" o las \"Tarjetas\"), la cual les permite acceder a los beneficios de dicho Club, que otorgan los establecimientos adheridos que participan en el programa de beneficios (en adelante el \"Programa\"), conforme se establece en los presentes términos y condiciones (en adelante \"Términos y Condiciones\").\r\n\r\n \r\n\r\n1)  SUSCRIPCION AL DIARIO LA NACION y/o la Revista Rolling Stone, Revista Living, Revista Lugares, Revista Jardín, Revista Ohlalá!, Revista ¡HOLA! Argentina\r\n \r\n1.1. Podrá ser Socio del Club La Nación aquella persona que se suscriba a uno de los siguientes productos: Diario LA NACION y/o a la Revista Rolling Stone, Revista Living, Revista Lugares, Revista Jardín, Revista Ohlala!, Revista ¡HOLA! Argentina (en adelante, en su conjunto denominados “Productos Editoriales”) en su versión impresa o digital, y que reúna las siguientes características: a) sean personas físicas mayores de dieciocho (18) años de edad y con capacidad para contratar; b) tengan domicilio dentro del territorio de la República Argentina; salvo la suscripción al diario en su versión impresa, quienes deberán tener domicilio en Capital Federal; Gran Buenos Aires, La Plata; Castelli; Chascomús; Lezama; Mercedes; Dolores; Lujan; Carlos Keen; Open Door; San Andrés de Giles; Carmen de Areco; 25 de Mayo; Mar del Plata; Rosario; Gualeguaychú o Ciudad de Córdoba; y dentro de las anteriores localidades, aquellas que estén incluidas en los siguientes códigos postales c) posean tarjeta de crédito y/o debito para el pago de la suscripción a través de débito automático.\r\n\r\n \r\n\r\nAMBA: De 1000 a 1891 | 1893 | 1980 | 2800 | 2804 | 7220 | 7240\r\n\r\nCarmen de Areco: 6725\r\n\r\nCastelli: 7114\r\n\r\nChascomús: 7130 | 7136\r\n\r\nCórdoba: De 5000 a 5021\r\n\r\nDolores: 7100\r\n\r\nLa Plata: De 1892 a 1950\r\n\r\nLezama: 7116\r\n\r\nLuján: 6700 | 6701 | 6702 | 6708\r\n\r\nMar del Plata: De 7600 a 7612 | 7614\r\n\r\nMercedes: 6600 | 6602\r\n\r\nRosario: De 2000 a 2013 | 2107 | 2121 | 2124 | 2132\r\n\r\nSan Andrés de Giles: 6720\r\n\r\n25 de Mayo: 6660 | 7260 | 7261\r\n\r\nGualeguaychú: 2820\r\n\r\n \r\n\r\n1.2. La persona que pretenda ser Socio, deberá completar la solicitud de suscripción (en adelante la \"Suscripción\") del sitio club.lanacion.com.ar (en adelante el \"Sitio\") o por vía de comunicación telefónica con Atención al Cliente al 5199-4700, a fin que el operador complete dicha solicitud.\r\n\r\n1.3. Dentro del plazo de catorce (14) días hábiles de completada la solicitud de Socio, SOCIEDAD ANÓNIMA LA NACION (en adelante \"LA NACION\"), a su exclusivo criterio y decisión determinará si acepta o rechaza la solicitud.\r\n\r\n1.4. De ser aceptada la solicitud de Suscripción por LA NACION, el Suscriptor comenzará a recibir el Diario LA NACION dentro del plazo de 7 días hábiles en su domicilio y con la frecuencia por él requerida al momento de la Suscripción.\r\n\r\n1.5. El rechazo de la solicitud de Suscripción por parte de LA NACION no dará derecho a reclamo alguno, por ningún concepto.\r\n\r\n1.6. El precio de la Suscripción será establecido por LA NACION en concepto de Abono Mensual por el conjunto de Productos Editoriales que el Socio contrate. LA NACIÓN podrá variar el precio de la suscripción, conforme variación de los precios de tapa de los productos editoriales y la recomposición de los restantes precios y costos de su operación. Ver precios actuales de los abonos en el siguiente link:  Ver precios actualizados de los abonos.\r\n\r\n1.7. Las facturas respectivas se emitirán en forma digital, las cuales serán enviadas al Socio a su correo electrónico, estando a su vez disponibles para ser consultadas en el siguiente link: https://micuenta.lanacion.com.ar/mis-facturas.  El Socio podrá además, acceder a las mismas en su cuenta personal, debiendo para ello crear un usuario de registro o loguearse en el caso de ya contar con un usuario registrado. La factura se considerará aceptada si no es impugnada por el Socio Titular dentro de los diez (10) días de recibida.\r\n\r\n1.8.a) Los pagos de la Suscripción a Acceso Digital se harán por mes adelantado y por medio de débito en la tarjeta de crédito y o débito que el Suscriptor haya autorizado a tales efectos.\r\n\r\n1.8.b) Los pagos de la suscripción a los Productos Editoriales  dados de alta hasta el día 27 de mayo 2019 se harán por mes vencido y por medio de débito en la tarjeta de crédito y o débito que Socio haya autorizado a tales efectos.\r\n\r\n1.8.c)  Los pagos de la suscripción a los Productos Editoriales, dados de alta a partir del día 28 de mayo de 2019, inclusive, se harán por mes adelantado y por medio de débito en la tarjeta de crédito y o débito que el Socio haya autorizado a tales efectos.\r\n\r\n1.8.d) Los pagos de la suscripción a los Productos Editoriales, dados de alta a partir de del día 01 de octubre de 2019, inclusive, se harán por mes adelantado y por medio de débito en la tarjeta de crédito y/o débito que el Socio haya autorizado a tales efectos\r\n\r\n\r\nPara todos los casos a) ,b), c) y d) cualquier cambio en la tarjeta de crédito y/o tarjeta de débito con la cual deberá hacerse el pago, deberá ser informado por el Socio a LA NACION comunicándose a Atención al Cliente al 5199-4700. Los mismos serán dados de alta en la base de datos de LA NACION, a los siete (7) días hábiles administrativos de ser informados por el Socio.\r\n \r\n\r\n\r\n1.9. La suscripción a los Productos Editoriales que otorga la calidad de Socio al Club La Nación será por el plazo de doce (12) meses a contar desde la fecha en que LA NACION ha aceptado la misma. Vencido dicho plazo, la suscripción se renovará automáticamente por períodos iguales y sucesivos, salvo que LA NACION y/o el Socio manifestasen lo contrario por medio de una notificación fehaciente, con treinta (30) días de anticipación a la fecha de término de la Suscripción original o sus prórrogas.\r\n\r\n1.10. LA NACION podrá ofrecer la contratación adicional del \"Módulo Feriados\" al Socio interesado, tanto al momento de la contratación como durante la vigencia de la suscripción. La contratación del \"Modulo Feriados\" implica la recepción del diario LA NACION impreso en los días feriados que no coincidan con los días en los cuales el Socio recibe su Producto Editorial de manera corriente. Estos ejemplares serán facturados por separado según el precio informado en la página de Club LN para el módulo feriado que corresponda, cobrándose a través del medio de pago que el Socio haya autorizado a tales efectos. El servicio está disponible para los domicilios de entrega habilitados al momento de realizar la contratación. La entrega se realizara conforme al cronograma de feriados nacionales anuales oficiales, con excepción del 1º de enero, viernes santo, 1º de mayo, 7 de noviembre y 25 de Diciembre. \r\n\r\n1.11. Quedará a elección del Socio aceptar la contratación del \"Módulo Feriados\" y en caso de aceptarla, podrá solicitar su baja en cualquier momento durante la vigencia de su suscripción, en cuyo caso seguirá recibiendo los ejemplares de Diario correspondientes a su suscripción contratada. Este modulo se aplica exclusivamente a los Productos Editoriales impresos.\r\n\r\n1.12. El Socio podrá requerir la suspensión de la  Suscripción por el plazo máximo de 90 días totales por año calendario, debiendo notificar ello a LA NACION, ingresando la solicitud en club.lanacion.com.ar y solo por una vez dentro del período de 12 meses. LA NACION suspenderá el servicio a los siete (7) días hábiles administrativos de haber sido informada. Esta opción se aplica exclusivamente a la suscripción de los Productos Editoriales impresos.\r\n\r\n1.13. LA NACION podrá suspender la suscripción que otorgará la calidad de Socio del Club LN por: a) falta de pago de las facturas por el Socio dentro del plazo de vencimiento establecido en las mismas; b) por rechazo de las tarjetas de crédito y/o débito al débito del importe de las mismas; c) imposibilidad de entregar el Diario LA NACION en el domicilio del Socio\r\n\r\n1.14.  LA NACION, podrá terminar la suscripción, sin expresar causa alguna y en forma unilateral y en cualquier momento, con un preaviso al Socio de treinta (30) días de antelación, que será notificado por medio de notificación escrita, quedando cumplida esta obligación con la notificación vía mail.\r\n\r\n1.15. Serán causales de terminación de la suscripción: a) la finalización del plazo de vigencia de la misma; b) por rescisión anticipada ejercida por LA NACION y/o el Socio; c) por falta de pago de la suscripción; d) por fallecimiento del Socio; e) por uso indebido de las normas previstas en el presente; y f) resolución\r\n \r\n\r\n2) PROGRAMA DE BENEFICIOS CLUB LA NACION.\r\n \r\n2.1.ADHESION AL PROGRAMA:\r\n\r\n2.1.1. Es condición necesaria para asociarse al Programa de Beneficios Club La Nación (en adelante, “el Programa”) ser socio de alguno/s de los Productos Editoriales, salvo para los casos de suscripciones corporativas.\r\n\r\n2.1.2. El Socio de alguno/s de los Productos Editoriales podrá adherirse al Programa de beneficios manifestando su voluntad al momento de concretar la suscripción de alguno/s de dichos Productos Editoriales (en adelante el/los \"Socio Titular\"). A tal fin, es posible que se le solicite información personal adicional a la requerida para la Suscripción.\r\n\r\n2.1.3. En función a los Productos Editoriales a los cuales el Socio se suscriba, conforme lo estipulado en el punto 2.2. de los presentes Términos y Condiciones, el Socio accederá a un número determinado de tarjetas para el uso de las mismas por parte de quien el Socio determine al momento de la contratación. Las tarjetas serán provistas por LA NACION al Socio que adhiera al Programa.\r\n\r\n2.1.4. La aceptación de la adhesión al Programa que solicite el Socio, se materializará con la entrega de las correspondientes Tarjetas.\r\n\r\n2.1.5. LA NACION, podrá a su exclusivo criterio, rechazar cualquier solicitud de adhesión al Programa.\r\n\r\n\r\n2.2. TARJETA CLUB LA NACION\r\n\r\n2.2.1. Existen tres (3) categorías de Tarjetas: negras, azules y celestes, las que darán acceso a diferentes tipos de beneficios, que serán anunciados en cada oportunidad, y su otorgamiento quedará supeditado a las diferentes opciones de Suscripción de alguno/s de los Producto/s Editoriales de LA NACIÓN, según se establece a continuación.\r\n\r\n​\r\n\r\n\r\n2.2.2. El Socio que haya accedido a la Tarjeta celeste gozará de los beneficios del Club LA NACION Classic (en adelante \"Socio Classic\"), el Socioque haya accedido a la Tarjeta azul, gozará de los beneficios del Club LA NACION Premium (en adelante \"Socio Premium\") y el que haya accedido a la Tarjeta negra, gozarán de los beneficios del Club LA NACION Black (en adelante \"Socio Black\"), los cuales podrán ser consultados en el Sitio.\r\n\r\n2.2.3. Las Tarjetas referenciadas, son para la identificación del Socio y quienes él designe, y contienen una banda magnética que los habilita a operar en las terminales de puntos de venta (en adelante \"POS\") ubicados en los establecimientos adheridos al Programa de beneficios y permitirá registrar la utilización de los beneficios por parte del Socioy quien él designe, en la compra o contratación que se realice en los establecimientos adheridos al Programa.\r\n\r\n2.2.4. Las Tarjetas son personales e intransferibles y sólo podrán ser utilizadas por la persona a cuyo nombre estén extendidas.\r\n\r\n2.2.5. Las Tarjetas serán enviadas al Socioal domicilio fijado en la Suscripción de los Productos Editoriales, dentro de los treinta (30) días de haberse el Socio adherido a los mismos.\r\n\r\n2.2.6. Las Tarjetas no tienen fecha de vencimiento, por lo cual se mantendrán vigentes durante el plazo de la Suscripción.\r\n\r\n2.2.7. Las Tarjetas no son tarjetas de crédito, ni de compra, ni de débito, no sirven como medio de pago y son de exclusiva propiedad de LA NACION.\r\n\r\n2.2.8. LA NACION se reserva el derecho de solicitar la devolución de las Tarjetas cuando se haga un uso indebido o inconveniente de las mismas. Asimismo, LA NACION se reserva el derecho de solicitar la devolución de las Tarjetas por cualquier otro motivo que perjudique el normal desarrollo del Programa.\r\n\r\n2.2.9. Se considerarán Tarjetas ilegalmente obtenidas a las que lo sean por cualquier medio o procedimiento no autorizado por LA NACION. Estas quedarán fuera del Programa y no gozarán de los beneficios que el mismo brinda a las Tarjetas autorizadas. LA NACION se reserva el derecho de iniciar las acciones legales que correspondan contra cualquier persona que intente un uso fraudulento o indebido de una Tarjeta.\r\n\r\n2.3) BENEFICIOS.\r\n\r\n2.3.1. El Socio y quien él designe para el uso de las demás tarjetas obtenidas con la suscripción, podrá acceder a beneficios en la compra de productos o contratación de servicios en los establecimientos adheridos a este Programa (en adelante los \"Establecimientos Adheridos\") los que serán anunciados en cada oportunidad en el Sitio, distinguiéndose entre los beneficios que serán otorgados al Socio Classic, Premium o Black, según corresponda, y estarán sujetos a disponibilidad y vencimiento.\r\n\r\n2.3.2. Los beneficios serán otorgados al Socio, exclusivamente por los propietarios de los Establecimientos Adheridos, al momento de la adquisición de un bien o contratación del servicio en dicho establecimiento. Los beneficios podrán consistir en regalos, descuentos variables en la adquisición de bienes o contratación de servicios, vendidos o provistos por los Establecimientos Adheridos, los cuales serán informados oportunamente a los Socios por medio del Sitio y por cualquier otro medio que LA NACION considere adecuado (en adelante los \"Beneficios\"). En algunos casos se establecerá el límite disponible para utilizar el Beneficio por parte de los Socios, comunicándose a los mismos el stock disponible en cada ocasión. En consecuencia, LA NACION, no se responsabiliza por el efectivo uso, la mala utilización o la imposibilidad de utilización sobreviviente de los Beneficios.\r\n\r\n2.3.3. Existirán beneficios exclusivos tanto para los Socios  Black, como para los Socios Premium o Classic. Los Socios Classic y Premium no podrán acceder a los beneficios del Socio Black. Asimismo, los Socios Classic no podrán acceder a los beneficios del Socio Premium.\r\n\r\n2.3.4. En ningún caso y bajo ninguna circunstancia estará permitido canjear Beneficios por dinero en efectivo.\r\n\r\n \r\n\r\n2.4) MODO DE USO DE LAS TARJETAS Y ACCESO A LOS BENEFICIOS.\r\n\r\n2.4.1. El Socio y quien él designe para el uso de las demás tarjetas obtenidas por la suscripción, podrá acceder a los Beneficios con la exhibición de la Tarjeta al momento de efectuar una compra y/o contratar un servicio en los Establecimientos Adheridos, junto con la exhibición de la cédula de identidad o el documento nacional de identidad y previa validación de la tarjeta en la terminal \"POS\" de dichos establecimientos. El acceso al Beneficio es aplicable cualquiera fuera la forma de pago admitida por el Establecimiento Adherido.\r\n\r\n2.4.2. El Socio y quien él designe para el uso de las demás tarjetas obtenidas por la suscripción, sólo podrá obtener el Beneficio otorgado por cada Establecimiento Adherido si presenta la Tarjeta antes de la emisión de la factura correspondiente por parte del Establecimiento Adherido por el bien adquirido o servicio contratado..\r\n\r\n2.4.3. En caso que el POS del Establecimiento Adherido se encuentre fuera de servicio por cualquier motivo, dicho Establecimiento Adherido, deberá confeccionar un comprobante de forma manual otorgando el Beneficio correspondiente.\r\n\r\n2.4.4. Los Beneficios no podrán ser transferidos por el Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, a terceros, ni a otro Socio.\r\n\r\n \r\n\r\n2.5) PROMOCIONES Y SORTEOS. ACCIONES PUBLICITARIAS.\r\n\r\n\r\n2.5.1. En forma directa o a través de terceros LA NACION, con la frecuencia que establezca a su exclusivo criterio, podrá realizar promociones y sorteos de bienes y/o servicios, de conformidad con las bases y condiciones que se determinen en cada oportunidad.\r\n\r\n2.5.2. En las promociones y sorteos, no podrán participar empleados y contratados directos de LA NACION, así como tampoco sus familiares parientes por consanguinidad o afinidad en segundo grado.\r\n\r\n2.5.3. Adicionalmente, LA NACION podrá emprender acciones publicitarias y promocionales con terceras personas ajenas o no al Programa, a fin de acercarles alos Socios diversa información, ofertas y beneficios que pueden resultar de su interés.\r\n\r\n2.5.4. En caso de entrega de Beneficios por parte del presente Programa, los mismos estarán sujetos a la disponibilidad del stock que se establecerá en cada caso. Ninguno de los Beneficios podrá ser canjeado por dinero en efectivo.\r\n\r\n2.5.5. Dentro de cada una de las categorías de Socio Classic, Premium y Black, podrán existir sorteos y promociones exclusivas para cada una de ellas o para todas a la vez, sin que ello dé derecho a uno de los Socio es de una categoría a participar en la otra.\r\n \r\n\r\n2.6) CANCELACION.\r\n \r\n\r\n2.6.1. LA NACION podrá cancelar o finalizar cualquier adhesión al Programa sin aviso previo, y sin que ello genere derecho a reclamo o indemnización alguna a favor del Socio, en los supuestos que se detallan a continuación: a) si no cumpliera con cualquiera de los Términos y Condiciones; b) si abusare de cualquier privilegio concedido bajo el presente Programa; c) si proveyera cualquier información falsa a LA NACION o a cualquier Establecimiento Adherido; d) si pretendiese vender a terceros los Beneficios obtenidos u obtuviere Beneficios de manera indebida, contrariando los presentes Términos y Condiciones; e) se atrasase en el pago de cualquiera de los Productos Editoriales a los cuales se encuentra Suscripto.\r\n\r\n \r\n\r\n2.6.2. El socio podrá solicitar la baja de la suscripción a través de los siguientes medios:\r\n\r\n \r\n\r\nComunicándose a nuestro centro de atención telefónica de lunes a viernes de 8:00 a 20:00 hs al (011) 5199-4700 o ingresando a micuenta.lanacion.com.ar en la sección suscripciones. \r\n\r\n​\r\n\r\n2.6.3. El Socio que cancele su Suscripción al Producto Editorial que le otorga la categoría de Socio Black, dejará de ser SocioBlack, sin perjuicio de la facultad de adherirse a la categoría de Socio Classic o Premium, si cumpliese con los requisitos dispuestos en el punto 2.2.1. y 2.2.2. de los presentes Términos y Condiciones. Por su parte, el Socio Premium, que cancele su Suscripción al Producto Editorial que le otorga dicha categoría, dejará de ser Socio Premium. Las Tarjetas del Socio quedarán automáticamente inhabilitadas a partir de la fecha de baja de la Suscripción. Asimismo, el Socio Classic, que cancele su Suscripción al Producto Editorial que le otorga dicha categoría, dejará de ser Socio Classic. Las Tarjetas del Socio quedarán automáticamente inhabilitadas a partir de la fecha de baja de la Suscripción.\r\n\r\n \r\n\r\n2.6.4. LA NACION podrá terminar el Programa en cualquier momento, notificando dicha decisión con un mínimo de treinta (30) días de anticipación a la fecha de terminación a través del envió de un email a la dirección de correo electrónico y/o por medio de una comunicación al domicilio que el Socio haya establecido al momento de adherirse al Programa y/o que haya modificado con posterioridad y conste en el registro de Socios de la base de datos del Programa, y/o por cualquier medio masivo de comunicación. La notificación al Socio del término del Programa, será extensiva y válida para todas sus Tarjetas.\r\n\r\n \r\n\r\n2.6.5. Finalizada la adhesión al Programa por cualquier causa, la información relativa al Socioy/o a quienes éste haya designado para el uso de las demás tarjetas obtenidas por la suscripción, permanecerá en la base de datos de LA NACION.\r\n\r\n​\r\n \r\n\r\n3) RECLAMOS.\r\n \r\n3.1. Los Socios adheridos al Programa podrán efectuar cualquier reclamo relacionado con la Suscripción y con el Programa, llamando al 5199-4700 o ingresando en http://club.lanacion.com.ar/atencion dentro del plazo de noventa (90) días corridos, de ocurrida la causa que motivo el mismo.\r\n\r\n3.2. Sin perjuicio de no existir responsabilidad alguna de LA NACION por el otorgamiento de los Beneficios por parte de los Establecimientos Adheridos, frente al supuesto que el reclamo tuviese como causa la falta de otorgamiento de algún Beneficio por medio de dichos Establecimientos Adheridos, el Socio al momento de efectuar el reclamo telefónico al 5199-4700 o ingresando en http://club.lanacion.com.ar/atencion, deberá brindar en el plazo fijado en el punto 4.1. precedente, la información referente a la operación (ya sea transacción, compra de bienes o servicios, etc.) que generó dicho reclamo, a fin de ser comunicado por LA NACION a los Establecimientos Adheridos para que tomen las medidas correspondientes.\r\n \r\n\r\n4) CONDICIONES GENERALES.\r\n​\r\n4.1. En cualquier momento, LA NACION podrá efectuar cambios en los presentes Términos y Condiciones, en las condiciones de la Suscripción a los Productos Editoriales, en la denominación del Programa y su logo, en los Beneficios incluidos en el Programa y en las condiciones de acceso a dichos Beneficios y la vigencia de los mismos, así como en las condiciones para adherirse a la categoría de SocioClassic, Premium o Black, y características de los Establecimientos Adheridos. Las modificaciones mencionadas precedentemente, podrán ser informadas al Socio por cualquier medio masivo de comunicación, a través del Sitio o de cualquier otro que implique su difusión pública, a elección de LA NACION.\r\n\r\n4.2. Los datos y ofertas relativas a los Beneficios comunicados por LA NACION revisten un carácter exclusivamente informativo y en modo alguno suponen que los establecimientos, productos y/o servicios indicados, y la calidad de los mismos, son responsabilidad de LA NACION. La información referida a los Establecimientos Adheridos, incluidas sus características, marcas, logos y foto/s, es suministrada exclusivamente por cada uno de dichos establecimientos, en consecuencia, LA NACION no es responsable del contenido, o autenticidad o veracidad de dicha información.\r\n\r\n4.3. Los Establecimientos Adheridos no tienen la autoridad, expresa o implícita, para formular ninguna declaración, manifestación ni ofrecer garantías en nombre de LA NACION o del Programa, y en consecuencia ni LA NACION ni el Programa asumen ninguna responsabilidad en relación a tales declaraciones, manifestaciones o garantías.\r\n\r\n4.4. LA NACION no será responsable por los daños causados al Socio en ocasión de la utilización de los Beneficios, responsabilidad que será directa y exclusivamente asumida por los Establecimientos Adheridos que prestan el servicio o comercializan el bien en su caso.\r\n\r\n4.5. Los datos del Socio y/o quienes él haya designado en las Tarjetas para el uso de las demás tarjetas obtenidas por la suscripción,  y la referida a las transacciones que resulten en la utilización de las Tarjetas (en adelante la \"Información\"), serán incluidos en una base de datos inscripta en el Registro Nacional de Bases de Datos Personales por LA NACION (en adelante la \"Base de Datos\"). La Información estará a disposición de LA NACION para su utilización y la de los Establecimientos Adheridos autorizados por LA NACION, con fines publicitarios, promocionales y comerciales. LA NACION utilizará los datos para conocer los intereses y/o afinidades delSocioy/o de quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, de tal forma que los Beneficios se adecuen a los intereses de los mismos y para el máximo rendimiento del Programa.\r\n\r\n4.6. El Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, expresamente aceptan y dan su consentimiento para:\r\n(a) Proveer la Información solicitada para suscribirse a alguno de los Productos Editoriales y adherirse al Programa y autoriza a LA NACION al acceso, conservación y tratamiento de la Información allí contenida.\r\n(b) Que cada Establecimiento Adherido revele a LA NACION y/o a sus agentes o dependientes la Información referida a las transacciones que realicen el Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, a los fines anteriormente indicados.\r\n(c) Que LA NACION trate y/o transfiera a sus sociedades controladas y/o vinculadas (Publirevistas S.A.; El Jardín en la Argentina S.A.; Covedisa S.A.; Eglam Argentina S.A., Buenos Aires Arena S.A.) y/o a sus anunciantes y/o a sus agentes y/o Establecimientos Adheridos la Información contenida en su Base de Datos con fines publicitarios y/o promocionales.\r\n(d) Que LA NACION trate y/o transfiera la Información a los Establecimientos Adheridos, a fin de que los mismos le envíen al Socio diversa información, ofertas y Beneficios que pueden resultar de su interés.\r\n(e) Que frente a un reclamo de un Socio, y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, o cualquier autoridad Administrativa o Judicial LA NACION utilice la Información obrante en su Base de Datos.\r\n\r\n4.7. El Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, declaran y aceptan que los datos que sean recopilados por LA NACION a través de la Suscripción a los Productos Editoriales y/o al Programa, sean utilizados de conformidad con el artículo 9 de la Ley 25.326 y su reglamentación.\r\n\r\n4.8. El Socio, titular de los datos personales, tiene la facultad de ejercer el derecho de acceso a los mismos en forma gratuita a intervalos no inferiores a seis (6) meses, salvo que se acredite un interés legítimo al efecto, conforme lo establecido en el artículo 14, inciso 3 de la Ley Nº 25.326 (Disposición 10/2008). La Dirección Nacional de Protección de Datos Personales, Órgano de control de la Ley referenciada, tiene la atribución de atender las denuncias y reclamos que se interpongan con relación al cumplimiento de las normas sobre protección de datos personales.\r\n\r\n4.9. El Socio, podrá requerir en cualquier momento la actualización, rectificación y/o supresión cuando corresponda de los datos personales de los cuales sea titular, de conformidad con lo dispuesto por el artículo 16 de la Ley Nº 25.326 y su reglamentación.\r\n\r\n4.10. Cualquier comunicación cursada por LA NACION a un Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción,se considerará notificada si fue remitida al domicilio del mismo o a la dirección de correo electrónico obrante en la Base de Datos.\r\n\r\n4.11. El Socio, al suscribirse y adherirse al Programa, brinda su conformidad y autoriza a LA NACION a enviarle y trasmitirle todo tipo de comunicaciones, avisos y mensajes que guarden relación con la Suscripción y/o el Programa y con los fines publicitarios, comerciales y promocionales a los domicilios, como así también a las direcciones de correo electrónico y teléfonos, que se encuentren registrados en la Base de Datos. El podrá revocar dicha autorización manifestando por escrito al domicilio de LA NACION sito en Zepita 3251, C.A.B.A. o por teléfono al 5199-4700 o por correo electrónico, su expreso deseo de no recibir aquellas comunicaciones. Esta autorización, además, obra como consentimiento expreso del Socio para recibir todo tipo de comunicaciones, avisos y mensajes que guarden relación con la Suscripción y/o el Programa y con los fines publicitarios, comerciales y promocionales, vía e-mail, no aplicándose a LA NACION en los términos de este Reglamento, la eventual registración del Socio en el Registro No Llame, creado por ley N°2014 de la Ciudad Autónoma de Buenos Aires.\r\n\r\n4.12. El Programa será válido únicamente en los puntos de venta de los Establecimientos Adheridos. El Programa podrá ser extendido a otros países.\r\n\r\n4.13. Cualquier exclusión o limitación de responsabilidad contenida en los presentes Términos y Condiciones, en favor de LA NACION se extiende a cada uno de sus Socio es, empleados, directores, gerentes y sus personas jurídicas vinculadas y filiales conforme el significado dado por la Ley Nº 19.550 de Sociedades.\r\n\r\n4.14. En ningún caso LA NACION será responsable, por la utilización indebida que pudieran hacer terceros de las Tarjetas, ni por los daños y perjuicios que tal circunstancia pudiera ocasionar al Socio y/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la Socio, y/o a los Establecimientos Adheridos. En este sentido LA NACION, no responderá en caso de robo, hurto, pérdida o extravío de las Tarjetas, ni ningún uso por extraños empleando impropiamente las mismas, o en cualquier otra que contraríe la voluntad del Socio.\r\n\r\n4.15. El robo, hurto, extracción, pérdida o deterioro sustancial de las Tarjetas deberá ser denunciado, de manera inmediata por el socio al teléfono 5199-4700 o en el lugar donde informe oportunamente LA NACION. La responsabilidad de LA NACION se limitará a la reposición de la Tarjeta robada, hurtada, perdida o deteriorada dentro de los cuarenta y cinco (45) días de efectuada la denuncia. LA NACION no se responsabiliza por demoras por causas no imputables a LA NACION en el reemplazo de una Tarjeta o por el uso fraudulento de la misma.\r\n\r\n4.16. La eventual nulidad de alguna de las cláusulas de los presentes Términos y Condiciones, no importará la nulidad de las restantes cláusulas.\r\n\r\n4.17. Cualquier impuesto, tasa, derecho, contribución u obligación aplicable como consecuencia de la participación de un Socio en el Programa estará a cargo exclusivo del Socio Titular y Adicional.\r\n\r\n4.18. Cualquier cuestión que se suscite con el Socio, en relación a la Suscripción y/o el Programa, será resuelto en forma definitiva e inapelable por LA NACION.\r\n\r\n4.19. La Suscripción implica la aceptación de los presentes Términos y Condiciones, los que se reputan conocidos por el Socioy/o quienes él haya designado para el uso de las demás tarjetas obtenidas por la suscripción, adheridos al Programa.\r\n\r\n4.20. LA NACION tiene su domicilio comercial en Edificio Torre al Río - Libertador 101, Vicente López, Provincia de Buenos Aires.\r\n\r\n4.21. El Socio y LA NACION, acuerdan someter cualquier disputa o divergencia derivada de los presentes Términos y Condiciones a la jurisdicción y competencia de los Tribunales Ordinarios en lo Comercial con asiento en la Ciudad Autónoma de Buenos Aires.','2025-07-20',1,2,NULL,1,'2025-07-20 13:35:37','2025-07-20 13:38:22'),
	(4,'Preguntas Frecuentes','preguntas-frecuentes','¿Tenes dudas? Despejalas aca','REGISTRO E INGRESO\r\n¿Para qué sirve registrarme?\r\nPara poder realizar una compra desde la web de 365 o bien ingresar a Mi suscripción, es obligatorio que te registres con tu cuenta de email o tu red social; de esta forma, vamos a poder brindarte una mejor experiencia de uso y proteger mejor tus datos personales.\r\nAl crear tu cuenta, podrás disfrutar además de los siguientes beneficios:\r\n\r\nPersonalizar tu experiencia: recibir información de calidad por e-mail, y contenidos personalizados, productos innovadores y servicios de excelencia acordes a tus intereses y preferencias.\r\n\r\nComentar las notas de los sitios editoriales: comentar y compartir las notas de Clarín y Olé.\r\n\r\nParticipar en concursos y promociones especiales: acceder a distintas promociones y a la posibilidad de obtener beneficios exclusivos.\r\n\r\nVer transmisiones en vivo: disfrutar de manera exclusiva eventos, entrevistas, y shows de reconocidas celebridades.\r\n\r\n¿Tengo que pagar para registrarme?\r\nNo, la registración es sin cargo.\r\n¿Cómo me registro?\r\nEs fácil, rápido y debés hacerlo por única vez. Para registrarte ingresá al link Mi suscripción o bien en el proceso de compra desde el botón PEDÍ TU TARJETA, donde se desplegará una ventana de ingreso a través de la cual podrás iniciar la creación de tu cuenta. Podés registrarte con tu usuario de Facebook, tu cuenta de Google, de Apple o creando una cuenta en nuestro sitio. En caso que hayas creado una cuenta nueva en nuestro sitio, para poder activarla, debés ingresar al correo electrónico que te enviamos con el asunto \"Verificá tu cuenta\" y hacer clic en el botón \"Activar mi cuenta\". Una vez activada, te aparecerá una pantalla dándote la bienvenida y contándote los beneficios de haber activado tu cuenta.\r\n¿Qué hago si no recibo el email de validación de cuenta?\r\nEn primer lugar podés revisar en tu carpeta de SPAM de tu casilla de correo. De no haberlo recibido, debés loguearte nuevamente en nuestro sitio online de ventas y hacer clic en el botón “Reenviar Email de Validación” que se encuentra al lado del mensaje “Tu correo electrónico no fue validado” y se te enviará automáticamente el email para que puedas validar tu cuenta.\r\nSi ya estoy registrado, ¿cómo inicio sesión?\r\nSi ya te registraste anteriormente en 365 o en alguno de los sitios editoriales de Clarín, simplemente conectate a través de tu red social o completá los datos (e-mail y contraseña) de la cuenta generada previamente.\r\nTengo iOS, ¿cómo hago para habilitar las cookies en Safari?\r\nSi estás navegando desde tu PC, dependiendo de la versión de tu Safari:\r\n\r\nHacé clic en el Menú de “Ajustes generales” o en el Menú “Safari”\r\n\r\nSeleccioná “Preferencias”\r\n\r\nHacé clic en la pestaña “Privacidad”\r\n\r\nSi la opción es “Bloquear cookies” seleccioná “Nunca” (*) /Si la opción es “Cookies y datos de sitios web”, seleccioná “Permitir siempre” (*)\r\n\r\n\r\nSi estás navegando desde tu teléfono celular o tablet:\r\n\r\nHacé clic en “Ajustes”\r\n\r\nSeleccioná “Safari”\r\n\r\nSeleccioná “Permitir siempre” o “Nunca” en la opción “Bloquear cookies”, según la versión del sistema operativo que tengas instalada en tu dispositivo (*).\r\n\r\n\r\n(*) Safari permite a todos los sitios web que visite, a terceros y a la publicidad, almacenar cookies y otros datos en el Mac.\r\n¿Qué tengo que hacer si olvidé mi contraseña?\r\nSi no recordás tu contraseña podés recuperarla haciendo clic en el link “¿Olvidaste tu contraseña?” que se encuentra en la ventana de ingreso de logueo luego de haber completado el mail y haber hecho clic en el botón \"Continuar\", y completá los datos requeridos para recuperarla.\r\n¿Puedo modificar mi contraseña?\r\nSí. Para modificar tu contraseña, tenés que hacer clic en el link \"¿Olvidaste tu contraseña?\" que se encuentra en la ventana de ingreso de logueo luego de haber completado el mail y haber hecho clic en el botón \"Continuar\", y completá los datos requeridos para modificarla.\r\n¿Tenés más consultas?\r\nSi aún tenés dudas de cómo registrarte, comunicate con nuestro Centro de Atención al Cliente al 0810.333.0365 o al 0800-222-2365 de lunes a viernes de 8 a 20hs. También podés escribirnos a la casilla de mail Contacto365@agea.com.ar','2025-07-20',1,2,NULL,1,'2025-07-20 13:43:14','2025-07-20 13:43:14'),
	(5,'Devoluciones','devoluciones','En El Tribuno, queremos que tu experiencia de compra sea satisfactoria. Si por algún motivo no estás conforme con tu compra, podés solicitar un cambio o devolución siguiendo las condiciones que detallamos a continuación.','1. Plazo para solicitar una devolución\r\n\r\nTenés hasta 10 días corridos desde la fecha de recepción del producto para solicitar una devolución o cambio. Pasado este período, no podremos garantizar la aceptación del reclamo.\r\n\r\n2. Condiciones del producto\r\n\r\nPara poder gestionar la devolución o cambio, el producto debe encontrarse en las siguientes condiciones:\r\n	•	Sin uso, en el mismo estado en que fue entregado.\r\n	•	Con su empaque original, etiquetas, accesorios y manuales completos.\r\n	•	Acompañado del comprobante de compra (factura o ticket).\r\n\r\nLos productos que no cumplan con estas condiciones no podrán ser devueltos.\r\n\r\n3. Productos excluidos\r\n\r\nNo se aceptan devoluciones de:\r\n	•	Productos personalizados o confeccionados a medida.\r\n	•	Productos de uso íntimo, higiene o consumo.\r\n	•	Productos en liquidación o con descuento, salvo que presenten fallas.\r\n\r\n4. Motivos de devolución\r\n\r\nPodés solicitar una devolución en los siguientes casos:\r\n	•	Producto defectuoso o dañado: si el artículo presenta fallas de fabricación o llegó dañado durante el envío.\r\n	•	Producto incorrecto: si recibiste un producto diferente al solicitado.\r\n	•	Desistimiento de compra: si simplemente cambiaste de opinión (dentro del plazo establecido y cumpliendo las condiciones mencionadas).\r\n\r\n5. Procedimiento\r\n	1.	Contactanos por email a [email@tienda.com] o por WhatsApp al [número], indicando el número de pedido y el motivo de la devolución.\r\n	2.	Te enviaremos las instrucciones para el envío del producto.\r\n	3.	Una vez recibido y verificado, procesaremos el cambio o reintegro del dinero.\r\n\r\n6. Reintegro del dinero\r\n\r\nEl reembolso se realizará por el mismo medio de pago utilizado en la compra. El tiempo de acreditación puede variar según la entidad emisora del pago.\r\n\r\n7. Costos de envío\r\n	•	Si la devolución se debe a un error nuestro o a un defecto del producto, nosotros cubrimos los costos de envío.\r\n	•	En caso de desistimiento, los costos de envío corren por cuenta del comprador.','2025-10-27',1,2,NULL,1,'2025-10-27 17:05:44','2025-10-27 17:05:44'),
	(6,'Quienes Somos','quienes-somos','Somos el tribuno','texto de quienes somos','2025-10-27',1,2,NULL,1,'2025-10-27 17:06:27','2025-10-27 17:06:27'),
	(7,'Politicas de privacidad','politicas-de-privacidad','En El Tribuno valoramos tu confianza y nos comprometemos a proteger tu privacidad. Esta Política explica cómo recopilamos, usamos y resguardamos tu información personal cuando navegás por nuestro sitio o realizás una compra.','1. Información que recopilamos\r\n\r\nPodemos recopilar los siguientes datos personales cuando utilizás nuestro sitio:\r\n	•	Nombre y apellido\r\n	•	Dirección de correo electrónico\r\n	•	Domicilio de envío y facturación\r\n	•	Número de teléfono\r\n	•	Información de pago (procesada de forma segura a través de pasarelas de pago externas)\r\n	•	Datos de navegación, como dirección IP, tipo de dispositivo, navegador y páginas visitadas\r\n\r\nToda la información se recopila con tu consentimiento y únicamente para fines legítimos relacionados con tu experiencia de compra.\r\n\r\n⸻\r\n\r\n2. Uso de la información\r\n\r\nUtilizamos tus datos personales para:\r\n	•	Procesar y gestionar tus pedidos\r\n	•	Enviarte confirmaciones, facturas y actualizaciones del estado de tu compra\r\n	•	Mejorar nuestros productos, servicios y atención al cliente\r\n	•	Enviarte promociones, novedades o comunicaciones comerciales (solo si aceptás recibirlas)\r\n	•	Cumplir con obligaciones legales o requerimientos de las autoridades competentes\r\n\r\n⸻\r\n\r\n3. Protección de los datos\r\n\r\nImplementamos medidas de seguridad físicas, electrónicas y administrativas para proteger tus datos personales frente a accesos no autorizados, pérdida, alteración o divulgación indebida.\r\nTus datos se almacenan en servidores seguros y el intercambio de información sensible (como datos de pago) se realiza mediante conexiones cifradas (SSL).\r\n\r\n⸻\r\n\r\n4. Compartición de información\r\n\r\nNo vendemos, alquilamos ni intercambiamos tus datos personales.\r\nSolo compartimos la información necesaria con:\r\n	•	Proveedores de servicios logísticos o de pago, para procesar envíos y transacciones.\r\n	•	Autoridades competentes, cuando sea requerido por ley o en cumplimiento de obligaciones legales.\r\n\r\nEn todos los casos, exigimos a nuestros proveedores el cumplimiento de normas de confidencialidad y seguridad equivalentes.\r\n\r\n⸻\r\n\r\n5. Derechos del usuario\r\n\r\nPodés ejercer en cualquier momento los derechos de:\r\n	•	Acceso: saber qué datos tuyos tenemos.\r\n	•	Rectificación: corregir datos inexactos o incompletos.\r\n	•	Cancelación: solicitar la eliminación de tus datos cuando ya no sean necesarios.\r\n	•	Oposición: oponerte al uso de tus datos con fines comerciales.\r\n\r\nPara ejercer estos derechos, escribinos a [email@tienda.com] con el asunto “Protección de Datos Personales”.\r\n\r\n⸻\r\n\r\n6. Cookies\r\n\r\nNuestro sitio utiliza cookies para mejorar la experiencia del usuario, analizar el tráfico y personalizar contenidos.\r\nPodés configurar tu navegador para rechazar cookies, aunque esto podría afectar el funcionamiento de algunas funciones del sitio.\r\n\r\n⸻\r\n\r\n7. Cambios en la Política de Privacidad\r\n\r\nNos reservamos el derecho de modificar esta política en cualquier momento. Las actualizaciones se publicarán en esta misma sección, indicando la fecha de la última modificación.\r\n\r\n⸻\r\n\r\n8. Contacto\r\n\r\nSi tenés dudas o comentarios sobre esta Política de Privacidad, podés comunicarte con nosotros a:\r\n📧 [email@tienda.com]\r\n📞 [Teléfono]','2025-10-27',1,2,NULL,1,'2025-10-27 17:07:23','2025-10-27 17:07:23');

/*!40000 ALTER TABLE `blog_posts` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla cache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla cache_locks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla cart_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cart_items`;

CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `attribute_values_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attribute_values_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_foreign` (`cart_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla carts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `carts`;

CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_customer_id_foreign` (`customer_id`),
  CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;

INSERT INTO `carts` (`id`, `customer_id`, `session_id`, `is_active`, `completed_at`, `notes`, `created_at`, `updated_at`)
VALUES
	(1,NULL,'hWwuMsbyd2MeFTNL1ubJ5o3hcxTZkWQwR0qsAGMD',1,NULL,NULL,'2026-02-21 13:56:59','2026-02-21 13:56:59'),
	(2,NULL,'MVfx2Mcz2T8CFuWcziurctTx1JnVSRAEfXcJsn9a',1,NULL,NULL,'2026-02-22 12:52:13','2026-02-22 12:52:13'),
	(3,NULL,'7ozCw9tX96UWgzq8qWgWA3G7x5d7U4PLg8zT209M',1,NULL,NULL,'2026-02-22 13:48:12','2026-02-22 13:48:12'),
	(4,NULL,'slz0N7OewAMu4ard8uJYzBNSZ4cJmaHIdq8GsehK',1,NULL,NULL,'2026-02-22 13:49:10','2026-02-22 13:49:10'),
	(5,NULL,'Hnb3m4jvieLsI0O64tEmx2Nuy8GV1gC16QPkQIGy',1,NULL,NULL,'2026-02-22 13:53:54','2026-02-22 13:53:54'),
	(6,NULL,'gsVCERBvSeGjtrt8E0Fdrhrg9gl2gSY11hOfOx18',1,NULL,NULL,'2026-02-22 14:04:27','2026-02-22 14:04:27'),
	(7,NULL,'rinaoUVKFr25bGvRxxQum8CxupBgo3c0q66Jp2YT',1,NULL,NULL,'2026-02-22 14:05:04','2026-02-22 14:05:04');

/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;

INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `order`, `image`, `icon`, `parent_id`, `is_active`, `created_at`, `updated_at`)
VALUES
	(1,'Nivel Inicial',NULL,'nivel-inicial',0,NULL,NULL,NULL,1,'2026-02-21 13:57:26','2026-02-21 13:57:26'),
	(2,'Nivel Primario',NULL,'nivel-primario',0,NULL,NULL,NULL,1,'2026-02-21 13:57:41','2026-02-21 13:57:41'),
	(3,'Nivel Secundario',NULL,'nivel-secundario',0,NULL,NULL,NULL,1,'2026-02-21 13:57:46','2026-02-21 13:57:58');

/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla category_product
# ------------------------------------------------------------

DROP TABLE IF EXISTS `category_product`;

CREATE TABLE `category_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_product_product_id_foreign` (`product_id`),
  KEY `category_product_category_id_foreign` (`category_id`),
  CONSTRAINT `category_product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla countries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO `countries` (`id`, `name`, `created_at`, `updated_at`)
VALUES
	(1,'Argentina','2025-07-29 22:24:00','2025-07-29 22:24:00');

/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla customer_addresses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customer_addresses`;

CREATE TABLE `customer_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `locality_id` bigint(20) unsigned DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_addresses_customer_id_foreign` (`customer_id`),
  KEY `customer_addresses_locality_id_foreign` (`locality_id`),
  CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_addresses_locality_id_foreign` FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla customer_billing_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customer_billing_data`;

CREATE TABLE `customer_billing_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `document_number` varchar(255) NOT NULL,
  `tax_status` enum('Responsable Inscripto','Monotributista','Consumidor Final','Exento') NOT NULL,
  `invoice_type` varchar(1) NOT NULL DEFAULT 'C',
  `address_line` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_billing_data_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_billing_data_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla customers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla discount_coupons
# ------------------------------------------------------------

DROP TABLE IF EXISTS `discount_coupons`;

CREATE TABLE `discount_coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `uses` int(11) NOT NULL DEFAULT 0,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discount_coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla email_logs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `email_logs`;

CREATE TABLE `email_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `to` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `transport` varchar(30) DEFAULT NULL,
  `ok` tinyint(1) NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_logs_key_index` (`key`),
  KEY `email_logs_order_id_index` (`order_id`),
  KEY `email_logs_to_index` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;

INSERT INTO `email_logs` (`id`, `key`, `order_id`, `to`, `subject`, `transport`, `ok`, `error`, `context`, `created_at`, `updated_at`)
VALUES
	(1,'payment_status_updated',45,'gallecima@gmail.com','Pago de tu pedido #45: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-09-11 20:46:59','2025-09-11 20:46:59'),
	(2,'order_confirmed',49,'gallecima@gmail.com','Tu pedido #49 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-09-15 13:58:40','2025-09-15 13:58:40'),
	(3,'payment_status_updated',49,'gallecima@gmail.com','Pago de tu pedido #49: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-09-16 17:29:04','2025-09-16 17:29:04'),
	(4,'payment_status_updated',49,'gallecima@gmail.com','Pago de tu pedido #49: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-09-16 17:32:05','2025-09-16 17:32:05'),
	(5,'payment_status_updated',49,'gallecima@gmail.com','Pago de tu pedido #49: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-09-16 17:33:44','2025-09-16 17:33:44'),
	(6,'payment_status_updated',44,'gallecima@gmail.com','Pago de tu pedido #44: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-03 12:07:28','2025-10-03 12:07:28'),
	(7,'payment_status_updated',43,'gallecima@gmail.com','Pago de tu pedido #43: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 17:30:27','2025-10-06 17:30:27'),
	(8,'payment_status_updated',42,'gallecima@gmail.com','Pago de tu pedido #42: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 17:47:38','2025-10-06 17:47:38'),
	(9,'payment_status_updated',41,'gallecima@gmail.com','Pago de tu pedido #41: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 17:48:58','2025-10-06 17:48:58'),
	(10,'payment_status_updated',47,'gallecima@gmail.com','Pago de tu pedido #47: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-06 17:56:17','2025-10-06 17:56:17'),
	(11,'payment_status_updated',47,'gallecima@gmail.com','Pago de tu pedido #47: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 17:56:38','2025-10-06 17:56:38'),
	(12,'payment_status_updated',45,'gallecima@gmail.com','Pago de tu pedido #45: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-06 18:06:44','2025-10-06 18:06:44'),
	(13,'payment_status_updated',45,'gallecima@gmail.com','Pago de tu pedido #45: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 18:07:02','2025-10-06 18:07:02'),
	(14,'payment_status_updated',46,'gallecima@gmail.com','Pago de tu pedido #46: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-06 18:24:47','2025-10-06 18:24:47'),
	(15,'payment_status_updated',46,'gallecima@gmail.com','Pago de tu pedido #46: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 18:25:03','2025-10-06 18:25:03'),
	(16,'payment_status_updated',44,'gallecima@gmail.com','Pago de tu pedido #44: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-06 18:34:46','2025-10-06 18:34:46'),
	(17,'payment_status_updated',44,'gallecima@gmail.com','Pago de tu pedido #44: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 18:35:02','2025-10-06 18:35:02'),
	(18,'payment_status_updated',39,'gallecima@gmail.com','Pago de tu pedido #39: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 19:37:01','2025-10-06 19:37:01'),
	(19,'payment_status_updated',37,'gallecima@gmail.com','Pago de tu pedido #37: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 19:54:23','2025-10-06 19:54:23'),
	(20,'payment_status_updated',36,'gallecima@gmail.com','Pago de tu pedido #36: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-06 20:51:47','2025-10-06 20:51:47'),
	(21,'payment_status_updated',34,'gallecima@gmail.com','Pago de tu pedido #34: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-07 11:58:45','2025-10-07 11:58:45'),
	(22,'payment_status_updated',34,'gallecima@gmail.com','Pago de tu pedido #34: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 11:59:09','2025-10-07 11:59:09'),
	(23,'payment_status_updated',33,'gallecima@gmail.com','Pago de tu pedido #33: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 12:34:38','2025-10-07 12:34:38'),
	(24,'payment_status_updated',40,'gallecima@gmail.com','Pago de tu pedido #40: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 21:15:11','2025-10-07 21:15:11'),
	(25,'payment_status_updated',38,'gallecima@gmail.com','Pago de tu pedido #38: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 21:20:01','2025-10-07 21:20:01'),
	(26,'payment_status_updated',38,'gallecima@gmail.com','Pago de tu pedido #38: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-07 21:21:11','2025-10-07 21:21:11'),
	(27,'payment_status_updated',38,'gallecima@gmail.com','Pago de tu pedido #38: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 21:21:52','2025-10-07 21:21:52'),
	(28,'payment_status_updated',32,'gallecima@gmail.com','Pago de tu pedido #32: Completed','plugin_smtp',0,'SMTP Error: Could not authenticate.',X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 22:42:45','2025-10-07 22:42:45'),
	(29,'payment_status_updated',45,'gallecima@gmail.com','Pago de tu pedido #45: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-07 22:55:43','2025-10-07 22:55:43'),
	(30,'payment_status_updated',45,'gallecima@gmail.com','Pago de tu pedido #45: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-07 22:56:07','2025-10-07 22:56:07'),
	(31,'payment_status_updated',42,'gallecima@gmail.com','Pago de tu pedido #42: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 12:14:14','2025-10-08 12:14:14'),
	(32,'payment_status_updated',42,'gallecima@gmail.com','Pago de tu pedido #42: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 12:15:33','2025-10-08 12:15:33'),
	(33,'payment_status_updated',30,'gallecima@gmail.com','Pago de tu pedido #30: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 12:48:33','2025-10-08 12:48:33'),
	(34,'payment_status_updated',48,'justoyessa@outlook.com','Pago de tu pedido #48: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 13:14:29','2025-10-08 13:14:29'),
	(35,'payment_status_updated',48,'justoyessa@outlook.com','Pago de tu pedido #48: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225696E766F6963655F6C6162656C25223A22222C2225696E766F6963655F7064665F75726C25223A22222C2225696E766F6963655F71725F75726C25223A22222C2225696E766F6963655F63616525223A22222C2225696E766F6963655F6361655F76746F25223A22227D','2025-10-08 13:14:56','2025-10-08 13:14:56'),
	(36,'order_confirmed',1,'gallecima@gmail.com','Tu pedido #1 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-08 13:23:12','2025-10-08 13:23:12'),
	(37,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225696E766F6963655F6C6162656C25223A224320303030312D3030303030303439222C2225696E766F6963655F7064665F75726C25223A22222C2225696E766F6963655F71725F75726C25223A22222C2225696E766F6963655F63616525223A223735343131323834313835353032222C2225696E766F6963655F6361655F76746F25223A2231385C2F31305C2F32303235227D','2025-10-08 13:25:42','2025-10-08 13:25:42'),
	(38,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 13:37:55','2025-10-08 13:37:55'),
	(39,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225696E766F6963655F6C6162656C25223A224320303030312D3030303030303530222C2225696E766F6963655F7064665F75726C25223A22222C2225696E766F6963655F71725F75726C25223A22222C2225696E766F6963655F63616525223A223735343131323834313836353435222C2225696E766F6963655F6361655F76746F25223A2231385C2F31305C2F32303235227D','2025-10-08 13:38:36','2025-10-08 13:38:36'),
	(40,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 13:45:36','2025-10-08 13:45:36'),
	(41,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 13:46:30','2025-10-08 13:46:30'),
	(42,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 14:03:20','2025-10-08 14:03:20'),
	(43,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 14:03:39','2025-10-08 14:03:39'),
	(44,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 14:18:54','2025-10-08 14:18:54'),
	(45,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 14:19:26','2025-10-08 14:19:26'),
	(46,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 14:32:08','2025-10-08 14:32:08'),
	(47,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 14:34:20','2025-10-08 14:34:20'),
	(48,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 14:49:47','2025-10-08 14:49:47'),
	(49,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-08 14:50:11','2025-10-08 14:50:11'),
	(50,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-08 14:55:05','2025-10-08 14:55:05'),
	(51,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-09 11:10:28','2025-10-09 11:10:28'),
	(52,'payment_status_updated',1,'gallecima@gmail.com','Pago de tu pedido #1: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-14 13:34:16','2025-10-14 13:34:16'),
	(53,'order_confirmed',2,'gallecima@gmail.com','Tu pedido #2 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-14 16:30:38','2025-10-14 16:30:38'),
	(54,'order_confirmed',3,'juanchijardin96@gmail.com','Tu pedido #3 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-14 17:08:39','2025-10-14 17:08:39'),
	(55,'order_confirmed',4,'gallecima@gmail.com','Tu pedido #4 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-14 17:15:34','2025-10-14 17:15:34'),
	(56,'payment_status_updated',3,'juanchijardin96@gmail.com','Pago de tu pedido #3: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-16 19:44:48','2025-10-16 19:44:48'),
	(57,'order_confirmed',7,'gallecima@gmail.com','Tu pedido #7 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-16 20:05:09','2025-10-16 20:05:09'),
	(58,'order_confirmed',8,'gallecima@gmail.com','Tu pedido #8 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-20 11:17:42','2025-10-20 11:17:42'),
	(59,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 11:28:22','2025-10-20 11:28:22'),
	(60,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 11:34:00','2025-10-20 11:34:00'),
	(61,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 11:34:17','2025-10-20 11:34:17'),
	(62,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 12:07:49','2025-10-20 12:07:49'),
	(63,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 12:08:34','2025-10-20 12:08:34'),
	(64,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 14:41:04','2025-10-20 14:41:04'),
	(65,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 14:42:00','2025-10-20 14:42:00'),
	(66,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 14:47:15','2025-10-20 14:47:15'),
	(67,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 14:54:05','2025-10-20 14:54:05'),
	(68,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 15:00:53','2025-10-20 15:00:53'),
	(69,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564227D','2025-10-20 15:01:35','2025-10-20 15:01:35'),
	(70,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',0,'Unclosed \'{\' on line 163',X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 19:26:08','2025-10-20 19:26:08'),
	(71,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',0,'Unclosed \'{\' on line 163',X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343231323837373438343936222C2225616669705F6E756D25223A223636222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961303331362D346135322D373638622D393837392D3133336432343031653865612E706466227D','2025-10-20 19:26:26','2025-10-20 19:26:26'),
	(72,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',0,'Unclosed \'{\' on line 163',X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 19:28:00','2025-10-20 19:28:00'),
	(73,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343231323837373438363837222C2225616669705F6E756D25223A223637222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961303331382D633233332D373333642D386266312D3432383464333131626330362E706466227D','2025-10-20 19:29:14','2025-10-20 19:29:14'),
	(74,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67227D','2025-10-20 19:42:40','2025-10-20 19:42:40'),
	(75,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343231323837373439393731222C2225616669705F6E756D25223A223638222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961303332372D373337382D373066382D396337662D6365623163346366363363352E706466222C2225737570706F72745F656D61696C25223A22736F706F7274654074752D646F6D696E696F2E636F6D222C2225636F6D70616E795F6E616D6525223A22547520456D707265736120532E412E222C2225636F6D70616E795F6164647265737325223A2241762E205369656D7072652056697661203734322C2053616C7461222C2225636F6D70616E795F7765627369746525223A2268747470733A5C2F5C2F74752D646F6D696E696F2E636F6D227D','2025-10-20 19:45:26','2025-10-20 19:45:26'),
	(76,'payment_status_updated_other',8,'gallecima@gmail.com','Pago de tu pedido #8: Pending','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A22436F6D706C65746564222C22256E65775F73746174757325223A2250656E64696E67222C22257061796D656E745F6D6574686F6425223A22436F6E74726120456E7472656761222C22257061796D656E745F616D6F756E7425223A223339342C3235222C22256F726465725F696425223A2238222C2225737570706F72745F656D61696C25223A22736F706F7274654074752D646F6D696E696F2E636F6D222C2225636F6D70616E795F6E616D6525223A22547520456D707265736120532E412E222C2225636F6D70616E795F6164647265737325223A2241762E205369656D7072652056697661203734322C2053616C7461222C2225636F6D70616E795F7765627369746525223A2268747470733A5C2F5C2F74752D646F6D696E696F2E636F6D227D','2025-10-20 19:55:48','2025-10-20 19:55:48'),
	(77,'payment_status_updated',8,'gallecima@gmail.com','Pago de tu pedido #8: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343231323837373439393731222C2225616669705F6E756D25223A223638222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961303332372D373337382D373066382D396337662D6365623163346366363363352E706466222C2225737570706F72745F656D61696C25223A22736F706F7274654074752D646F6D696E696F2E636F6D222C2225636F6D70616E795F6E616D6525223A22547520456D707265736120532E412E222C2225636F6D70616E795F6164647265737325223A2241762E205369656D7072652056697661203734322C2053616C7461222C2225636F6D70616E795F7765627369746525223A2268747470733A5C2F5C2F74752D646F6D696E696F2E636F6D227D','2025-10-20 19:56:25','2025-10-20 19:56:25'),
	(78,'order_confirmed',9,'gallecima@gmail.com','Tu pedido #9 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-27 16:54:08','2025-10-27 16:54:08'),
	(79,'order_confirmed',15,'agustin@pixelio.com','Tu pedido #15 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-30 22:44:27','2025-10-30 22:44:27'),
	(80,'order_confirmed',16,'agustin@pixelio.com.ar','Tu pedido #16 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-31 12:12:42','2025-10-31 12:12:42'),
	(81,'order_confirmed',17,'agustin@pixelio.com.ar','Tu pedido #17 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-31 13:22:14','2025-10-31 13:22:14'),
	(82,'payment_status_updated',17,'agustin@pixelio.com.ar','Pago de tu pedido #17: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A22222C2225616669705F6E756D25223A22222C2225616669705F70646675726C25223A22227D','2025-10-31 14:27:27','2025-10-31 14:27:27'),
	(83,'order_confirmed',18,'agustin@pixelio.com.ar','Tu pedido #18 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-31 14:31:00','2025-10-31 14:31:00'),
	(84,'payment_status_updated',18,'agustin@pixelio.com.ar','Pago de tu pedido #18: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343431323839393030313138222C2225616669705F6E756D25223A223639222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961336162302D303239632D373163642D396461362D6463376664353834343965362E706466227D','2025-10-31 14:33:32','2025-10-31 14:33:32'),
	(85,'order_confirmed',19,'gallecima@gmail.com','Tu pedido #19 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-10-31 15:18:29','2025-10-31 15:18:29'),
	(86,'payment_status_updated',19,'gallecima@gmail.com','Pago de tu pedido #19: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343431323839393035333931222C2225616669705F6E756D25223A223730222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961336164662D363866622D373532652D623435392D3266353964643335396664612E706466227D','2025-10-31 15:25:19','2025-10-31 15:25:19'),
	(87,'order_confirmed',22,'mdejuana.eltribuno@gmail.com','Tu pedido #22 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-10 21:01:10','2025-11-10 21:01:10'),
	(88,'order_confirmed',24,'mdejuana.eltribuno@gmail.com','Tu pedido #24 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-10 21:09:18','2025-11-10 21:09:18'),
	(89,'order_confirmed',23,'pruebamilena989@gmail.com','Tu pedido #23 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-10 21:12:38','2025-11-10 21:12:38'),
	(90,'shipment_status_updated',24,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #24: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-10 21:12:55','2025-11-10 21:12:55'),
	(91,'shipment_status_updated',23,'pruebamilena989@gmail.com','Envío de tu pedido #23: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-10 21:17:49','2025-11-10 21:17:49'),
	(92,'shipment_status_updated',23,'pruebamilena989@gmail.com','Envío de tu pedido #23: Delivered','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2244656C697665726564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-10 21:18:49','2025-11-10 21:18:49'),
	(93,'payment_status_updated',23,'pruebamilena989@gmail.com','Pago de tu pedido #23: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A22222C2225616669705F6E756D25223A22222C2225616669705F70646675726C25223A22227D','2025-11-10 21:20:12','2025-11-10 21:20:12'),
	(94,'order_confirmed',25,'mledesma.eltribuno@gmail.com','Tu pedido #25 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-12 18:48:30','2025-11-12 18:48:30'),
	(95,'order_confirmed',26,'jnanni.eltribuno@gmail.com','Tu pedido #26 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-14 18:38:26','2025-11-14 18:38:26'),
	(96,'order_confirmed',28,'jnanni.eltribuno@gmail.com','Tu pedido #28 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-14 19:51:14','2025-11-14 19:51:14'),
	(97,'order_confirmed',29,'mdejuana.eltribuno@gmail.com','Tu pedido #29 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-14 22:13:39','2025-11-14 22:13:39'),
	(98,'order_confirmed',30,'mdejuana.eltribuno@gmail.com','Tu pedido #30 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-15 19:03:44','2025-11-15 19:03:44'),
	(99,'payment_status_updated',22,'mdejuana.eltribuno@gmail.com','Pago de tu pedido #22: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A22222C2225616669705F6E756D25223A22222C2225616669705F70646675726C25223A22227D','2025-11-18 19:39:45','2025-11-18 19:39:45'),
	(100,'shipment_status_updated',22,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #22: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22373839343536222C2225747261636B696E675F75726C25223A22227D','2025-11-18 19:43:37','2025-11-18 19:43:37'),
	(101,'order_confirmed',31,'mdejuana.eltribuno@gmail.com','Tu pedido #31 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-25 18:14:56','2025-11-25 18:14:56'),
	(102,'payment_status_updated',31,'mdejuana.eltribuno@gmail.com','Pago de tu pedido #31: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A22222C2225616669705F6E756D25223A22222C2225616669705F70646675726C25223A22227D','2025-11-25 18:16:35','2025-11-25 18:16:35'),
	(103,'shipment_status_updated',31,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #31: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-25 18:17:19','2025-11-25 18:17:19'),
	(104,'shipment_status_updated',31,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #31: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22446973706F6E69626C652070617261207265746972617220656C2032375C2F3131222C2225747261636B696E675F75726C25223A22227D','2025-11-25 18:23:54','2025-11-25 18:23:54'),
	(105,'shipment_status_updated',31,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #31: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-25 19:10:42','2025-11-25 19:10:42'),
	(106,'payment_status_updated',32,'mledesma.eltribuno@gmail.com','Pago de tu pedido #32: Completed','plugin_smtp',1,NULL,X'7B22256F6C645F73746174757325223A2250656E64696E67222C22256E65775F73746174757325223A22436F6D706C65746564222C2225616669705F63616525223A223735343831323934303130303633222C2225616669705F6E756D25223A223731222C2225616669705F70646675726C25223A2268747470733A5C2F5C2F616669702D73646B2D7064662D73746F726167652E73332E616D617A6F6E6177732E636F6D5C2F30313961633166312D353063662D373362392D393036632D3734343837396665326266342E706466227D','2025-11-26 20:53:37','2025-11-26 20:53:37'),
	(107,'shipment_status_updated',31,'mdejuana.eltribuno@gmail.com','Envío de tu pedido #31: Delivered','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2244656C697665726564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-11-27 11:19:25','2025-11-27 11:19:25'),
	(108,'order_confirmed',33,'gallecima@gmail.com','Tu pedido #33 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-11-27 14:16:52','2025-11-27 14:16:52'),
	(109,'order_confirmed',34,'gallecima@gmail.com','Tu pedido #34 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-12-01 14:32:02','2025-12-01 14:32:02'),
	(110,'shipment_status_updated',34,'gallecima@gmail.com','Envío de tu pedido #34: Ready_for_pickup','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2252656164795F666F725F7069636B7570222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-12-01 21:08:36','2025-12-01 21:08:36'),
	(111,'shipment_status_updated',34,'gallecima@gmail.com','Envío de tu pedido #34: Shipped','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A2253686970706564222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-12-12 13:28:21','2025-12-12 13:28:21'),
	(112,'shipment_status_updated',34,'gallecima@gmail.com','Envío de tu pedido #34: Listo para retirar','plugin_smtp',1,NULL,X'7B2225736869706D656E745F73746174757325223A224C6973746F20706172612072657469726172222C2225747261636B696E675F6E756D62657225223A22222C2225747261636B696E675F75726C25223A22227D','2025-12-12 13:35:39','2025-12-12 13:35:39'),
	(113,'order_confirmed',6,'gallecima@gmail.com','Tu pedido #6 fue recibido','plugin_smtp',1,NULL,X'7B22257061796D656E745F6D6574686F6425223A224D65726361646F5061676F222C22257061796D656E745F616D6F756E7425223A22382C3535222C22256F726465725F696425223A2236227D','2025-12-26 18:53:15','2025-12-26 18:53:15'),
	(114,'order_confirmed',7,'gallecima@gmail.com','Tu pedido #7 fue recibido','plugin_smtp',1,NULL,X'5B5D','2025-12-26 19:12:01','2025-12-26 19:12:01'),
	(115,'order_confirmed',9,'gallecima@gmail.com','Tu pedido #9 fue recibido','plugin_smtp',1,NULL,X'5B5D','2026-01-08 12:33:10','2026-01-08 12:33:10'),
	(116,'order_confirmed',10,'gallecima@gmail.com','Tu pedido #10 fue recibido','plugin_smtp',1,NULL,X'5B5D','2026-01-08 12:46:56','2026-01-08 12:46:56');

/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla email_templates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `email_templates`;

CREATE TABLE `email_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;

INSERT INTO `email_templates` (`id`, `key`, `name`, `subject`, `body_html`, `enabled`, `options`, `created_at`, `updated_at`)
VALUES
	(1,'order_confirmed','Confirmación de compra','Tu pedido #%pedido_id% fue recibido','<!doctype html>\r\n<html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <meta charset=\"utf-8\">\r\n  <title>Confirmación de compra</title>\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\r\n  <style>\r\n    @media (max-width: 600px) { .container{width:100% !important;} .px-24{padding-left:16px !important; padding-right:16px !important;} }\r\n    .btn { background:#0057D9; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600; }\r\n    .muted { color:#6b7280; }\r\n    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; }\r\n    .hr { border:none; border-top:1px solid #e5e7eb; height:1px; margin:24px 0; }\r\n    .footnote { font-size:12px; line-height:1.6; color:#6b7280; }\r\n  </style>\r\n</head>\r\n<body style=\"margin:0; padding:0; background:#f5f6f8; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background:#f5f6f8; padding:24px 0;\">\r\n    <tr>\r\n      <td align=\"center\">\r\n        <table class=\"container\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"width:600px; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;\">\r\n          <!-- Header -->\r\n          <tr>\r\n            <td style=\"background:#0b1324; padding:24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td align=\"left\">\r\n                  <div style=\"color:#ffffff; font-size:18px; font-weight:700;\">¡Gracias por tu compra!</div>\r\n                  <div style=\"color:#a7b0c3; font-size:13px; margin-top:4px;\">Pedido realizado el %fecha%</div>\r\n                </td>\r\n                <td align=\"right\">\r\n                  <!-- Logo opcional -->\r\n                </td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Body -->\r\n          <tr>\r\n            <td class=\"px-24\" style=\"padding:24px;\">\r\n              <p style=\"margin:0 0 8px 0; font-size:16px;\">Hola %nombre%,</p>\r\n              <p class=\"muted\" style=\"margin:0 0 20px 0; font-size:14px;\">\r\n                Recibimos tu pedido y estamos preparando todo para despacharlo. Abajo vas a encontrar el detalle de tu compra.\r\n              </p>\r\n\r\n              <!-- Items (inyectado por EmailTemplateRenderer) -->\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px;\">\r\n                %items_table%\r\n              </div>\r\n\r\n              <!-- Totales + CTA -->\r\n              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:8px 0 20px 0;\">\r\n                <tr>\r\n                  <td align=\"left\" class=\"muted\" style=\"color:#6b7280; font-size:14px;\">Total pagado</td>\r\n                  <td align=\"right\" style=\"font-weight:700; font-size:16px;\">$ %total%</td>\r\n                </tr>\r\n              </table>\r\n\r\n              <div style=\"margin:16px 0 12px 0;\">\r\n                <a href=\"%order_link%\" class=\"btn\" style=\"background:#0057D9; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600;\">\r\n                  Ver seguimiento y detalle del pedido\r\n                </a>\r\n              </div>\r\n\r\n              <p class=\"muted\" style=\"margin:4px 0 0 0; font-size:12px;\">\r\n                Si el botón no funciona, copiá y pegá este enlace en tu navegador:<br>\r\n                <span style=\"word-break:break-all;\"><a href=\"%order_link%\" style=\"color:#0057D9; text-decoration:none;\">%order_link%</a></span>\r\n              </p>\r\n\r\n              <div class=\"hr\"></div>\r\n\r\n              <!-- Ayuda / Soporte -->\r\n              <p style=\"margin:0 0 6px 0; font-weight:600;\">¿Tenés dudas?</p>\r\n              <p class=\"muted\" style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                Escribinos y con gusto te ayudamos.\r\n              </p>\r\n\r\n              <p class=\"footnote\" style=\"margin:0;\">Este mensaje fue enviado automáticamente.</p>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Footer -->\r\n          <tr>\r\n            <td style=\"background:#f9fafb; padding:16px 24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td class=\"muted\" style=\"font-size:12px; color:#6b7280;\">\r\n                  %company_name% — %company_address%\r\n                </td>\r\n                <td align=\"right\">\r\n                  <a href=\"%company_website%\" style=\"font-size:12px; color:#6b7280; text-decoration:none;\">%company_website%</a>\r\n                </td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n        </table>\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>',1,X'7B22746F5F637573746F6D6572223A747275657D','2025-09-11 12:44:44','2025-10-20 20:03:48'),
	(2,'payment_status_updated','Cambio de estado de pago','Pago de tu pedido #%pedido_id%: %payment_status%','<!doctype html>\r\n<html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <meta charset=\"utf-8\">\r\n  <title>Actualización de pago</title>\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\r\n  <style>\r\n    /* Clientes que respetan <style> */\r\n    @media (max-width: 600px) { .container { width: 100% !important; } .px-24 { padding-left:16px !important; padding-right:16px !important; } }\r\n    .btn { background:#0057D9; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600; }\r\n    .muted { color:#6b7280; }\r\n    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }\r\n    .badge--success { background:#E8F5E9; color:#1B5E20; }\r\n    .badge--warn { background:#FFF8E1; color:#8D6E00; }\r\n    .badge--fail { background:#FDECEA; color:#B71C1C; }\r\n    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; }\r\n    .hr { border:none; border-top:1px solid #e5e7eb; height:1px; margin:24px 0; }\r\n    .footnote { font-size:12px; line-height:1.6; color:#6b7280; }\r\n  </style>\r\n</head>\r\n<body style=\"margin:0; padding:0; background:#f5f6f8; font-family: -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background:#f5f6f8; padding:24px 0;\">\r\n    <tr>\r\n      <td align=\"center\">\r\n        <table class=\"container\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"width:600px; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;\">\r\n          <!-- Header -->\r\n          <tr>\r\n            <td style=\"background:#0b1324; padding:24px;\">\r\n              <table width=\"100%\">\r\n                <tr>\r\n                  <td align=\"left\">\r\n                    <div style=\"color:#ffffff; font-size:18px; font-weight:700;\">Confirmación de pago</div>\r\n                    <div style=\"color:#a7b0c3; font-size:13px; margin-top:4px;\">Hemos actualizado el estado de tu pago.</div>\r\n                  </td>\r\n                  <td align=\"right\">\r\n                    <!-- Logo opcional: reemplazar src si querés incrustar uno -->\r\n                    <!-- <img src=\"cid:logo_mail\" width=\"120\" alt=\"Logo\" style=\"display:block;\"> -->\r\n                  </td>\r\n                </tr>\r\n              </table>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Body -->\r\n          <tr>\r\n            <td class=\"px-24\" style=\"padding:24px;\">\r\n              <!-- Estado -->\r\n              <p style=\"margin:0 0 12px 0; font-size:16px;\">\r\n                Estado actualizado de <strong>%old_status%</strong> a\r\n                <span class=\"badge badge--success\" style=\"background:#E8F5E9; color:#1B5E20; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700;\">%new_status%</span>\r\n              </p>\r\n              <p class=\"muted\" style=\"margin:0 0 20px 0; color:#6b7280; font-size:14px;\">\r\n                Si necesitas ayuda o ves algún error, respondé a este correo así lo revisamos.\r\n              </p>\r\n\r\n              <!-- Resumen / Detalle -->\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px;\">\r\n                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:14px;\">\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280; width:35%;\">Comprobante AFIP</td>\r\n                    <td style=\"padding:6px 0; font-weight:600;\">#%afip_num%</td>\r\n                  </tr>\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280;\">CAE</td>\r\n                    <td style=\"padding:6px 0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, \'Liberation Mono\', \'Courier New\', monospace;\">\r\n                      %afip_cae%\r\n                    </td>\r\n                  </tr>\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280;\">Estado del CAE</td>\r\n                    <td style=\"padding:6px 0;\">Vigente</td>\r\n                  </tr>\r\n                </table>\r\n              </div>\r\n\r\n              <!-- Botón de descarga -->\r\n              <div style=\"margin:20px 0 12px 0;\">\r\n                <a href=\"%afip_pdfurl%\" class=\"btn\" style=\"background:#0057D9; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600;\">\r\n                  Descargar comprobante (PDF)\r\n                </a>\r\n              </div>\r\n\r\n              <p class=\"muted\" style=\"margin:4px 0 0 0; font-size:12px;\">\r\n                Si no ves el adjunto, podés usar el botón de descarga. Si el enlace aparece vacío, intentá nuevamente más tarde o respondé a este correo.\r\n              </p>\r\n\r\n              <div class=\"hr\"></div>\r\n\r\n              <!-- Ayuda / Soporte -->\r\n              <p style=\"margin:0 0 6px 0; font-weight:600;\">¿Necesitás ayuda?</p>\r\n              <p class=\"muted\" style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                Escribinos a <a href=\"mailto:%support_email%\" style=\"color:#0057D9; text-decoration:none;\">%support_email%</a> y referí el comprobante <strong>#%afip_num%</strong>.\r\n              </p>\r\n\r\n              <p class=\"footnote\" style=\"margin:0;\">\r\n                Este mensaje fue enviado automáticamente. No compartas datos sensibles por email.\r\n              </p>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Footer -->\r\n          <tr>\r\n            <td style=\"background:#f9fafb; padding:16px 24px;\">\r\n              <table width=\"100%\">\r\n                <tr>\r\n                  <td class=\"muted\" style=\"font-size:12px; color:#6b7280;\">\r\n                    %company_name% — %company_address%\r\n                  </td>\r\n                  <td align=\"right\">\r\n                    <a href=\"%company_website%\" style=\"font-size:12px; color:#6b7280; text-decoration:none;\">%company_website%</a>\r\n                  </td>\r\n                </tr>\r\n              </table>\r\n            </td>\r\n          </tr>\r\n\r\n        </table>\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>',1,X'7B22746F5F637573746F6D6572223A747275657D','2025-09-11 12:44:44','2025-10-20 19:38:16'),
	(3,'shipment_status_updated','Actualización de envío','Envío de tu pedido #%pedido_id%: %shipment_status%','<!doctype html>\r\n<html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <meta charset=\"utf-8\">\r\n  <title>Actualización de envío</title>\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\r\n  <style>\r\n    @media (max-width: 600px) { \r\n      .container { width:100% !important; } \r\n      .px-24 { padding-left:16px !important; padding-right:16px !important; } \r\n    }\r\n    .btn { background:#0057D9; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600; }\r\n    .muted { color:#6b7280; }\r\n    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; }\r\n    .hr { border:none; border-top:1px solid #e5e7eb; height:1px; margin:24px 0; }\r\n    .footnote { font-size:12px; line-height:1.6; color:#6b7280; }\r\n  </style>\r\n</head>\r\n\r\n<body style=\"margin:0; padding:0; background:#f5f6f8; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background:#f5f6f8; padding:24px 0;\">\r\n    <tr>\r\n      <td align=\"center\">\r\n\r\n        <table class=\"container\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" \r\n               style=\"width:600px; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;\">\r\n\r\n          <!-- Header -->\r\n          <tr>\r\n            <td style=\"background:#0b1324; padding:24px;\">\r\n              <table width=\"100%\">\r\n                <tr>\r\n                  <td align=\"left\">\r\n                    <div style=\"color:#ffffff; font-size:18px; font-weight:700;\">\r\n                      Actualización de tu envío\r\n                    </div>\r\n                    <div style=\"color:#a7b0c3; font-size:13px; margin-top:4px;\">\r\n                      Estamos siguiendo tu pedido\r\n                    </div>\r\n                  </td>\r\n                  <td align=\"right\">\r\n                    <!-- Logo opcional -->\r\n                  </td>\r\n                </tr>\r\n              </table>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- BODY -->\r\n          <tr>\r\n            <td class=\"px-24\" style=\"padding:24px;\">\r\n\r\n              <p style=\"margin:0 0 8px 0; font-size:16px;\">\r\n                Hola %nombre%,\r\n              </p>\r\n\r\n              <p class=\"muted\" style=\"margin:0 0 20px 0; font-size:14px;\">\r\n                ¡Tenemos novedades sobre tu pedido!<br>\r\n                El estado actual del envío es:\r\n              </p>\r\n\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px;\">\r\n                <p style=\"margin:0; font-size:15px;\">\r\n                  <strong>Estado del envío:</strong> <span>%shipment_status%</span><br>\r\n                  <strong>Tracking:</strong> %tracking_number%<br>\r\n                  <strong>Link de seguimiento:</strong> \r\n                  <a href=\"%tracking_url%\" style=\"color:#0057D9; text-decoration:none;\">Rastrear aquí</a>\r\n                </p>\r\n              </div>\r\n\r\n              <!-- CTA principal -->\r\n              <div style=\"margin:20px 0 12px 0;\">\r\n                <a href=\"%order_link%\" class=\"btn\">\r\n                  Ver seguimiento y detalle del pedido\r\n                </a>\r\n              </div>\r\n\r\n              <p class=\"muted\" style=\"margin:4px 0 0 0; font-size:12px;\">\r\n                Si el botón no funciona, copiá y pegá este enlace en tu navegador:<br>\r\n                <span style=\"word-break:break-all;\">\r\n                  <a href=\"%order_link%\" style=\"color:#0057D9; text-decoration:none;\">%order_link%</a>\r\n                </span>\r\n              </p>\r\n\r\n              <div class=\"hr\"></div>\r\n\r\n              <!-- Soporte -->\r\n              <p style=\"margin:0 0 6px 0; font-weight:600;\">¿Necesitás ayuda?</p>\r\n              <p class=\"muted\" style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                Estamos aquí para ayudarte ante cualquier consulta sobre tu pedido.\r\n              </p>\r\n\r\n              <p class=\"footnote\" style=\"margin:0;\">Este mensaje fue generado automáticamente.</p>\r\n\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- FOOTER -->\r\n          <tr>\r\n            <td style=\"background:#f9fafb; padding:16px 24px;\">\r\n              <table width=\"100%\">\r\n                <tr>\r\n                  <td class=\"muted\" style=\"font-size:12px; color:#6b7280;\">\r\n                    %company_name% — %company_address%\r\n                  </td>\r\n                  <td align=\"right\">\r\n                    <a href=\"%company_website%\" style=\"font-size:12px; color:#6b7280; text-decoration:none;\">\r\n                      %company_website%\r\n                    </a>\r\n                  </td>\r\n                </tr>\r\n              </table>\r\n            </td>\r\n          </tr>\r\n\r\n        </table>\r\n\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>',1,X'7B22746F5F637573746F6D6572223A747275657D','2025-09-11 12:44:44','2025-12-01 21:15:04'),
	(4,'payment_status_updated_other','Cambio de estado de pago','Pago de tu pedido #%pedido_id%: %payment_status%','<!doctype html>\r\n<html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <meta charset=\"utf-8\">\r\n  <title>Actualización de pago</title>\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\r\n  <style>\r\n    @media (max-width: 600px) { .container{width:100% !important;} .px-24{padding-left:16px !important; padding-right:16px !important;} }\r\n    .btn { background:#0057D9; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600; }\r\n    .muted { color:#6b7280; }\r\n    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }\r\n    .badge--neutral { background:#EFF6FF; color:#0B4AA2; }\r\n    .badge--warn    { background:#FFF8E1; color:#8D6E00; }\r\n    .badge--fail    { background:#FDECEA; color:#B71C1C; }\r\n    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; }\r\n    .hr { border:none; border-top:1px solid #e5e7eb; height:1px; margin:24px 0; }\r\n    .footnote { font-size:12px; line-height:1.6; color:#6b7280; }\r\n  </style>\r\n</head>\r\n<body style=\"margin:0; padding:0; background:#f5f6f8; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background:#f5f6f8; padding:24px 0;\">\r\n    <tr>\r\n      <td align=\"center\">\r\n        <table class=\"container\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"width:600px; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;\">\r\n          <!-- Header -->\r\n          <tr>\r\n            <td style=\"background:#0b1324; padding:24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td align=\"left\">\r\n                  <div style=\"color:#ffffff; font-size:18px; font-weight:700;\">Estado de tu pago</div>\r\n                  <div style=\"color:#a7b0c3; font-size:13px; margin-top:4px;\">Hemos registrado un cambio en tu transacción.</div>\r\n                </td>\r\n                <td align=\"right\"></td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Body -->\r\n          <tr>\r\n            <td class=\"px-24\" style=\"padding:24px;\">\r\n              <p style=\"margin:0 0 12px 0; font-size:16px;\">\r\n                Estado actualizado de <strong>%old_status%</strong> a\r\n                <!-- Usamos una sola badge “neutral” para cualquier estado no-completed -->\r\n                <span class=\"badge badge--neutral\" style=\"background:#EFF6FF; color:#0B4AA2; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700;\">\r\n                  %new_status%\r\n                </span>\r\n              </p>\r\n\r\n              <p class=\"muted\" style=\"margin:0 0 20px 0; font-size:14px;\">\r\n                A continuación, te compartimos el resumen de tu operación. Si necesitás ayuda, respondé a este correo.\r\n              </p>\r\n\r\n              <!-- Resumen del pago -->\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px;\">\r\n                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size:14px;\">\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280; width:40%;\">Orden</td>\r\n                    <td style=\"padding:6px 0; font-weight:600;\">#%order_id%</td>\r\n                  </tr>\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280;\">Método de pago</td>\r\n                    <td style=\"padding:6px 0;\">%payment_method%</td>\r\n                  </tr>\r\n                  <tr>\r\n                    <td style=\"padding:6px 0; color:#6b7280;\">Importe</td>\r\n                    <td style=\"padding:6px 0;\">$ %payment_amount%</td>\r\n                  </tr>\r\n                </table>\r\n              </div>\r\n\r\n              <!-- Próximos pasos (texto genérico útil para pending/failed/refunded) -->\r\n              <div class=\"card\" style=\"padding:16px;\">\r\n                <p style=\"margin:0 0 8px 0; font-weight:600;\">Próximos pasos</p>\r\n                <ul style=\"margin:0; padding-left:18px; color:#374151; font-size:14px; line-height:1.6;\">\r\n                  <li>Si tu pago está <strong>Pendiente</strong>, se acreditará automáticamente cuando el proveedor confirme los fondos.</li>\r\n                  <li>Si tu pago figura <strong>Fallido</strong>, verificá tus datos o probá con otro método.</li>\r\n                  <li>Si tu pago fue <strong>Reembolsado</strong>, verás el reintegro según los tiempos de tu banco.</li>\r\n                </ul>\r\n              </div>\r\n\r\n              <div class=\"hr\"></div>\r\n\r\n              <!-- Soporte -->\r\n              <p style=\"margin:0 0 6px 0; font-weight:600;\">¿Necesitás ayuda?</p>\r\n              <p class=\"muted\" style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                Escribinos a <a href=\"mailto:%support_email%\" style=\"color:#0057D9; text-decoration:none;\">%support_email%</a> indicando la orden <strong>#%order_id%</strong>.\r\n              </p>\r\n\r\n              <p class=\"footnote\" style=\"margin:0;\">Este mensaje fue enviado automáticamente.</p>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Footer -->\r\n          <tr>\r\n            <td style=\"background:#f9fafb; padding:16px 24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td class=\"muted\" style=\"font-size:12px; color:#6b7280;\">\r\n                  %company_name% — %company_address%\r\n                </td>\r\n                <td align=\"right\">\r\n                  <a href=\"%company_website%\" style=\"font-size:12px; color:#6b7280; text-decoration:none;\">%company_website%</a>\r\n                </td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n        </table>\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>',1,X'7B22746F5F637573746F6D6572223A747275657D',NULL,'2025-10-20 19:52:38'),
	(5,'order_ready_for_pickup','order ready','¡Tu pedido está listo para retirar!','<!doctype html>\r\n<html lang=\"es\" xmlns=\"http://www.w3.org/1999/xhtml\">\r\n<head>\r\n  <meta charset=\"utf-8\">\r\n  <title>Pedido listo para retiro</title>\r\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\r\n  <style>\r\n    @media (max-width: 600px) { .container{width:100% !important;} .px-24{padding-left:16px !important; padding-right:16px !important;} }\r\n    .btn { background:#10B981; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600; }\r\n    .muted { color:#6b7280; }\r\n    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; }\r\n    .hr { border:none; border-top:1px solid #e5e7eb; height:1px; margin:24px 0; }\r\n    .footnote { font-size:12px; line-height:1.6; color:#6b7280; }\r\n  </style>\r\n</head>\r\n<body style=\"margin:0; padding:0; background:#f5f6f8; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n  <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"background:#f5f6f8; padding:24px 0;\">\r\n    <tr>\r\n      <td align=\"center\">\r\n        <table class=\"container\" role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" width=\"600\" style=\"width:600px; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;\">\r\n          <!-- Header -->\r\n          <tr>\r\n            <td style=\"background:#064E3B; padding:24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td align=\"left\">\r\n                  <div style=\"color:#ffffff; font-size:18px; font-weight:700;\">¡Tu pedido está listo para retirar!</div>\r\n                  <div style=\"color:#a7b0c3; font-size:13px; margin-top:4px;\">Pedido #%pedido_id% — realizado el %fecha%</div>\r\n                </td>\r\n                <td align=\"right\">\r\n                  <!-- Logo opcional -->\r\n                </td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Body -->\r\n          <tr>\r\n            <td class=\"px-24\" style=\"padding:24px;\">\r\n              <p style=\"margin:0 0 8px 0; font-size:16px;\">Hola %nombre%,</p>\r\n              <p class=\"muted\" style=\"margin:0 0 20px 0; font-size:14px;\">\r\n                Tu pedido ya está disponible para retirar en nuestro local. A continuación te dejamos los detalles:\r\n              </p>\r\n\r\n              <!-- Items (inyectado por EmailTemplateRenderer) -->\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px;\">\r\n                %items_table%\r\n              </div>\r\n\r\n              <!-- Total -->\r\n              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin:8px 0 20px 0;\">\r\n                <tr>\r\n                  <td align=\"left\" class=\"muted\" style=\"color:#6b7280; font-size:14px;\">Total pagado</td>\r\n                  <td align=\"right\" style=\"font-weight:700; font-size:16px;\">$ %total%</td>\r\n                </tr>\r\n              </table>\r\n\r\n              <!-- Instrucciones de retiro -->\r\n              <div class=\"card\" style=\"padding:16px; margin-bottom:16px; background:#F0FDF4; border-color:#A7F3D0;\">\r\n                <p style=\"margin:0 0 8px 0; font-weight:600;\">📍 Dirección de retiro</p>\r\n                <p style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                  %company_address%\r\n                </p>\r\n\r\n                <p style=\"margin:0 0 8px 0; font-weight:600;\">🕒 Horarios</p>\r\n                <p style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                  Lunes a Viernes de 9:00 a 18:00 hs.<br>\r\n                  Sábados de 9:00 a 13:00 hs.\r\n                </p>\r\n\r\n                <p style=\"margin:0; font-size:14px;\">\r\n                  Recordá presentar tu número de pedido <strong>#%pedido_id%</strong> o el correo de confirmación al momento de retirar.\r\n                </p>\r\n              </div>\r\n\r\n              <!-- CTA -->\r\n              <div style=\"margin:16px 0 12px 0;\">\r\n                <a href=\"%order_link%\" class=\"btn\" style=\"background:#10B981; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:6px; font-weight:600;\">\r\n                  Ver detalle del pedido\r\n                </a>\r\n              </div>\r\n\r\n              <p class=\"muted\" style=\"margin:4px 0 0 0; font-size:12px;\">\r\n                Si el botón no funciona, copiá y pegá este enlace en tu navegador:<br>\r\n                <span style=\"word-break:break-all;\"><a href=\"%order_link%\" style=\"color:#047857; text-decoration:none;\">%order_link%</a></span>\r\n              </p>\r\n\r\n              <div class=\"hr\"></div>\r\n\r\n              <!-- Ayuda / Soporte -->\r\n              <p style=\"margin:0 0 6px 0; font-weight:600;\">¿Tenés alguna consulta?</p>\r\n              <p class=\"muted\" style=\"margin:0 0 12px 0; font-size:14px;\">\r\n                Escribinos y te ayudaremos a coordinar tu retiro.\r\n              </p>\r\n\r\n              <p class=\"footnote\" style=\"margin:0;\">Este mensaje fue enviado automáticamente.</p>\r\n            </td>\r\n          </tr>\r\n\r\n          <!-- Footer -->\r\n          <tr>\r\n            <td style=\"background:#f9fafb; padding:16px 24px;\">\r\n              <table width=\"100%\"><tr>\r\n                <td class=\"muted\" style=\"font-size:12px; color:#6b7280;\">\r\n                  %company_name% — %company_address%\r\n                </td>\r\n                <td align=\"right\">\r\n                  <a href=\"%company_website%\" style=\"font-size:12px; color:#6b7280; text-decoration:none;\">%company_website%</a>\r\n                </td>\r\n              </tr></table>\r\n            </td>\r\n          </tr>\r\n\r\n        </table>\r\n      </td>\r\n    </tr>\r\n  </table>\r\n</body>\r\n</html>',1,X'7B22746F5F637573746F6D6572223A747275657D',NULL,'2025-10-20 20:05:29');

/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla facturacion_electronica
# ------------------------------------------------------------

DROP TABLE IF EXISTS `facturacion_electronica`;

CREATE TABLE `facturacion_electronica` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `domicilio` varchar(255) NOT NULL,
  `cuit` varchar(255) NOT NULL,
  `cert_crt` text DEFAULT NULL,
  `public_key` text DEFAULT NULL,
  `punto_venta` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facturacion_electronica_cuit_unique` (`cuit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla failed_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla job_batches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`)
VALUES
	(1,'default','{\"uuid\":\"7d2652d3-6da9-4ce7-8343-52f124b2bcd0\",\"displayName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"command\":\"O:41:\\\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\\\":13:{s:7:\\\"timeout\\\";i:120;s:7:\\\"orderId\\\";i:32;s:8:\\\"override\\\";a:5:{s:6:\\\"source\\\";s:8:\\\"enviacom\\\";s:6:\\\"amount\\\";d:3923.6999999999998181010596454143524169921875;s:5:\\\"label\\\";s:41:\\\"andreani — Andreani Estandar a Sucursal\\\";s:4:\\\"data\\\";a:3:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";}s:4:\\\"meta\\\";a:3:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1756142516,\"delay\":null}',0,NULL,1756142516,1756142516),
	(2,'default','{\"uuid\":\"7a50bf77-4d07-42f7-b972-69cd215bacff\",\"displayName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"command\":\"O:41:\\\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\\\":13:{s:7:\\\"timeout\\\";i:120;s:7:\\\"orderId\\\";i:35;s:8:\\\"override\\\";a:5:{s:6:\\\"source\\\";s:8:\\\"enviacom\\\";s:6:\\\"amount\\\";d:4090.73000000000001818989403545856475830078125;s:5:\\\"label\\\";s:41:\\\"andreani — Andreani Estandar a Sucursal\\\";s:4:\\\"data\\\";a:3:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";}s:4:\\\"meta\\\";a:3:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1756146687,\"delay\":null}',0,NULL,1756146687,1756146687),
	(3,'default','{\"uuid\":\"f888a97c-2d5b-4d0b-a73f-cda07dfd2a6c\",\"displayName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"command\":\"O:41:\\\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\\\":13:{s:7:\\\"timeout\\\";i:120;s:7:\\\"orderId\\\";i:36;s:8:\\\"override\\\";a:4:{s:6:\\\"source\\\";s:8:\\\"enviacom\\\";s:6:\\\"amount\\\";d:3923.6999999999998181010596454143524169921875;s:5:\\\"label\\\";s:41:\\\"andreani — Andreani Estandar a Sucursal\\\";s:4:\\\"data\\\";a:4:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";s:10:\\\"branchCode\\\";s:5:\\\"10180\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1756162866,\"delay\":null}',0,NULL,1756162866,1756162866),
	(4,'default','{\"uuid\":\"d73353f6-35aa-43e9-880a-6a926745fb69\",\"displayName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"command\":\"O:41:\\\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\\\":13:{s:7:\\\"timeout\\\";i:120;s:7:\\\"orderId\\\";i:37;s:8:\\\"override\\\";a:4:{s:6:\\\"source\\\";s:8:\\\"enviacom\\\";s:6:\\\"amount\\\";d:3923.6999999999998181010596454143524169921875;s:5:\\\"label\\\";s:41:\\\"andreani — Andreani Estandar a Sucursal\\\";s:4:\\\"data\\\";a:4:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";s:11:\\\"branch_code\\\";s:5:\\\"10067\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1756163473,\"delay\":null}',0,NULL,1756163473,1756163473),
	(5,'default','{\"uuid\":\"14076711-f61a-4fa4-8511-ff34d4a43b94\",\"displayName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\",\"command\":\"O:41:\\\"Plugins\\\\EnviaCom\\\\Jobs\\\\CreateEnviaShipment\\\":13:{s:7:\\\"timeout\\\";i:120;s:7:\\\"orderId\\\";i:38;s:8:\\\"override\\\";a:4:{s:6:\\\"source\\\";s:8:\\\"enviacom\\\";s:6:\\\"amount\\\";d:3923.6999999999998181010596454143524169921875;s:5:\\\"label\\\";s:41:\\\"andreani — Andreani Estandar a Sucursal\\\";s:4:\\\"data\\\";a:4:{s:7:\\\"rate_id\\\";s:8:\\\"98-427-2\\\";s:7:\\\"service\\\";s:28:\\\"Andreani Estandar a Sucursal\\\";s:7:\\\"carrier\\\";s:8:\\\"andreani\\\";s:11:\\\"branch_code\\\";s:5:\\\"10180\\\";}}s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\"},\"createdAt\":1756210826,\"delay\":null}',0,NULL,1756210826,1756210826),
	(6,'default','{\"uuid\":\"73b29b54-75bc-44dd-b8ae-e3bef80d9725\",\"displayName\":\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\",\"command\":\"O:33:\\\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\\\":1:{s:7:\\\"orderId\\\";i:44;}\"},\"createdAt\":1759493240,\"delay\":null}',0,NULL,1759493240,1759493240),
	(7,'default','{\"uuid\":\"44bd2c8b-1b78-4d94-883e-3aea4a8134a5\",\"displayName\":\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\",\"command\":\"O:33:\\\"Plugins\\\\AFIP\\\\Jobs\\\\EmitAfipInvoice\\\":1:{s:7:\\\"orderId\\\";i:40;}\"},\"createdAt\":1759871703,\"delay\":null}',0,NULL,1759871703,1759871703);

/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla localities
# ------------------------------------------------------------

DROP TABLE IF EXISTS `localities`;

CREATE TABLE `localities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `province_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `localities_province_id_name_unique` (`province_id`,`name`),
  CONSTRAINT `localities_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `localities` WRITE;
/*!40000 ALTER TABLE `localities` DISABLE KEYS */;

INSERT INTO `localities` (`id`, `province_id`, `name`, `created_at`, `updated_at`)
VALUES
	(1,1,'La Plata','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(2,1,'Mar del Plata','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(3,1,'Bahía Blanca','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(4,1,'Tandil','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(5,1,'San Nicolás de los Arroyos','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(6,2,'San Fernando del Valle de Catamarca','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(7,2,'Valle Viejo','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(8,2,'Andalgalá','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(9,2,'Belén','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(10,2,'Tinogasta','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(11,3,'Resistencia','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(12,3,'Presidencia Roque Sáenz Peña','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(13,3,'Villa Ángela','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(14,3,'Charata','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(15,3,'General San Martín','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(16,4,'Rawson','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(17,4,'Trelew','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(18,4,'Puerto Madryn','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(19,4,'Comodoro Rivadavia','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(20,4,'Esquel','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(21,5,'Córdoba','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(22,5,'Villa María','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(23,5,'Río Cuarto','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(24,5,'San Francisco','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(25,5,'Villa Carlos Paz','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(26,6,'Corrientes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(27,6,'Goya','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(28,6,'Paso de los Libres','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(29,6,'Mercedes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(30,6,'Santo Tomé','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(31,7,'Paraná','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(32,7,'Concordia','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(33,7,'Gualeguaychú','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(34,7,'Gualeguay','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(35,7,'Villaguay','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(36,8,'Formosa','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(37,8,'Clorinda','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(38,8,'El Colorado','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(39,8,'Pirané','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(40,8,'Las Lomitas','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(41,9,'San Salvador de Jujuy','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(42,9,'Palpalá','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(43,9,'Libertador General San Martín','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(44,9,'San Pedro de Jujuy','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(45,9,'La Quiaca','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(46,10,'Santa Rosa','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(47,10,'General Pico','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(48,10,'Toay','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(49,10,'Realicó','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(50,10,'Eduardo Castex','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(51,11,'La Rioja','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(52,11,'Chilecito','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(53,11,'Aimogasta','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(54,11,'Chamical','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(55,11,'Chepes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(56,12,'Mendoza','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(57,12,'San Rafael','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(58,12,'Godoy Cruz','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(59,12,'Guaymallén','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(60,12,'Maipú','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(61,13,'Posadas','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(62,13,'Eldorado','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(63,13,'Oberá','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(64,13,'Puerto Iguazú','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(65,13,'Apóstoles','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(66,14,'Neuquén','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(67,14,'Plottier','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(68,14,'Cutral Có','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(69,14,'Zapala','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(70,14,'San Martín de los Andes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(71,15,'Viedma','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(72,15,'San Carlos de Bariloche','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(73,15,'General Roca','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(74,15,'Cipolletti','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(75,15,'Villa Regina','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(76,16,'Salta','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(77,16,'San Ramón de la Nueva Orán','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(78,16,'Tartagal','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(79,16,'General Güemes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(80,16,'Metán','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(81,17,'San Juan','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(82,17,'Rawson','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(83,17,'Chimbas','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(84,17,'Pocito','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(85,17,'Caucete','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(86,18,'San Luis','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(87,18,'Villa Mercedes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(88,18,'La Punta','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(89,18,'Justo Daract','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(90,18,'Merlo','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(91,19,'Río Gallegos','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(92,19,'Caleta Olivia','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(93,19,'Puerto Deseado','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(94,19,'Pico Truncado','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(95,19,'Las Heras','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(96,20,'Santa Fe','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(97,20,'Rosario','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(98,20,'Rafaela','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(99,20,'Venado Tuerto','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(100,20,'Villa Gobernador Gálvez','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(101,21,'Santiago del Estero','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(102,21,'La Banda','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(103,21,'Termas de Río Hondo','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(104,21,'Frías','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(105,21,'Añatuya','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(106,22,'Ushuaia','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(107,22,'Río Grande','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(108,22,'Tolhuin','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(109,22,'San Sebastián','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(110,22,'Puerto Almanza','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(111,23,'San Miguel de Tucumán','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(112,23,'Tafí Viejo','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(113,23,'Yerba Buena','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(114,23,'Concepción','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(115,23,'Banda del Río Salí','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(116,16,'Joaquín V. Gonzalez','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(117,16,'Rosario de la Frontera','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(118,16,'Cachi','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(119,16,'Cafayate','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(120,16,'San Lorenzo','2025-12-25 14:33:18','2025-12-25 14:33:18'),
	(121,16,'Capital','2025-12-25 23:05:54','2025-12-25 23:05:54');

/*!40000 ALTER TABLE `localities` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla menu_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `menu_groups`;

CREATE TABLE `menu_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `icono` varchar(255) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `menu_groups` WRITE;
/*!40000 ALTER TABLE `menu_groups` DISABLE KEYS */;

INSERT INTO `menu_groups` (`id`, `nombre`, `icono`, `orden`, `created_at`, `updated_at`)
VALUES
	(1,'Tienda',NULL,1,'2025-07-01 22:04:18','2025-07-01 22:04:18'),
	(2,'Gestión',NULL,2,'2025-07-01 22:10:11','2025-07-01 22:10:11'),
	(3,'Configuración',NULL,4,'2025-07-01 22:10:26','2025-07-15 11:52:34'),
	(5,'CMS',NULL,3,'2025-07-15 11:42:55','2025-07-15 11:52:27');

/*!40000 ALTER TABLE `menu_groups` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla menu_perfil
# ------------------------------------------------------------

DROP TABLE IF EXISTS `menu_perfil`;

CREATE TABLE `menu_perfil` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `perfil_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permisos`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_perfil_perfil_id_foreign` (`perfil_id`),
  KEY `menu_perfil_menu_id_foreign` (`menu_id`),
  CONSTRAINT `menu_perfil_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_perfil_perfil_id_foreign` FOREIGN KEY (`perfil_id`) REFERENCES `perfiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `menu_perfil` WRITE;
/*!40000 ALTER TABLE `menu_perfil` DISABLE KEYS */;

INSERT INTO `menu_perfil` (`id`, `perfil_id`, `menu_id`, `permisos`, `created_at`, `updated_at`)
VALUES
	(1,1,1,NULL,NULL,NULL),
	(2,1,2,NULL,NULL,NULL),
	(3,1,3,NULL,NULL,NULL),
	(4,1,4,NULL,NULL,NULL),
	(5,1,5,NULL,NULL,NULL),
	(6,1,6,NULL,NULL,NULL),
	(7,1,7,NULL,NULL,NULL),
	(23,1,9,NULL,NULL,NULL),
	(24,1,11,NULL,NULL,NULL),
	(25,1,10,NULL,NULL,NULL),
	(27,1,13,NULL,NULL,NULL),
	(28,1,14,NULL,NULL,NULL),
	(30,1,15,NULL,NULL,NULL),
	(32,1,17,NULL,NULL,NULL),
	(33,1,18,NULL,NULL,NULL),
	(34,1,19,NULL,NULL,NULL),
	(35,1,20,NULL,NULL,NULL),
	(37,1,22,NULL,NULL,NULL),
	(38,1,23,NULL,NULL,NULL),
	(39,1,8,NULL,NULL,NULL),
	(40,1,24,NULL,NULL,NULL),
	(41,1,12,NULL,NULL,NULL);

/*!40000 ALTER TABLE `menu_perfil` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla menus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `grupo` varchar(255) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `icono` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `menu_group_id` bigint(20) unsigned DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `menus_menu_group_id_foreign` (`menu_group_id`),
  CONSTRAINT `menus_menu_group_id_foreign` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;

INSERT INTO `menus` (`id`, `nombre`, `grupo`, `ruta`, `icono`, `activo`, `created_at`, `updated_at`, `menu_group_id`, `orden`)
VALUES
	(1,'Dashboard',NULL,'/admin/dashboard','si si-speedometer',1,'2025-06-17 15:43:30','2025-08-12 21:02:21',NULL,0),
	(2,'Usuarios','Configuración','admin/usuarios','si si-users',1,'2025-06-17 15:43:30','2025-06-24 22:30:54',3,1),
	(3,'Perfiles','Configuración','admin/perfiles','si si-key',1,'2025-06-17 15:43:30','2025-06-22 15:27:00',3,2),
	(4,'Mi Cuenta','Configuración','profile/edit','si si-user',1,'2025-06-17 15:43:30','2025-06-24 22:21:59',3,6),
	(5,'Pedidos','Gestión','admin/orders','si si-basket',1,'2025-06-17 15:43:30','2025-06-30 13:32:53',2,1),
	(6,'Clientes','Gestión','admin/customers','si si-people',1,'2025-06-17 15:43:30','2025-06-30 13:28:21',2,3),
	(7,'Pagos','Gestión','admin/payments','si si-wallet',1,'2025-06-17 15:43:30','2025-06-30 13:32:26',2,2),
	(8,'Log de Actividades','Configuración','admin/log-actividades','si si-settings',1,'2025-06-17 15:43:30','2025-10-14 12:57:11',3,5),
	(9,'Menús','Configuración','admin/menus','si si-list',1,NULL,NULL,3,3),
	(10,'Productos','Tienda','admin/products','si si-social-dropbox',1,'2025-06-30 13:20:50','2025-07-01 22:15:28',1,1),
	(11,'Categorías','Tienda','admin/categories','si si-tag',1,'2025-06-30 13:26:56','2025-07-01 22:11:20',1,2),
	(12,'Reportes','Gestión','admin/reports','si si-bar-chart',1,'2025-06-30 13:34:59','2025-06-30 13:34:59',2,4),
	(13,'Atributos','Tienda','admin/attributes','si si-list',1,'2025-06-30 19:16:56','2025-07-01 22:11:11',1,3),
	(14,'Grupos','Configuración','admin/menu-groups','si si-folder-alt',1,'2025-07-01 21:52:14','2025-07-01 21:52:14',3,4),
	(15,'Sliders','CMS','admin/sliders','fa fa-arrows-left-right',1,'2025-07-15 11:54:43','2025-07-15 14:49:36',5,1),
	(17,'Categorías','CMS','admin/blog/categories','fa fa-list',1,'2025-07-17 21:44:18','2025-07-17 21:44:18',5,3),
	(18,'Posts','CMS','admin/blog/posts','fa fa-file-lines',1,'2025-07-17 22:25:47','2025-07-17 22:25:47',5,4),
	(19,'Medios de Pago','Gestión','admin/payment-methods','fa fa-money-bill-transfer',1,'2025-07-22 22:28:21','2025-07-22 22:32:34',2,6),
	(20,'Cupones de Descuento','Tienda','admin/discount-coupons','fa fa-ticket-simple',1,'2025-07-28 21:12:56','2025-07-28 21:44:06',1,4),
	(21,'Métodos de envio','Gestión','admin/shipmentmethod','fa fa-truck',1,'2025-07-29 22:43:35','2025-07-31 12:49:36',2,7),
	(22,'Plugins','Configuración','admin/plugins','fa fa-puzzle-piece',1,'2025-08-13 12:52:45','2025-08-13 12:52:45',3,3),
	(23,'Emails','Configuración','admin/emails','fa fa-envelope',1,'2025-09-11 12:54:30','2025-09-11 12:54:30',3,6),
	(24,'Información','Configuración','admin/info','fa-solid fa-circle-info',1,'2025-10-21 11:54:21','2025-10-21 11:54:39',3,9);

/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES
	(1,'0001_01_01_000000_create_users_table',1),
	(2,'0001_01_01_000001_create_cache_table',1),
	(3,'0001_01_01_000002_create_jobs_table',1),
	(4,'2025_06_17_131820_add_profile_photo_to_users_table',1),
	(5,'2025_06_17_150443_create_menus_table',1),
	(6,'2025_06_17_150443_create_perfiles_table',1),
	(7,'2025_06_17_150444_create_menu_perfil_table',1),
	(8,'2025_06_17_153712_add_grupo_to_menus_table',2),
	(9,'2025_06_18_112747_add_perfil_id_to_users_table',3),
	(10,'2025_06_24_223148_add_active_to_users_table',4),
	(11,'2025_06_30_125919_create_customer_addresses_table',5),
	(12,'2025_06_30_125919_create_customer_billing_data_table',5),
	(13,'2025_06_30_125919_create_customers_table',5),
	(14,'2025_06_30_125935_create_categories_table',5),
	(15,'2025_06_30_125935_create_product_images_table',5),
	(16,'2025_06_30_125935_create_products_table',5),
	(17,'2025_06_30_125943_create_order_items_table',5),
	(18,'2025_06_30_125943_create_orders_table',5),
	(19,'2025_06_30_125949_create_payments_table',5),
	(20,'2025_06_30_125949_create_shipments_table',5),
	(21,'2025_06_30_125956_create_discount_coupons_table',5),
	(22,'2025_06_30_130002_create_cart_items_table',5),
	(23,'2025_06_30_130002_create_carts_table',5),
	(24,'2025_06_30_130009_create_category_product_table',5),
	(30,'2025_06_30_134640_create_attributes_table',6),
	(31,'2025_06_30_134641_create_attribute_values_table',6),
	(32,'2025_06_30_134642_create_attribute_product_table',6),
	(33,'2025_06_30_151323_update_products_table',7),
	(34,'2025_06_30_151349_update_categories_table',7),
	(35,'2025_06_30_151807_update_customers_table',7),
	(36,'2025_06_30_151828_update_customer_addresses_table',7),
	(37,'2025_06_30_151848_update_customer_billing_data_table',7),
	(38,'2025_06_30_151908_update_orders_table',7),
	(39,'2025_06_30_152025_update_order_items_table',7),
	(40,'2025_06_30_152049_update_payments_table',7),
	(41,'2025_06_30_152109_update_shipments_table',7),
	(42,'2025_06_30_152127_update_discount_coupons_table',7),
	(43,'2025_06_30_152147_update_carts_table',7),
	(44,'2025_06_30_152208_update_cart_items_table',7),
	(45,'2025_06_30_192726_create_facturacion_electronicas_table',8),
	(46,'2025_06_30_213112_create_attribute_category_table',9),
	(47,'2025_06_30_223617_add_order_image_icon_to_categories_table',10),
	(48,'2025_06_30_225415_add_description_to_categories_table',11),
	(49,'2025_07_01_213803_create_menu_groups_table',12),
	(50,'2025_07_08_000001_add_shipping_meta_featured_to_products',13),
	(51,'2025_07_10_114318_create_category_product_table',14),
	(52,'2025_07_10_141439_add_product_id_and_path_to_product_images_table',15),
	(53,'2025_07_13_134423_add_has_stock_price_to_attributes_table',16),
	(54,'2025_07_13_142507_add_stock_and_price_to_attribute_product_table',17),
	(55,'2025_07_15_150449_create_sliders_table',18),
	(56,'2025_07_15_150537_create_slider_images_table',18),
	(57,'2025_07_17_163151_create_blog_categories_table',19),
	(58,'2025_07_17_163219_create_blog_posts_table',19),
	(59,'2025_07_20_141329_add_completed_at_and_notes_to_carts_table',20),
	(60,'2025_07_20_141454_add_name_and_image_to_cart_items_table',21),
	(61,'2025_07_21_150315_add_attribute_values_json_to_cart_items_table',22),
	(62,'2025_07_22_222522_create_payment_methods_table',23),
	(63,'2025_07_23_140005_add_image_to_attribute_product_table',24),
	(64,'2025_07_29_214715_create_countries_table',25),
	(65,'2025_07_29_214716_create_provinces_table',25),
	(66,'2025_07_29_214717_create_localities_table',25),
	(67,'2025_07_29_223132_create_shipment_methods_table',26),
	(68,'2025_07_30_123137_add_min_cart_amount_to_shipment_methods_table',27),
	(69,'2025_08_01_114704_add_payment_method_id_to_orders_table',28),
	(70,'2025_08_01_153110_add_financial_fields_to_orders_table',29),
	(71,'2025_08_01_153130_add_total_and_attribute_to_order_items_table',29),
	(72,'2025_08_04_202355_add_shipping_discount_to_orders_table',30),
	(73,'2025_08_04_204431_add_buyer_fields_to_orders_table',31),
	(74,'2025_08_04_215238_add_shipment_method_and_address_to_shipments_table',32),
	(75,'2025_08_05_134421_nullable_session_id_cart',33),
	(76,'2025_08_13_124246_create_plugins_table',34),
	(77,'2025_08_19_211453_make_customer_id_nullable_on_orders',35),
	(78,'2025_08_20_135629_add_meta_to_shipments_table',36),
	(79,'2025_08_21_225929_add_shipping_data_json_to_shipments_table',37),
	(80,'2025_08_25_122717_add_plugin_key_to_shipment_methods',38),
	(86,'2025_01_01_000001_create_suppliers_table',39),
	(87,'2025_01_01_000002_create_supplier_products_table',39),
	(88,'2025_01_01_000003_create_purchase_orders_table',39),
	(89,'2025_01_01_000004_create_purchase_order_items_table',39),
	(90,'2025_01_01_000005_create_replenishment_suggestions_table',39),
	(91,'2025_09_11_122028_2025_01_01_010000_create_email_templates_table',40),
	(92,'2025_09_11_203623_2025_09_11_000001_create_email_logs_table',41),
	(93,'2025_09_11_204618_2025_09_11_000001_create_email_logs_table2',42),
	(94,'2025_01_01_000001_create_afip_invoices_table',43),
	(95,'2025_03_12_000001_add_invoice_type_to_customer_billing_data_table',44),
	(96,'2025_03_12_000002_create_order_invoices_table',45),
	(97,'2025_10_03_120128_create_or_rename_order_invoices_table',45),
	(98,'2025_10_09_000000_create_tribuno_product_discounts',46),
	(99,'2025_10_14_112922_create_activities_table',47),
	(100,'2025_10_21_114324_create_site_infos_table',48),
	(101,'2025_10_31_131829_add_public_token_to_orders',49),
	(102,'2025_12_01_130511_add_locality_id_to_customer_addresses_table',50),
	(103,'2025_12_01_202148_add_is_pickup_to_shipment_methods_table',51),
	(104,'2025_12_23_000001_create_blog_post_product_table',52),
	(105,'2025_11_10_000001_add_order_to_products_table',53),
	(106,'2026_02_15_120000_add_location_text_fields_to_shipment_methods_table',54),
	(107,'2026_02_22_100000_add_site_title_to_site_infos_table',55),
	(108,'2026_02_22_140000_add_theme_vars_to_site_infos_table',56);

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla order_invoices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_invoices`;

CREATE TABLE `order_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(255) NOT NULL DEFAULT 'manual',
  `title` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'issued',
  `issued_at` timestamp NULL DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_invoices_order_id_foreign` (`order_id`),
  CONSTRAINT `order_invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla order_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `attribute_value_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_attribute_value_id_foreign` (`attribute_value_id`),
  CONSTRAINT `order_items_attribute_value_id_foreign` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `public_token` varchar(64) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `shipment_method_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled') NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT NULL,
  `shipping_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `billing_data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`billing_data_json`)),
  `notes` text DEFAULT NULL,
  `coupon_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_public_token_unique` (`public_token`),
  KEY `orders_coupon_id_foreign` (`coupon_id`),
  KEY `orders_payment_method_id_foreign` (`payment_method_id`),
  KEY `orders_shipment_method_id_foreign` (`shipment_method_id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `discount_coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_shipment_method_id_foreign` FOREIGN KEY (`shipment_method_id`) REFERENCES `shipment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla password_reset_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`)
VALUES
	('agustin@pixelio.com.ar','$2y$12$q9SSpaaU7IeNDael4oDivOATE6Cx2D8NdwYCCSZ/Jee1QaKiuYOrm','2025-06-25 11:22:38');

/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla payment_methods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payment_methods`;

CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'manual',
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `instructions` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;

INSERT INTO `payment_methods` (`id`, `name`, `slug`, `type`, `config`, `instructions`, `active`, `created_at`, `updated_at`)
VALUES
	(3,'MercadoPago','mercadopago','plugin',X'7B22736F75726365223A22706C7567696E2E6D65726361646F7061676F227D','Serás redirigido a MercadoPago para completar el pago.',1,'2025-08-18 14:37:46','2025-12-12 13:47:07');

/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla payments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `payment_method_id` bigint(20) unsigned DEFAULT NULL,
  `method` varchar(255) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla perfiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `perfiles`;

CREATE TABLE `perfiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `es_master` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perfiles_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `perfiles` WRITE;
/*!40000 ALTER TABLE `perfiles` DISABLE KEYS */;

INSERT INTO `perfiles` (`id`, `nombre`, `es_master`, `created_at`, `updated_at`)
VALUES
	(1,'Master',1,'2025-06-17 15:43:30','2025-06-17 15:43:30');

/*!40000 ALTER TABLE `perfiles` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `plugins`;

CREATE TABLE `plugins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `version` varchar(255) DEFAULT NULL,
  `is_installed` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `installed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugins_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;

INSERT INTO `plugins` (`id`, `name`, `slug`, `version`, `is_installed`, `is_active`, `config`, `installed_at`, `created_at`, `updated_at`, `description`)
VALUES
	(1,'Hello World','helloworld','1.0.0',1,0,X'7B226D657373616765223A224A75737469746F2C206573746520657320656C20736974696F222C22686F6F6B223A2266726F6E743A676C6F62616C3A626F64792D656E64222C22636F6E7465787473223A5B22686F6D65225D2C227374796C65223A22616C65727420616C6572742D64616E676572227D','2025-08-13 12:54:01','2025-08-13 12:54:01','2025-10-16 18:53:57',NULL),
	(2,'SMTP','smtp','1.0.0',1,1,X'7B22686F7374223A2275363030303436322E6665726F7A6F2E636F6D222C22706F7274223A22343635222C22656E6372797074696F6E223A2273736C222C22757365726E616D65223A22696E666F40706978656C696F2E636F6D2E6172222C2270617373776F7264223A224931323334356E666F222C2266726F6D5F656D61696C223A22696E666F40706978656C696F2E636F6D2E6172222C2266726F6D5F6E616D65223A22496E666F20506978656C696F222C227265706C795F746F223A22696E666F40706978656C696F2E636F6D2E6172222C22616C6C6F775F73656C665F7369676E6564223A747275652C22736B69705F686F73745F766572696679223A2231227D','2025-08-15 15:12:33','2025-08-15 15:12:33','2025-08-16 14:31:09',NULL),
	(3,'MercadoPago','mercadopago','1.0.0',1,1,X'7B226D6F6465223A226C697665222C227075626C69635F6B6579223A224150505F5553522D61326261656532662D653930612D343238642D626461372D346539333738383939373566222C226163636573735F746F6B656E223A224150505F5553522D313530333431333038313239323033352D3132313531362D64646338326130653165363135653462376235343562376464633863303736352D313930353433373837222C22696E7465677261746F725F6964223A6E756C6C2C22776562686F6F6B5F736563726574223A6E756C6C2C22737563636573735F75726C223A225C2F636865636B6F75745C2F636F6D706C6574653F7374617475733D73756363657373222C226661696C7572655F75726C223A225C2F636865636B6F75745C2F636F6D706C6574653F7374617475733D6661696C757265222C2270656E64696E675F75726C223A225C2F636865636B6F75745C2F636F6D706C6574653F7374617475733D70656E64696E67222C226175746F5F63617074757265223A747275652C22696E7374616C6C6D656E7473223A312C22776562686F6F6B5F75726C223A2268747470733A5C2F5C2F636172742E706978656C696F2E636F6D2E61725C2F776562686F6F6B735C2F6D65726361646F7061676F227D','2025-08-16 15:24:15','2025-08-16 15:24:15','2025-12-26 18:50:18',NULL);

/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla product_images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `product_images`;

CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla products
# ------------------------------------------------------------

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `length` decimal(8,2) DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `is_digital` tinyint(1) NOT NULL DEFAULT 0,
  `is_new` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla provinces
# ------------------------------------------------------------

DROP TABLE IF EXISTS `provinces`;

CREATE TABLE `provinces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_country_id_name_unique` (`country_id`,`name`),
  CONSTRAINT `provinces_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;

INSERT INTO `provinces` (`id`, `country_id`, `name`, `created_at`, `updated_at`)
VALUES
	(1,1,'Buenos Aires','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(2,1,'Catamarca','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(3,1,'Chaco','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(4,1,'Chubut','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(5,1,'Córdoba','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(6,1,'Corrientes','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(7,1,'Entre Ríos','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(8,1,'Formosa','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(9,1,'Jujuy','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(10,1,'La Pampa','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(11,1,'La Rioja','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(12,1,'Mendoza','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(13,1,'Misiones','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(14,1,'Neuquén','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(15,1,'Río Negro','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(16,1,'Salta','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(17,1,'San Juan','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(18,1,'San Luis','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(19,1,'Santa Cruz','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(20,1,'Santa Fe','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(21,1,'Santiago del Estero','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(22,1,'Tierra del Fuego','2025-07-29 22:24:00','2025-07-29 22:24:00'),
	(23,1,'Tucumán','2025-07-29 22:24:00','2025-07-29 22:24:00');

/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla purchase_order_items
# ------------------------------------------------------------

DROP TABLE IF EXISTS `purchase_order_items`;

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `qty_ordered` int(11) NOT NULL,
  `qty_received` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `subtotal` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla purchase_orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `purchase_orders`;

CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `status` enum('draft','placed','partial','received','cancelled') NOT NULL DEFAULT 'draft',
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `eta_date` date DEFAULT NULL,
  `placed_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_code_unique` (`code`),
  KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla replenishment_suggestions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `replenishment_suggestions`;

CREATE TABLE `replenishment_suggestions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `run_key` varchar(64) NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `window_days` int(11) NOT NULL,
  `lead_time_days` int(11) NOT NULL DEFAULT 0,
  `service_level` decimal(5,2) NOT NULL DEFAULT 0.90,
  `demand_daily` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `stdev_daily` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `safety_stock` int(11) NOT NULL DEFAULT 0,
  `reorder_point` int(11) NOT NULL DEFAULT 0,
  `stock_on_hand` int(11) NOT NULL DEFAULT 0,
  `stock_in_po` int(11) NOT NULL DEFAULT 0,
  `suggested_qty` int(11) NOT NULL DEFAULT 0,
  `reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reason`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `replenishment_suggestions_product_id_foreign` (`product_id`),
  KEY `replenishment_suggestions_supplier_id_foreign` (`supplier_id`),
  KEY `replenishment_suggestions_run_key_index` (`run_key`),
  CONSTRAINT `replenishment_suggestions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `replenishment_suggestions_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
VALUES
	('hAQq7Z35FWRmgNogNEOXvQybW3N3pFxM9mfOJWJB',1,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo2OntzOjY6Il90b2tlbiI7czo0MDoiR0VvaXp5YWVXQVN3OGIxMWE1Vk5KdjFVWVNzaUJNNE42Z1FaNVZ0UyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vaW5mby8xL2VkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjc6ImNhcnRfaWQiO2k6MjtzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1771770763);

/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla shipment_methods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shipment_methods`;

CREATE TABLE `shipment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_cart_amount` decimal(10,2) DEFAULT NULL,
  `delay` varchar(255) DEFAULT NULL,
  `discount_type` enum('amount','percent') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `province_id` bigint(20) unsigned DEFAULT NULL,
  `locality_id` bigint(20) unsigned DEFAULT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `province_name` varchar(255) DEFAULT NULL,
  `locality_name` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `weight_limit` decimal(10,2) DEFAULT NULL,
  `height_limit` decimal(10,2) DEFAULT NULL,
  `width_limit` decimal(10,2) DEFAULT NULL,
  `length_limit` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `plugin_key` varchar(100) DEFAULT NULL,
  `is_pickup` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipment_methods_country_id_foreign` (`country_id`),
  KEY `shipment_methods_province_id_foreign` (`province_id`),
  KEY `shipment_methods_locality_id_foreign` (`locality_id`),
  KEY `shipment_methods_plugin_key_index` (`plugin_key`),
  CONSTRAINT `shipment_methods_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipment_methods_locality_id_foreign` FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipment_methods_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `shipment_methods` WRITE;
/*!40000 ALTER TABLE `shipment_methods` DISABLE KEYS */;

INSERT INTO `shipment_methods` (`id`, `name`, `amount`, `min_cart_amount`, `delay`, `discount_type`, `discount_value`, `country_id`, `province_id`, `locality_id`, `country_name`, `province_name`, `locality_name`, `postal_code`, `weight_limit`, `height_limit`, `width_limit`, `length_limit`, `is_active`, `plugin_key`, `is_pickup`, `created_at`, `updated_at`)
VALUES
	(1,'Sin Cargo',0.00,5.00,'10 dias',NULL,NULL,1,16,119,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-07-29 23:03:19','2025-12-01 14:04:36'),
	(2,'Pick up Salta Capital - Av. Ex Combatientes de Malvinas 3890',0.00,0.00,'0','percent',5.00,1,16,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-07-30 12:47:07','2025-11-25 18:12:32'),
	(3,'Preferencial',2500.00,50.00,'1 dia',NULL,NULL,1,16,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-07-30 14:25:12','2025-10-28 12:38:53'),
	(7,'Envia.com',0.00,NULL,'Se cotiza automáticamente',NULL,0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'enviacom',0,'2025-08-25 12:43:28','2025-08-25 12:43:28'),
	(8,'Pick up Salta Capital - Stand El Tribuno - Alto Noa Shopping',0.00,0.00,'0','percent',5.00,1,16,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:13:49','2025-11-25 18:13:49'),
	(9,'Pick up Gral. Güemes - Concejal Sosa Nº 12 Barrio El Naranjito',0.00,0.00,'0','percent',5.00,1,16,79,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:26:14','2025-11-25 18:26:14'),
	(10,'Pick up Orán - Sarmiento 339',0.00,0.00,'0','percent',5.00,1,16,77,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:26:48','2025-11-25 18:26:48'),
	(11,'Pick up Tartagal - España 398',0.00,0.00,'0','percent',5.00,1,16,78,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:27:28','2025-11-25 18:27:28'),
	(12,'Pick up Metán - Catamarca 305 esq. Marcos Avellaneda',0.00,0.00,'0','percent',5.00,1,16,80,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:27:54','2025-11-25 18:30:59'),
	(13,'Pick up Joaquín V. González - Av. San Martín 416',0.00,0.00,'0','percent',5.00,1,16,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:28:28','2025-11-25 18:28:28'),
	(14,'Pick up Rosario de la Frontera - San Martín 252',0.00,0.00,'0','percent',5.00,1,16,76,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,0,'2025-11-25 18:28:49','2025-11-25 18:28:49'),
	(15,'Pick up Cachi - Benjamín Zorrilla s/n',0.00,0.00,'0','percent',5.00,1,16,118,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,1,'2025-11-25 18:29:21','2025-12-01 20:29:06'),
	(16,'Pick up Cafayate - Güemes Sur 221',0.00,0.00,'0','percent',5.00,1,16,119,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,1,'2025-11-25 18:29:43','2025-12-01 20:30:00'),
	(17,'caleta olivia',0.00,0.00,'7 dias',NULL,NULL,NULL,NULL,NULL,'Argentina','Santa Cruz','Mercado de la Ciudad','9011',NULL,NULL,NULL,NULL,1,NULL,1,'2026-02-15 15:24:32','2026-02-15 15:24:32');

/*!40000 ALTER TABLE `shipment_methods` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla shipments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shipments`;

CREATE TABLE `shipments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `shipment_method_id` bigint(20) unsigned DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `shipping_data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_data_json`)),
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `carrier` varchar(255) DEFAULT NULL,
  `status` enum('pending','shipped','delivered','ready_for_pickup') NOT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipments_order_id_foreign` (`order_id`),
  KEY `shipments_shipment_method_id_foreign` (`shipment_method_id`),
  CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipments_shipment_method_id_foreign` FOREIGN KEY (`shipment_method_id`) REFERENCES `shipment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla site_infos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `site_infos`;

CREATE TABLE `site_infos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_title` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `support_email` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `theme_vars` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`theme_vars`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `site_infos` WRITE;
/*!40000 ALTER TABLE `site_infos` DISABLE KEYS */;

INSERT INTO `site_infos` (`id`, `site_title`, `company_name`, `company_address`, `company_website`, `support_email`, `logo_path`, `theme_vars`, `created_at`, `updated_at`)
VALUES
	(1,'Bilingual Treasure Website','Bilingual Treasure','Uruguay 2760 - San Lorenzo','https://bilingualtresure.com','soporte@bilingualtresure.com','site/logo/RCppRmAcql4FZBV3cdDBV086X2AB3ubNoXnFTzWe.png',X'7B22676F6F676C655F666F6E745F64656661756C745F75726C223A2268747470733A5C2F5C2F666F6E74732E676F6F676C65617069732E636F6D5C2F637373323F66616D696C793D4F70656E2B53616E733A6974616C2C7767687440302C3330302E2E3830303B312C3330302E2E38303026646973706C61793D73776170222C22676F6F676C655F666F6E745F7072696D6172795F75726C223A2268747470733A5C2F5C2F666F6E74732E676F6F676C65617069732E636F6D5C2F637373323F66616D696C793D4C696C6974612B4F6E6526646973706C61793D73776170222C22666F6E745F64656661756C74223A225C224F70656E2053616E735C22222C22666F6E745F7072696D617279223A225C224C696C697461204F6E655C22222C22636F6C6F725F64656661756C74223A2223343434343434222C22636F6C6F725F7072696D617279223A2223636661303966222C22636F6C6F725F7072696D6172795F6461726B223A2223393137303731222C22636F6C6F725F7365636F6E64617279223A2223303032303566222C22636F6C6F725F7465727469617279223A2223316664363566222C22636F6C6F725F7768697465223A2223666666666666222C22636F6C6F725F6C69676874223A2223653030623062222C22636F6C6F725F6461726B223A2223303032303337222C22726567756C61725F736861646F77223A2230202E3572656D203172656D207267626128302C302C302C2E31352921696D706F7274616E74222C227363726F6C6C5F6265686176696F72223A22736D6F6F7468222C2262735F6C696E6B5F636F6C6F72223A2223303032303566222C227377697065725F6E617669676174696F6E5F636F6C6F72223A2223303032303566227D','2025-10-21 11:45:39','2026-02-22 14:29:49');

/*!40000 ALTER TABLE `site_infos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla slider_images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `slider_images`;

CREATE TABLE `slider_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slider_id` bigint(20) unsigned NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slider_images_slider_id_foreign` (`slider_id`),
  CONSTRAINT `slider_images_slider_id_foreign` FOREIGN KEY (`slider_id`) REFERENCES `sliders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `slider_images` WRITE;
/*!40000 ALTER TABLE `slider_images` DISABLE KEYS */;

INSERT INTO `slider_images` (`id`, `slider_id`, `imagen`, `orden`, `created_at`, `updated_at`)
VALUES
	(6,3,'uploads/sliders/39c8068c-2738-4319-b9ff-d6cba54cc8e4.png',1,'2025-11-28 15:46:01','2025-11-28 15:56:33'),
	(9,3,'uploads/sliders/191e65b7-cf0c-4833-9ca7-bc53d8a424b5.jpg',2,'2025-11-28 15:56:01','2025-11-28 15:56:33'),
	(10,1,'uploads/sliders/51e00d18-0f27-4483-ab5c-43c9af1a19e7.jpg',0,'2026-02-22 14:14:04','2026-02-22 14:14:04');

/*!40000 ALTER TABLE `slider_images` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla sliders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sliders`;

CREATE TABLE `sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sliders_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;

INSERT INTO `sliders` (`id`, `nombre`, `slug`, `activo`, `created_at`, `updated_at`)
VALUES
	(1,'Principal','principal',1,'2025-07-15 21:59:17','2025-07-15 22:02:25'),
	(3,'Principal Vertical','principal-vertical',1,'2025-11-28 11:30:12','2025-11-28 11:30:12');

/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla supplier_products
# ------------------------------------------------------------

DROP TABLE IF EXISTS `supplier_products`;

CREATE TABLE `supplier_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_sku` varchar(100) DEFAULT NULL,
  `cost` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `lead_time_days` int(11) DEFAULT NULL,
  `moq` int(11) NOT NULL DEFAULT 1,
  `pack_size` int(11) NOT NULL DEFAULT 1,
  `is_preferred` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_products_supplier_id_product_id_unique` (`supplier_id`,`product_id`),
  KEY `supplier_products_product_id_foreign` (`product_id`),
  CONSTRAINT `supplier_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla suppliers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `suppliers`;

CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'ARS',
  `payment_terms` varchar(100) DEFAULT NULL,
  `default_lead_time_days` int(11) DEFAULT NULL,
  `min_order_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `perfil_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_perfil_id_foreign` (`perfil_id`),
  CONSTRAINT `users_perfil_id_foreign` FOREIGN KEY (`perfil_id`) REFERENCES `perfiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `perfil_id`, `name`, `email`, `profile_photo`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `active`)
VALUES
	(1,1,'Administrador','anitasansone@hotmail.com','profile_photos/iaaERT2EfBuwVLXbsMnOA6Pf5x3xVViFRyuGP2kj.jpg',NULL,'$2y$12$Z/EaKOrROazhULdltz.N6eSNrxxRjrsagV29qh3wTxqhYAwxWiz9C',NULL,'2025-06-17 15:47:50','2026-02-21 22:47:42',1),
	(2,1,'Agustin Cima','gallecima@gmail.com','profile_photos/0pyLZ62ebKDUKWj6DlvlCTXjn3it89GIWlX7A3k2.jpg',NULL,'$2y$12$dXskFPWoWzE87ei3uj2EDuJFNyfjqpbobgyfxxrccXDdiHILCQji6',NULL,'2025-06-24 22:51:23','2026-02-21 13:51:55',1);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
