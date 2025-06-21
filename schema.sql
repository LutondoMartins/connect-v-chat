CREATE DATABASE connect_v_chat;
USE connect_v_chat;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255),
    status ENUM('online', 'offline') DEFAULT 'offline',
    last_seen DATETIME,
    created_at DATETIME NOT NULL
);

CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    is_group BOOLEAN NOT NULL DEFAULT 0,
    description TEXT,
    created_at DATETIME NOT NULL
);

CREATE TABLE chat_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME NOT NULL,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (chat_id, user_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_id INT NOT NULL,
    type ENUM('text', 'file', 'voice') NOT NULL DEFAULT 'text',
    content TEXT,
    file_name VARCHAR(255),
    file_size VARCHAR(50),
    sent_at DATETIME NOT NULL,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE message_reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction VARCHAR(10) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (message_id, user_id, reaction)
);

CREATE TABLE message_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME NOT NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (message_id, user_id)
);

ALTER TABLE messages MODIFY COLUMN type ENUM('text', 'file', 'voice', 'image', 'video') NOT NULL DEFAULT 'text';