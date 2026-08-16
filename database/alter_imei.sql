-- IMEI / serial tracking enhancements (safe to re-run where possible)
-- Links serials to sale/purchase lines and enforces unique IMEI values.

ALTER TABLE `product_serials`
    ADD COLUMN IF NOT EXISTS `sale_item_id` INT UNSIGNED DEFAULT NULL AFTER `warehouse_id`,
    ADD COLUMN IF NOT EXISTS `purchase_item_id` INT UNSIGNED DEFAULT NULL AFTER `sale_item_id`;

-- Unique IMEI (NULLs allowed for serial-only rows)
SET @idx_exists := (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'product_serials' AND index_name = 'uq_product_serials_imei'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE `product_serials` ADD UNIQUE KEY `uq_product_serials_imei` (`imei`)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx2 := (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'product_serials' AND index_name = 'idx_serial_sale_item'
);
SET @sql2 := IF(@idx2 = 0,
    'ALTER TABLE `product_serials` ADD KEY `idx_serial_sale_item` (`sale_item_id`)',
    'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
