ALTER TABLE products
ADD COLUMN url_slug VARCHAR(180) UNIQUE AFTER name,
ADD COLUMN discount DECIMAL(5,2) DEFAULT 0.00 AFTER price,
ADD COLUMN stock INT DEFAULT 0 AFTER discount,
ADD COLUMN status ENUM('active','inactive') DEFAULT 'active' AFTER stock;
