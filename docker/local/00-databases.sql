DROP DATABASE IF EXISTS `shopping_cart`;
DROP DATABASE IF EXISTS `shopping_cart_test`;
CREATE DATABASE IF NOT EXISTS `shopping_cart`;
CREATE DATABASE IF NOT EXISTS `shopping_cart_test`;

CREATE USER 'shopping_cart'@'%' IDENTIFIED BY 'P@ssw0rd';
GRANT ALL PRIVILEGES ON `shopping_cart%`.* TO 'ishtaki'@'%';
FLUSH PRIVILEGES;
