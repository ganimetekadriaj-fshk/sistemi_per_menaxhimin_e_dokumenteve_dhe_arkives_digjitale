-- 1. Krijimi i databazës
CREATE DATABASE IF NOT EXISTS document_archive_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE document_archive_db;


-- 2. Tabela Users
CREATE TABLE Users (
    user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'USER') NOT NULL DEFAULT 'USER',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- 3. Tabela Categories
CREATE TABLE Categories (
    category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;


-- 4. Tabela Documents
CREATE TABLE Documents (
    document_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    content_type VARCHAR(100),
    file_size BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_archived BOOLEAN NOT NULL DEFAULT FALSE,

    uploaded_by BIGINT NOT NULL,
    category_id BIGINT NOT NULL,

    CONSTRAINT fk_documents_users
        FOREIGN KEY (uploaded_by)
        REFERENCES Users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_documents_categories
        FOREIGN KEY (category_id)
        REFERENCES Categories(category_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- 5. Tabela Document_Access
CREATE TABLE Document_Access (
    document_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    permission ENUM('VIEW', 'EDIT') NOT NULL DEFAULT 'VIEW',

    PRIMARY KEY (document_id, user_id),

    CONSTRAINT fk_access_documents
        FOREIGN KEY (document_id)
        REFERENCES Documents(document_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_access_users
        FOREIGN KEY (user_id)
        REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- 6. Kontrollimi i tabelave
SHOW TABLES;