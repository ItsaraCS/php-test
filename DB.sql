CREATE DATABASE IF NOT EXISTS php_test;

USE php_test;

CREATE TABLE IF NOT EXISTS users(
    user_id INT(3) NOT NULL AUTO_INCREMENT,
    email VARCHAR(200) NOT NULL,
    password VARCHAR(200) NOT NULL,
    firstname VARCHAR(200) NOT NULL,
    lastname VARCHAR(200) NOT NULL,  
    PRIMARY KEY (user_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS menus(
    menu_id INT(3) NOT NULL AUTO_INCREMENT,
    menu_item INT(10) NOT NULL,
    menu_name VARCHAR(200) NOT NULL,
    user_id INT(3) NOT NULL,
    PRIMARY KEY (menu_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS orders(
    order_id INT(3) NOT NULL AUTO_INCREMENT,
    order_code VARCHAR(10) NOT NULL,
    order_name VARCHAR(200) NOT NULL,
    create_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP NULL,
    user_id INT(3) NOT NULL,
    PRIMARY KEY (order_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;