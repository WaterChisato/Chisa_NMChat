<?php
// admin.php - 管理界面
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<div style='padding: 20px; background: #f8d7da; color: #721c24; text-align: center;'>
        数据库连接失败，请检查config.php中的配置
    </div>");
}
$conn->set_charset("utf8");

$login_error = '';
$password_error = '';
$password_success = '';

// 管理员登录处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $login_username = $conn->real_escape_string($_POST['username']);
    $login_password = md5($conn->real_escape_string($_POST['password']));
    
    // 修改为查询 chat_admin 表
    $sql = "SELECT * FROM `chat_admin` WHERE `username` = '$login_username' AND `password` = '$login_password'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $login_username;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = "用户名或密码错误！<br>默认账号: admin / admin123";
    }
}

// 管理员退出
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// 修改密码处理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        $password_error = "请先登录管理员账号";
    } else {
        $current_password = md5($conn->real_escape_string($_POST['current_password']));
        $new_password = $conn->real_escape_string($_POST['new_password']);
        $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
        $admin_user = $_SESSION['admin_username'];
        
        // 修改为查询 chat_admin 表
        $check_sql = "SELECT * FROM `chat_admin` WHERE `username` = '$admin_user' AND `password` = '$current_password'";
        $result = $conn->query($check_sql);
        
        if ($result && $result->num_rows > 0) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $new_password_hash = md5($new_password);
                    // 修改为更新 chat_admin 表
                    $update_sql = "UPDATE `chat_admin` SET `password` = '$new_password_hash' WHERE `username` = '$admin_user'";
                    
                    if ($conn->query($update_sql)) {
                        $password_success = "密码修改成功！";
                    } else {
                        $password_error = "密码修改失败！";
                    }
                } else {
                    $password_error = "新密码长度至少6位！";
                }
            } else {
                $password_error = "新密码和确认密码不一致！";
            }
        } else {
            $password_error = "当前密码错误！";
        }
    }
}

// 删除消息处理
if (isset($_GET['delete_msg']) && isset($_SESSION['admin_logged_in'])) {
    $msg_id = intval($_GET['delete_msg']);
    $delete_sql = "DELETE FROM chat_messages WHERE id = $msg_id";
    if ($conn->query($delete_sql)) {
        header('Location: admin.php?deleted=1');
        exit;
    }
}

// 清空聊天记录
if (isset($_GET['clear_all']) && isset($_SESSION['admin_logged_in'])) {
    $clear_sql = "TRUNCATE TABLE chat_messages";
    if ($conn->query($clear_sql)) {
        header('Location: admin.php?cleared=1');
        exit;
    }
}

// 获取统计数据
if (isset($_SESSION['admin_logged_in'])) {
    $total_messages = $conn->query("SELECT COUNT(*) as count FROM chat_messages")->fetch_assoc()['count'];
    $total_users = $conn->query("SELECT COUNT(DISTINCT username) as count FROM chat_messages")->fetch_assoc()['count'];
    $today_messages = $conn->query("SELECT COUNT(*) as count FROM chat_messages WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
    
    // 获取最新消息
    $messages_result = $conn->query("SELECT * FROM chat_messages ORDER BY id DESC LIMIT 50");
    
    // 获取系统信息
    $db_info = $conn->query("SELECT VERSION() as version")->fetch_assoc();
    $db_size = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = DATABASE()")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>聊天室管理面板</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .admin-nav {
            background: #34495e;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        .admin-content {
            padding: 20px;
        }
        .stats-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #3498db;
        }
        .messages-panel {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .message-item {
            border-bottom: 1px solid #eee;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message-item:hover {
            background: #f9f9f9;
        }
        .message-info {
            flex: 1;
        }
        .message-actions a {
            color: #e74c3c;
            text-decoration: none;
            margin-left: 10px;
        }
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            transition: opacity 0.3s;
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 10px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .password-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .system-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <!-- 登录界面 -->
            <div class="admin-header">
                <h1>管理员登录</h1>
                <p>请输入管理员账号和密码</p>
            </div>
            
            <div class="login-form">
                <?php if(isset($_GET['deleted'])): ?>
                    <div class="success">消息删除成功！</div>
                <?php endif; ?>
                <?php if(isset($_GET['cleared'])): ?>
                    <div class="success">所有聊天记录已清空！</div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>用户名:</label>
                        <input type="text" name="username" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label>密码:</label>
                        <input type="password" name="password" value="admin123" required>
                    </div>
                    <button type="submit" name="admin_login" class="btn">登录</button>
                    <?php if ($login_error): ?>
                        <div class="error"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                </form>
            </div>
            
        <?php else: ?>
            <!-- 管理面板 -->
            <div class="admin-header">
                <h1>聊天室管理面板</h1>
                <p>欢迎回来，<?php echo $_SESSION['admin_username']; ?></p>
            </div>
            
            <div class="admin-nav">
                <div>
                    <a href="admin.php">管理首页</a>
                    <a href="index.php" target="_blank">查看聊天室</a>
                </div>
                <div>
                    <span style="color: #ecf0f1; margin-right: 10px;">登录账号: <?php echo $_SESSION['admin_username']; ?></span>
                    <a href="admin.php?logout=1">退出登录</a>
                </div>
            </div>
            
            <div class="admin-content">
                <!-- 系统信息 -->
                <div class="system-info">
                    <h3>系统信息</h3>
                    <p>数据库版本: <?php echo isset($db_info) ? $db_info['version'] : ''; ?></p>
                    <p>数据库大小: <?php echo isset($db_size) ? $db_size['size_mb'] . ' MB' : ''; ?></p>
                </div>
                
                <!-- 统计信息 -->
                <div class="stats-panel">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_messages; ?></div>
                        <div>总消息数</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_users; ?></div>
                        <div>总用户数</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $today_messages; ?></div>
                        <div>今日消息</div>
                    </div>
                </div>
                
                <!-- 消息管理 -->
                <div class="messages-panel">
                    <h3>最近消息</h3>
                    <div style="margin-bottom: 15px;">
                        <a href="admin.php?clear_all=1" class="btn btn-danger" onclick="return confirm('确定要清空所有聊天记录吗？此操作不可撤销！')">清空所有记录</a>
                    </div>
                    
                    <?php if ($messages_result && $messages_result->num_rows > 0): ?>
                        <?php while($message = $messages_result->fetch_assoc()): ?>
                        <div class="message-item">
                            <div class="message-info">
                                <strong><?php echo htmlspecialchars($message['username']); ?></strong>
                                <span style="color: #666; font-size: 0.9em;">(IP: <?php echo htmlspecialchars($message['ip_address']); ?>)</span>
                                <br>
                                <?php echo htmlspecialchars($message['message']); ?>
                                <br>
                                <small style="color: #999;"><?php echo $message['created_at']; ?></small>
                            </div>
                            <div class="message-actions">
                                <a href="admin.php?delete_msg=<?php echo $message['id']; ?>" onclick="return confirm('确定要删除这条消息吗？')">删除</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #999; padding: 20px;">暂无聊天记录</p>
                    <?php endif; ?>
                </div>
                
                <!-- 密码修改 -->
                <div class="password-form">
                    <h3>修改密码</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>当前密码:</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>新密码:</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>确认新密码:</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-success">修改密码</button>
                        <?php if ($password_error): ?>
                            <div class="error"><?php echo $password_error; ?></div>
                        <?php endif; ?>
                        <?php if ($password_success): ?>
                            <div class="success"><?php echo $password_success; ?></div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>
