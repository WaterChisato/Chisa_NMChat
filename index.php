<?php
// index.php - 主聊天界面
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// 处理消息发送
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $username = !empty($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '匿名用户';
    $message = $conn->real_escape_string($_POST['message']);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO chat_messages (username, message, ip_address) VALUES ('$username', '$message', '$ip')";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '发送失败']);
    }
    exit;
}

// 获取聊天消息
if (isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    
    $sql = "SELECT * FROM chat_messages WHERE id > $last_id ORDER BY id ASC";
    $result = $conn->query($sql);
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode($messages);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP匿名聊天室</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .chat-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #666;
        }
        .username { font-weight: bold; color: #3498db; }
        .timestamp { color: #95a5a6; }
        .message-content { color: #2c3e50; }
        .chat-input {
            padding: 20px;
            background: #ecf0f1;
            border-top: 1px solid #bdc3c7;
        }
        .input-group {
            margin-bottom: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 8px;
            font-size: 14px;
            resize: none;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
        .admin-link {
            text-align: center;
            padding: 10px;
            background: #34495e;
            color: white;
        }
        .admin-link a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>匿名聊天室</h1>
            <p>欢迎来到实时聊天室，请文明聊天</p>
        </div>
        
        <div class="admin-link">
            <a href="admin.php" target="_blank">管理员入口</a>
        </div>
        
        <div class="chat-messages" id="messages">
            <!-- 消息将在这里显示 -->
        </div>
        
        <div class="chat-input">
            <div class="input-group">
                <input type="text" id="username" placeholder="请输入您的昵称（默认：匿名用户）" maxlength="20">
            </div>
            <div class="input-group">
                <textarea id="messageInput" placeholder="请输入消息内容..." rows="3" maxlength="500"></textarea>
            </div>
            <button onclick="sendMessage()">发送消息</button>
        </div>
    </div>

    <script>
        let lastMessageId = 0;
        
        // 加载消息
        function loadMessages() {
            fetch(`index.php?action=get_messages&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(messages => {
                    messages.forEach(message => {
                        addMessageToChat(message);
                        lastMessageId = Math.max(lastMessageId, message.id);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
        
        // 添加消息到聊天界面
        function addMessageToChat(message) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';
            
            const messageTime = new Date(message.created_at).toLocaleString();
            
            messageDiv.innerHTML = `
                <div class="message-header">
                    <span class="username">${escapeHtml(message.username)}</span>
                    <span class="timestamp">${messageTime}</span>
                </div>
                <div class="message-content">${escapeHtml(message.message)}</div>
            `;
            
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        // 发送消息
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const usernameInput = document.getElementById('username');
            const message = messageInput.value.trim();
            
            if (message === '') {
                alert('请输入消息内容');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', usernameInput.value.trim());
            formData.append('message', message);
            
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    alert('发送失败，请重试');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('发送失败，请检查网络连接');
            });
        }
        
        // HTML转义防止XSS攻击
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 回车发送消息
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // 每2秒刷新一次消息
        setInterval(loadMessages, 2000);
        
        // 页面加载时获取消息
        window.onload = loadMessages;
    </script>
</body>
</html>
<?php
$conn->close();
?>
