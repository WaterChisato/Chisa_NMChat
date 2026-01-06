<?php
// config.php - 数据库配置文件
session_start();

// 数据库配置 - 使用您提供的凭据
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

// 创建数据库连接
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    return $conn;
}

// 初始化数据库和表
function initializeDatabase() {
    $conn = getDBConnection();
    
    // 检查数据库是否存在，不存在则创建
    $sql = "CREATE DATABASE IF NOT EXISTS ".DB_NAME." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        $conn->select_db(DB_NAME);
        
        // 创建聊天消息表
        $sql_chat = "CREATE TABLE IF NOT EXISTS `chat_messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `message` text NOT NULL,
            `ip_address` varchar(15) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        // 创建管理员表 - 修改为 chat_admin
        $sql_admin = "CREATE TABLE IF NOT EXISTS `chat_admin` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($sql_chat) && $conn->query($sql_admin)) {
            // 检查是否有管理员账号，如果没有则创建默认账号
            $check_admin = "SELECT * FROM `chat_admin`";
            $result = $conn->query($check_admin);
            
            if ($result->num_rows == 0) {
                // 创建默认管理员账号（用户名：admin，密码：admin123）
                $default_password = md5('admin123');
                $insert_admin = "INSERT INTO `chat_admin` (`username`, `password`) VALUES ('admin', '$default_password')";
                $conn->query($insert_admin);
            }
        } else {
            die("创建表失败: " . $conn->error);
        }
    } else {
        die("创建数据库失败: " . $conn->error);
    }
    $conn->close();
}

// 测试数据库连接
function testDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "数据库连接失败: " . $conn->connect_error;
        echo "<br>请检查以下配置：";
        echo "<br>主机: " . DB_HOST;
        echo "<br>用户名: " . DB_USER;
        echo "<br>数据库名: " . DB_NAME;
        echo "</div>";
        return false;
    }
    $conn->close();
    return true;
}

// 调用初始化函数
initializeDatabase();
testDatabaseConnection();
?>
