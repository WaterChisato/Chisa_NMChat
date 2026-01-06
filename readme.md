# 匿名聊天系统

## 基于PHP和MySQL开发的匿名聊天系统


### 功能特性

### 🎯 核心功能
~~~
- 匿名聊天：无需注册登录即可参与聊天
- 实时消息：消息实时推送到所有在线用户
- 自定义昵称：用户可以设置个性化聊天昵称
- 响应式设计：适配PC端和移动端
~~~

### 🔐 管理功能
~~~
- 管理员登录：安全的管理员身份验证  
- 消息管理：查看、删除聊天记录  
- 统计信息：实时统计消息总数、用户数  
- 密码管理：支持在线修改管理员密码  
- 数据清理：一键清空所有聊天记录  
~~~
📁 文件功能
~~~
- 保存聊天记录，不支持文件传输
~~~

### 系统架构
~~~
匿名聊天系统/       
├── index.php                   # 国内聊天站主界面
├── admin.php                   # 国内聊天站管理界面
├── config.php                  # 数据库配置文件               # 网站图标
└── README.md                   # 项目说明文件
~~~
数据库结构
~~~
系统自动创建以下数据库表：

chat_messages（聊天消息表）

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

chat_admin（管理员表）

CREATE TABLE `chat_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
~~~
### 快速开始
~~~
环境要求

- PHP 5.6+（推荐PHP 7.0+）
- MySQL 5.6+
- Web服务器（Apache/Nginx）
- 支持HTTPS（可选，但推荐）

安装步骤

1. 克隆或下载项目文件
git clone [项目仓库地址]
2. 上传文件到服务器将项目文件上传到您的Web服务器目录。
3. 配置数据库连接编辑 
"config.php" 文件，设置您的数据库连接信息：
define('DB_HOST', 'localhost');
define('DB_USER', '您的数据库用户名');
define('DB_PASS', '您的数据库密码');
define('DB_NAME', '您的数据库名');
4. 设置文件权限
chmod 755 config.php
chmod 755 index.php
chmod 755 admin.php
5. 访问系统
   - 国内聊天站：
"https://您的域名/index.php"
   - 管理界面：
"https://您的域名/admin.php"

默认管理员账号

- 用户名：
"admin"
- 密码：
"admin123"

注意：首次登录后请立即修改密码！
~~~
### 使用说明
~~~
1. 用户使用

1. 访问聊天站选择页面
2. 根据网络环境选择合适的聊天站：
   无需代理，保存聊天记录，不支持文件传输
3. 输入昵称（可选），开始聊天

2. 管理员使用

1. 访问 
"admin.php" 登录管理界面
2. 查看聊天统计信息
3. 管理聊天记录
4. 修改系统设置



配置说明

网站图标

修改 
"favicon.jpg" 或更新HTML中的图标链接。



安全配置

1. 修改默认管理员账号和密码
2. 定期备份数据库
3. 开启HTTPS加密传输
4. 限制IP访问频率

技术细节

前端技术

- HTML5 + CSS3
- 原生JavaScript
- 响应式布局
- CSS动画效果

后端技术

- PHP 7+
- MySQL数据库
- 会话管理
- 安全过滤（防XSS、SQL注入）

安全特性

- SQL注入防护
- XSS攻击防护
- 密码加密存储
- IP地址记录
- 会话管理
~~~
### 常见问题

~~~
Q: 如何确保聊天安全？

A: 系统采取以下安全措施：

1. 所有消息进行HTML转义
2. 密码使用MD5加密存储
3. 记录用户IP地址
4. 管理员操作需要登录验证

Q: 如何备份聊天记录？

A: 可以通过以下方式备份：

1. 通过phpMyAdmin导出数据库
2. 在管理界面查看历史消息
3. 定期自动备份脚本

Q: 如何自定义界面？

A: 修改CSS样式文件中的相关类名和属性，或替换网站图标。
~~~
### 故障排除
~~~
数据库连接失败

1. 检查 
"config.php" 中的数据库配置
2. 确保MySQL服务正在运行
3. 检查数据库用户权限
4. 验证网络连接

管理员无法登录

1. 检查数据库中的管理员账号
2. 验证密码加密方式
3. 清除浏览器缓存
4. 检查会话配置

消息无法发送

1. 检查数据库连接
2. 查看PHP错误日志
3. 验证文件权限
4. 检查网络连接

更新日志

v1.0.0 (2026-01-06)

- 初始版本发布
- 基础匿名聊天功能
- 管理员管理界面
~~~
## 贡献指南

### 欢迎贡献代码！请遵循以下步骤：
~~~
1. Fork 本仓库
2. 创建特性分支 (
"git checkout -b feature/AmazingFeature")
3. 提交更改 (
"git commit -m 'Add some AmazingFeature'")
4. 推送到分支 (
"git push origin feature/AmazingFeature")
5. 开启一个 Pull Request

~~~
许可证

本项目采用 MIT 许可证。详情请参阅 "LICENSE" (LICENSE) 文件。

免责声明
~~~
1. 本系统仅供学习和交流使用
2. 请遵守当地法律法规
3. 禁止用于非法用途
4. 作者不承担任何使用责任
5. 请勿在聊天中泄露个人敏感信息
~~~


## 致谢

### 感谢所有为本项目做出贡献的开发者！
----
温馨提示：请合理使用聊天系统，文明交流，共建和谐网络环境。
