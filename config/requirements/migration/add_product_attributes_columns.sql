CREATE TABLE IF NOT EXISTS product_attributes (
  attribute_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  attribute VARCHAR(100) NOT NULL,
  value VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_product_attributes_product
    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
    ON DELETE CASCADE
);
