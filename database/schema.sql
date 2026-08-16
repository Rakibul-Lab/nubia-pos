-- ============================================================
--  Nubia Inventory - Database Schema
--  Engine: MySQL 8+ / InnoDB / utf8mb4
--  Normalized design with foreign keys, indexes & constraints.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `nubia_inventory`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `nubia_inventory`;

-- ------------------------------------------------------------
--  Roles & Permissions (RBAC)
-- ------------------------------------------------------------
CREATE TABLE `roles` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(80)  NOT NULL,
    `slug`        VARCHAR(80)  NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `slug`       VARCHAR(120) NOT NULL,
    `module`     VARCHAR(80)  NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_permissions_slug` (`slug`),
    KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
    `role_id`       INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`       FOREIGN KEY (`role_id`)       REFERENCES `roles` (`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Users
-- ------------------------------------------------------------
CREATE TABLE `users` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id`        INT UNSIGNED NOT NULL,
    `warehouse_id`   INT UNSIGNED DEFAULT NULL,
    `name`           VARCHAR(120) NOT NULL,
    `email`          VARCHAR(160) NOT NULL,
    `phone`          VARCHAR(30)  DEFAULT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `avatar`         VARCHAR(255) DEFAULT NULL,
    `status`         ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    `otp_code`       VARCHAR(10)  DEFAULT NULL,
    `otp_expires_at` DATETIME     DEFAULT NULL,
    `last_login_at`  DATETIME     DEFAULT NULL,
    `last_login_ip`  VARCHAR(45)  DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role` (`role_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(160) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pr_email` (`email`),
    KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `remember_tokens` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `selector`   VARCHAR(24)  NOT NULL,
    `token_hash` VARCHAR(64)  NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rt_selector` (`selector`),
    KEY `idx_rt_user` (`user_id`),
    CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Catalog: Categories, Brands, Units, Warehouses
-- ------------------------------------------------------------
CREATE TABLE `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`  INT UNSIGNED DEFAULT NULL,
    `name`       VARCHAR(120) NOT NULL,
    `slug`       VARCHAR(140) NOT NULL,
    `image`      VARCHAR(255) DEFAULT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `brands` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `slug`       VARCHAR(140) NOT NULL,
    `logo`       VARCHAR(255) DEFAULT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_brands_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `units` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(80)  NOT NULL,
    `short_name` VARCHAR(20)  NOT NULL,
    `base_unit`  INT UNSIGNED DEFAULT NULL,
    `operator`   ENUM('*','/') NOT NULL DEFAULT '*',
    `operation_value` DECIMAL(12,4) NOT NULL DEFAULT 1,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `warehouses` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `code`       VARCHAR(40)  DEFAULT NULL,
    `phone`      VARCHAR(30)  DEFAULT NULL,
    `email`      VARCHAR(160) DEFAULT NULL,
    `address`    VARCHAR(255) DEFAULT NULL,
    `is_default` TINYINT(1)   NOT NULL DEFAULT 0,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_warehouses_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Products & Variants
-- ------------------------------------------------------------
CREATE TABLE `products` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id`    INT UNSIGNED DEFAULT NULL,
    `brand_id`       INT UNSIGNED DEFAULT NULL,
    `unit_id`        INT UNSIGNED DEFAULT NULL,
    `name`           VARCHAR(200) NOT NULL,
    `slug`           VARCHAR(220) NOT NULL,
    `sku`            VARCHAR(80)  NOT NULL,
    `barcode`        VARCHAR(120) DEFAULT NULL,
    `type`           ENUM('standard','variant','service') NOT NULL DEFAULT 'standard',
    `description`    TEXT         DEFAULT NULL,
    `image`          VARCHAR(255) DEFAULT NULL,
    `cost_price`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `selling_price`  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `wholesale_price` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax_rate`       DECIMAL(6,2)  NOT NULL DEFAULT 0,
    `alert_quantity` DECIMAL(14,2) NOT NULL DEFAULT 5,
    `has_serial`     TINYINT(1)   NOT NULL DEFAULT 0,
    `has_imei`       TINYINT(1)   NOT NULL DEFAULT 0,
    `has_expiry`     TINYINT(1)   NOT NULL DEFAULT 0,
    `status`         TINYINT(1)   NOT NULL DEFAULT 1,
    `created_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_sku` (`sku`),
    KEY `idx_products_barcode` (`barcode`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_brand` (`brand_id`),
    KEY `idx_products_name` (`name`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_products_brand`    FOREIGN KEY (`brand_id`)    REFERENCES `brands` (`id`)     ON DELETE SET NULL,
    CONSTRAINT `fk_products_unit`     FOREIGN KEY (`unit_id`)     REFERENCES `units` (`id`)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variants` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`    INT UNSIGNED NOT NULL,
    `variant_name`  VARCHAR(160) NOT NULL,
    `color`         VARCHAR(60)  DEFAULT NULL,
    `size`          VARCHAR(60)  DEFAULT NULL,
    `sku`           VARCHAR(90)  NOT NULL,
    `barcode`       VARCHAR(120) DEFAULT NULL,
    `cost_price`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `selling_price` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_variant_sku` (`sku`),
    KEY `idx_variant_product` (`product_id`),
    CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `path`       VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pimg_product` (`product_id`),
    CONSTRAINT `fk_pimg_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Serial / IMEI tracking
CREATE TABLE `product_serials` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`       INT UNSIGNED NOT NULL,
    `variant_id`       INT UNSIGNED DEFAULT NULL,
    `serial_no`        VARCHAR(120) DEFAULT NULL,
    `imei`             VARCHAR(120) DEFAULT NULL,
    `status`           ENUM('available','sold','returned','damaged') NOT NULL DEFAULT 'available',
    `warehouse_id`     INT UNSIGNED DEFAULT NULL,
    `sale_item_id`     INT UNSIGNED DEFAULT NULL,
    `purchase_item_id` INT UNSIGNED DEFAULT NULL,
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_product_serials_imei` (`imei`),
    KEY `idx_serial_product` (`product_id`),
    KEY `idx_serial_no` (`serial_no`),
    KEY `idx_serial_imei` (`imei`),
    KEY `idx_serial_sale_item` (`sale_item_id`),
    CONSTRAINT `fk_serial_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Stock (per product per warehouse) & movement logs
-- ------------------------------------------------------------
CREATE TABLE `stock` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`   INT UNSIGNED NOT NULL,
    `variant_id`   INT UNSIGNED DEFAULT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `quantity`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `expiry_date`  DATE         DEFAULT NULL,
    `batch_no`     VARCHAR(80)  DEFAULT NULL,
    `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_stock` (`product_id`, `variant_id`, `warehouse_id`, `batch_no`),
    KEY `idx_stock_warehouse` (`warehouse_id`),
    CONSTRAINT `fk_stock_product`   FOREIGN KEY (`product_id`)   REFERENCES `products` (`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_stock_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_logs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`    INT UNSIGNED NOT NULL,
    `variant_id`    INT UNSIGNED DEFAULT NULL,
    `warehouse_id`  INT UNSIGNED NOT NULL,
    `type`          ENUM('purchase','sale','adjustment','transfer_in','transfer_out','return_in','return_out','opening','damage','direct_sell') NOT NULL,
    `reference_type` VARCHAR(40) DEFAULT NULL,
    `reference_id`  INT UNSIGNED DEFAULT NULL,
    `quantity`      DECIMAL(14,2) NOT NULL,
    `balance_after` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `note`          TEXT         DEFAULT NULL,
    `imei_codes`    TEXT         DEFAULT NULL,
    `created_by`    INT UNSIGNED DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slog_product` (`product_id`),
    KEY `idx_slog_type` (`type`),
    KEY `idx_slog_ref` (`reference_type`, `reference_id`),
    CONSTRAINT `fk_slog_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_adjustments` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`    VARCHAR(40)  NOT NULL,
    `warehouse_id` INT UNSIGNED NOT NULL,
    `type`         ENUM('addition','subtraction') NOT NULL DEFAULT 'addition',
    `reason`       VARCHAR(255) DEFAULT NULL,
    `created_by`   INT UNSIGNED DEFAULT NULL,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_adj_ref` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_transfers` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`      VARCHAR(40)  NOT NULL,
    `from_warehouse` INT UNSIGNED NOT NULL,
    `to_warehouse`   INT UNSIGNED NOT NULL,
    `note`           VARCHAR(255) DEFAULT NULL,
    `status`         ENUM('pending','completed') NOT NULL DEFAULT 'completed',
    `created_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_transfer_ref` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Parties: Customers & Suppliers
-- ------------------------------------------------------------
CREATE TABLE `customers` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(160) NOT NULL,
    `phone`         VARCHAR(30)  DEFAULT NULL,
    `email`         VARCHAR(160) DEFAULT NULL,
    `company`       VARCHAR(160) DEFAULT NULL,
    `address`       VARCHAR(255) DEFAULT NULL,
    `city`          VARCHAR(80)  DEFAULT NULL,
    `type`          ENUM('retail','wholesale') NOT NULL DEFAULT 'retail',
    `opening_balance` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `credit_limit`  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `loyalty_points` INT NOT NULL DEFAULT 0,
    `status`        TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_customers_phone` (`phone`),
    KEY `idx_customers_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `suppliers` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(160) NOT NULL,
    `phone`         VARCHAR(30)  DEFAULT NULL,
    `email`         VARCHAR(160) DEFAULT NULL,
    `company`       VARCHAR(160) DEFAULT NULL,
    `address`       VARCHAR(255) DEFAULT NULL,
    `city`          VARCHAR(80)  DEFAULT NULL,
    `opening_balance` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `status`        TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_suppliers_phone` (`phone`),
    KEY `idx_suppliers_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Purchases
-- ------------------------------------------------------------
CREATE TABLE `purchases` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`      VARCHAR(40)  NOT NULL,
    `supplier_id`    INT UNSIGNED DEFAULT NULL,
    `warehouse_id`   INT UNSIGNED NOT NULL,
    `purchase_date`  DATE         NOT NULL,
    `status`         ENUM('ordered','received','pending','cancelled') NOT NULL DEFAULT 'received',
    `subtotal`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `shipping`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`          DECIMAL(14,2) NOT NULL DEFAULT 0,
    `paid`           DECIMAL(14,2) NOT NULL DEFAULT 0,
    `due`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `payment_status` ENUM('paid','partial','unpaid') NOT NULL DEFAULT 'unpaid',
    `note`           VARCHAR(255) DEFAULT NULL,
    `created_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_purchase_ref` (`reference`),
    KEY `idx_purchase_supplier` (`supplier_id`),
    KEY `idx_purchase_date` (`purchase_date`),
    CONSTRAINT `fk_purchase_supplier`  FOREIGN KEY (`supplier_id`)  REFERENCES `suppliers` (`id`)  ON DELETE SET NULL,
    CONSTRAINT `fk_purchase_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_items` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `purchase_id` INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `variant_id`  INT UNSIGNED DEFAULT NULL,
    `quantity`    DECIMAL(14,2) NOT NULL,
    `unit_cost`   DECIMAL(14,2) NOT NULL,
    `discount`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `subtotal`    DECIMAL(14,2) NOT NULL,
    `returned_qty` DECIMAL(14,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_pitem_purchase` (`purchase_id`),
    KEY `idx_pitem_product` (`product_id`),
    CONSTRAINT `fk_pitem_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pitem_product`  FOREIGN KEY (`product_id`)  REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Sales
-- ------------------------------------------------------------
CREATE TABLE `sales` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_no`     VARCHAR(40)  NOT NULL,
    `customer_id`    INT UNSIGNED DEFAULT NULL,
    `warehouse_id`   INT UNSIGNED NOT NULL,
    `sale_date`      DATE         NOT NULL,
    `type`           ENUM('pos','retail','wholesale') NOT NULL DEFAULT 'pos',
    `status`         ENUM('completed','pending','cancelled','draft') NOT NULL DEFAULT 'completed',
    `subtotal`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount_type`  ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
    `coupon_code`    VARCHAR(40)  DEFAULT NULL,
    `tax`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `vat`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `shipping`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`          DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total_cost`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `profit`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `paid`           DECIMAL(14,2) NOT NULL DEFAULT 0,
    `due`            DECIMAL(14,2) NOT NULL DEFAULT 0,
    `change_amount`  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `payment_status` ENUM('paid','partial','unpaid') NOT NULL DEFAULT 'paid',
    `payment_method` ENUM('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','credit','mixed') NOT NULL DEFAULT 'cash',
    `note`           VARCHAR(255) DEFAULT NULL,
    `created_by`     INT UNSIGNED DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sale_invoice` (`invoice_no`),
    KEY `idx_sale_customer` (`customer_id`),
    KEY `idx_sale_date` (`sale_date`),
    KEY `idx_sale_status` (`status`),
    CONSTRAINT `fk_sale_customer`  FOREIGN KEY (`customer_id`)  REFERENCES `customers` (`id`)  ON DELETE SET NULL,
    CONSTRAINT `fk_sale_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_items` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_id`     INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED NOT NULL,
    `variant_id`  INT UNSIGNED DEFAULT NULL,
    `quantity`    DECIMAL(14,2) NOT NULL,
    `unit_price`  DECIMAL(14,2) NOT NULL,
    `unit_cost`   DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `subtotal`    DECIMAL(14,2) NOT NULL,
    `returned_qty` DECIMAL(14,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_sitem_sale` (`sale_id`),
    KEY `idx_sitem_product` (`product_id`),
    CONSTRAINT `fk_sitem_sale`    FOREIGN KEY (`sale_id`)    REFERENCES `sales` (`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_sitem_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Returns (sales & purchases)
-- ------------------------------------------------------------
CREATE TABLE `sale_returns` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`  VARCHAR(40)  NOT NULL,
    `sale_id`    INT UNSIGNED NOT NULL,
    `total`      DECIMAL(14,2) NOT NULL DEFAULT 0,
    `reason`     VARCHAR(255) DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sreturn_ref` (`reference`),
    CONSTRAINT `fk_sreturn_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_return_items` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sale_return_id` INT UNSIGNED NOT NULL,
    `sale_item_id`   INT UNSIGNED NOT NULL,
    `product_id`     INT UNSIGNED NOT NULL,
    `quantity`       DECIMAL(14,2) NOT NULL,
    `unit_price`     DECIMAL(14,2) NOT NULL,
    `subtotal`       DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_sri_return` (`sale_return_id`),
    KEY `idx_sri_item` (`sale_item_id`),
    CONSTRAINT `fk_sri_return`  FOREIGN KEY (`sale_return_id`) REFERENCES `sale_returns` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sri_item`    FOREIGN KEY (`sale_item_id`)   REFERENCES `sale_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sri_product` FOREIGN KEY (`product_id`)     REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_exchanges` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`        VARCHAR(40)  NOT NULL,
    `original_sale_id` INT UNSIGNED NOT NULL,
    `new_sale_id`      INT UNSIGNED DEFAULT NULL,
    `sale_return_id`   INT UNSIGNED DEFAULT NULL,
    `return_total`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `new_total`        DECIMAL(14,2) NOT NULL DEFAULT 0,
    `difference`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `settlement`       ENUM('even','customer_pays','refund') NOT NULL DEFAULT 'even',
    `amount_paid`      DECIMAL(14,2) NOT NULL DEFAULT 0,
    `amount_refunded`  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `payment_method`   VARCHAR(40)  DEFAULT NULL,
    `reason`           VARCHAR(255) DEFAULT NULL,
    `created_by`       INT UNSIGNED DEFAULT NULL,
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sexchange_ref` (`reference`),
    KEY `idx_sex_original` (`original_sale_id`),
    KEY `idx_sex_new` (`new_sale_id`),
    CONSTRAINT `fk_sex_original` FOREIGN KEY (`original_sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sex_new`      FOREIGN KEY (`new_sale_id`)      REFERENCES `sales` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_sex_return`   FOREIGN KEY (`sale_return_id`)   REFERENCES `sale_returns` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_returns` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`   VARCHAR(40)  NOT NULL,
    `purchase_id` INT UNSIGNED NOT NULL,
    `total`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `reason`      VARCHAR(255) DEFAULT NULL,
    `created_by`  INT UNSIGNED DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_preturn_ref` (`reference`),
    CONSTRAINT `fk_preturn_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Quotations
-- ------------------------------------------------------------
CREATE TABLE `quotations` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`   VARCHAR(40)  NOT NULL,
    `customer_id` INT UNSIGNED DEFAULT NULL,
    `quote_date`  DATE         NOT NULL,
    `valid_until` DATE         DEFAULT NULL,
    `subtotal`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `discount`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total`       DECIMAL(14,2) NOT NULL DEFAULT 0,
    `status`      ENUM('draft','sent','accepted','declined','converted') NOT NULL DEFAULT 'draft',
    `note`        VARCHAR(255) DEFAULT NULL,
    `created_by`  INT UNSIGNED DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quote_ref` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotation_items` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quotation_id` INT UNSIGNED NOT NULL,
    `product_id`   INT UNSIGNED NOT NULL,
    `quantity`     DECIMAL(14,2) NOT NULL,
    `unit_price`   DECIMAL(14,2) NOT NULL,
    `subtotal`     DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_qitem_quote` (`quotation_id`),
    CONSTRAINT `fk_qitem_quote` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Direct Buy & Sell (special feature)
-- ------------------------------------------------------------
CREATE TABLE `direct_transactions` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`          VARCHAR(40)  NOT NULL,
    `product_name`       VARCHAR(200) NOT NULL,
    `product_id`         INT UNSIGNED DEFAULT NULL,
    `supplier_id`        INT UNSIGNED DEFAULT NULL,
    `supplier_name`      VARCHAR(160) DEFAULT NULL,
    `customer_id`        INT UNSIGNED DEFAULT NULL,
    `customer_name`      VARCHAR(160) DEFAULT NULL,
    `customer_phone`     VARCHAR(30)  DEFAULT NULL,
    `quantity`           DECIMAL(14,2) NOT NULL DEFAULT 1,
    `purchase_price`     DECIMAL(14,2) NOT NULL DEFAULT 0,
    `transportation_cost` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `additional_cost`    DECIMAL(14,2) NOT NULL DEFAULT 0,
    `selling_price`      DECIMAL(14,2) NOT NULL DEFAULT 0,
    `total_cost`         DECIMAL(14,2) NOT NULL DEFAULT 0,
    `profit`             DECIMAL(14,2) NOT NULL DEFAULT 0,
    `add_to_inventory`   TINYINT(1)   NOT NULL DEFAULT 0,
    `payment_method`     ENUM('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','credit') NOT NULL DEFAULT 'cash',
    `paid`               DECIMAL(14,2) NOT NULL DEFAULT 0,
    `due`                DECIMAL(14,2) NOT NULL DEFAULT 0,
    `purchase_id`        INT UNSIGNED DEFAULT NULL,
    `sale_id`            INT UNSIGNED DEFAULT NULL,
    `note`               VARCHAR(255) DEFAULT NULL,
    `created_by`         INT UNSIGNED DEFAULT NULL,
    `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_direct_ref` (`reference`),
    KEY `idx_direct_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Payments (polymorphic: sale, purchase, direct)
-- ------------------------------------------------------------
CREATE TABLE `payments` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`     VARCHAR(40)  NOT NULL,
    `payable_type`  ENUM('sale','purchase','direct','customer','supplier','expense') NOT NULL,
    `payable_id`    INT UNSIGNED NOT NULL,
    `party_type`    ENUM('customer','supplier','none') NOT NULL DEFAULT 'none',
    `party_id`      INT UNSIGNED DEFAULT NULL,
    `direction`     ENUM('in','out') NOT NULL,
    `amount`        DECIMAL(14,2) NOT NULL,
    `method`        ENUM('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','cheque') NOT NULL DEFAULT 'cash',
    `account`       VARCHAR(80)  DEFAULT NULL,
    `note`          VARCHAR(255) DEFAULT NULL,
    `paid_at`       DATE         NOT NULL,
    `created_by`    INT UNSIGNED DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pay_payable` (`payable_type`, `payable_id`),
    KEY `idx_pay_party` (`party_type`, `party_id`),
    KEY `idx_pay_date` (`paid_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Expenses
-- ------------------------------------------------------------
CREATE TABLE `expense_categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expenses` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reference`   VARCHAR(40)  NOT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `warehouse_id` INT UNSIGNED DEFAULT NULL,
    `title`       VARCHAR(160) NOT NULL,
    `amount`      DECIMAL(14,2) NOT NULL,
    `method`      ENUM('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla') NOT NULL DEFAULT 'cash',
    `expense_date` DATE        NOT NULL,
    `note`        VARCHAR(255) DEFAULT NULL,
    `created_by`  INT UNSIGNED DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_expense_ref` (`reference`),
    KEY `idx_expense_category` (`category_id`),
    KEY `idx_expense_date` (`expense_date`),
    CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Settings, Notifications, Activity & Audit logs
-- ------------------------------------------------------------
CREATE TABLE `settings` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(100) NOT NULL,
    `value`      TEXT         DEFAULT NULL,
    `group`      VARCHAR(60)  NOT NULL DEFAULT 'general',
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `title`      VARCHAR(160) NOT NULL,
    `body`       VARCHAR(255) DEFAULT NULL,
    `icon`       VARCHAR(60)  DEFAULT NULL,
    `link`       VARCHAR(255) DEFAULT NULL,
    `type`       VARCHAR(40)  NOT NULL DEFAULT 'info',
    `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED DEFAULT NULL,
    `action`      VARCHAR(120) NOT NULL,
    `module`      VARCHAR(80)  DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `subject_type` VARCHAR(80) DEFAULT NULL,
    `subject_id`  INT UNSIGNED DEFAULT NULL,
    `ip_address`  VARCHAR(45)  DEFAULT NULL,
    `user_agent`  VARCHAR(255) DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_alog_user` (`user_id`),
    KEY `idx_alog_module` (`module`),
    KEY `idx_alog_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- End of schema.
