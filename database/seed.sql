-- ============================================================
--  Nubia Inventory - Seed Data
--  Default password for all seeded users: admin123
-- ============================================================

USE `nubia_inventory`;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------- Roles -------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`) VALUES
    (1, 'Super Admin', 'super-admin', 'Full unrestricted access', 1),
    (2, 'Admin',       'admin',       'Administrative access', 1),
    (3, 'Manager',     'manager',     'Manage operations & reports', 1),
    (4, 'Cashier',     'cashier',     'POS & sales operations', 1),
    (5, 'Salesman',    'salesman',    'Sales & customers', 1),
    (6, 'Store Keeper','store-keeper','Inventory & stock', 1),
    (7, 'Accountant',  'accountant',  'Accounting & expenses', 1),
    (8, 'Employee',    'employee',    'Limited access', 1);

-- ---------------------- Permissions -------------------------
-- The canonical catalog is config/permissions.php. install.php runs
-- database/apply_full_permissions.php after this seed so fresh installs and
-- upgrades use the same fine-grained permission definitions. If importing
-- schema.sql + seed.sql manually, run that PHP sync script afterwards.

-- ---------------------- Warehouses --------------------------
INSERT INTO `warehouses` (`id`, `name`, `code`, `phone`, `address`, `is_default`, `status`) VALUES
    (1, 'Main Store', 'WH-MAIN', '017000000000', 'Head Office, Dhaka', 1, 1),
    (2, 'Warehouse B', 'WH-B', '017000000001', 'Branch, Chittagong', 0, 1);

-- ---------------------- Users -------------------------------
-- Password for all = admin123
INSERT INTO `users` (`role_id`, `warehouse_id`, `name`, `email`, `phone`, `password`, `status`) VALUES
    (1, 1, 'Super Admin', 'admin@nubia.test', '01700000000', '$2y$10$KI.MZFKDEfbFBh3mgWZuIOAqn8gOu5R2kPQ/rM35ZOEwvHOY62A12', 'active'),
    (4, 1, 'Cashier One', 'cashier@nubia.test', '01700000001', '$2y$10$KI.MZFKDEfbFBh3mgWZuIOAqn8gOu5R2kPQ/rM35ZOEwvHOY62A12', 'active');

-- ---------------------- Units -------------------------------
INSERT INTO `units` (`name`, `short_name`) VALUES
    ('Piece', 'pcs'), ('Kilogram', 'kg'), ('Gram', 'g'),
    ('Litre', 'ltr'), ('Box', 'box'), ('Pack', 'pack'), ('Dozen', 'dz');

-- ---------------------- Brands ------------------------------
INSERT INTO `brands` (`name`, `slug`) VALUES
    ('Generic', 'generic'), ('Samsung', 'samsung'), ('Apple', 'apple'),
    ('Nestle', 'nestle'), ('Unilever', 'unilever');

-- ---------------------- Categories --------------------------
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`) VALUES
    (1, NULL, 'Electronics', 'electronics'),
    (2, NULL, 'Groceries', 'groceries'),
    (3, NULL, 'Fashion', 'fashion'),
    (4, NULL, 'Pharmacy', 'pharmacy'),
    (5, 1, 'Mobile Phones', 'mobile-phones'),
    (6, 1, 'Accessories', 'accessories');

-- ---------------------- Products ----------------------------
INSERT INTO `products` (`category_id`, `brand_id`, `unit_id`, `name`, `slug`, `sku`, `barcode`, `cost_price`, `selling_price`, `wholesale_price`, `tax_rate`, `alert_quantity`, `has_imei`) VALUES
    (5, 2, 1, 'Samsung Galaxy A15', 'samsung-galaxy-a15', 'SKU-A15', '8801000000015', 18000, 21500, 20500, 5, 5, 1),
    (5, 3, 1, 'Apple iPhone 15', 'apple-iphone-15', 'SKU-IP15', '8801000000022', 110000, 129000, 125000, 5, 3, 1),
    (6, 1, 1, 'USB-C Cable 1m', 'usb-c-cable-1m', 'SKU-USBC', '8801000000039', 120, 250, 200, 0, 20, 0),
    (2, 4, 1, 'Nescafe Classic 100g', 'nescafe-classic-100g', 'SKU-NESC', '8801000000046', 480, 650, 600, 0, 15, 0),
    (2, 5, 1, 'Lifebuoy Soap 100g', 'lifebuoy-soap', 'SKU-LIFE', '8801000000053', 45, 70, 60, 0, 30, 0);

-- ---------------------- Opening Stock -----------------------
INSERT INTO `stock` (`product_id`, `warehouse_id`, `quantity`) VALUES
    (1, 1, 12), (2, 1, 4), (3, 1, 50), (4, 1, 25), (5, 1, 100);

-- ---------------------- Customers ---------------------------
INSERT INTO `customers` (`name`, `phone`, `email`, `type`, `opening_balance`) VALUES
    ('Walk-in Customer', '000', NULL, 'retail', 0),
    ('Rahim Traders', '01811111111', 'rahim@example.com', 'wholesale', 0),
    ('Karim Store', '01822222222', 'karim@example.com', 'wholesale', 0);

-- ---------------------- Suppliers ---------------------------
INSERT INTO `suppliers` (`name`, `phone`, `email`, `company`, `opening_balance`) VALUES
    ('Tech Distributors Ltd', '01911111111', 'sales@techdist.com', 'Tech Distributors', 0),
    ('Global FMCG Supply', '01922222222', 'info@globalfmcg.com', 'Global FMCG', 0);

-- ---------------------- Expense categories ------------------
INSERT INTO `expense_categories` (`name`) VALUES
    ('Salary'), ('Electricity'), ('Internet'), ('Transport'),
    ('Office'), ('Marketing'), ('Rent'), ('Miscellaneous');

-- ---------------------- Settings ----------------------------
INSERT INTO `settings` (`key`, `value`, `group`) VALUES
    ('business_name', 'Nubia Inventory', 'general'),
    ('business_email', 'contact@nubia.test', 'general'),
    ('business_phone', '+880 1700 000000', 'general'),
    ('business_address', 'Dhaka, Bangladesh', 'general'),
    ('currency', 'BDT', 'general'),
    ('currency_symbol', 'Tk', 'general'),
    ('timezone', 'Asia/Dhaka', 'general'),
    ('invoice_prefix', 'INV', 'invoice'),
    ('purchase_prefix', 'PUR', 'invoice'),
    ('tax_rate', '5', 'tax'),
    ('vat_rate', '0', 'tax'),
    ('theme', 'light', 'appearance'),
    ('language', 'en', 'appearance'),
    ('logo', '', 'general');

SET FOREIGN_KEY_CHECKS = 1;

-- End of seed.
