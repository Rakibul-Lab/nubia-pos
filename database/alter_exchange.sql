-- Exchange / return line items (safe to re-run)
CREATE TABLE IF NOT EXISTS `sale_return_items` (
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

CREATE TABLE IF NOT EXISTS `sale_exchanges` (
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
