<?php
session_start();

// 初始化会话列表
if (!isset($_SESSION['conversations'])) {
    $_SESSION['conversations'] = [];
    $_SESSION['conversation_id'] = 0;
}

// 处理会话切换
if (isset($_GET['conversation_id'])) {
    $current_conversation_id = intval($_GET['conversation_id']);
    $_SESSION['conversation_id'] = $current_conversation_id;
} else {
    $current_conversation_id = $_SESSION['conversation_id'];
}

// 初始化当前会话
if (!isset($_SESSION['conversations'][$current_conversation_id])) {
    $_SESSION['conversations'][$current_conversation_id] = [
        'id' => $current_conversation_id,
        'name' => '会话 ' . $current_conversation_id,
        'messages' => []
    ];
}

// 更新会话名称
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conversation_name'])) {
    $name = trim($_POST['conversation_name']);
    if ($name !== '') {
        $_SESSION['conversations'][$current_conversation_id]['name'] = htmlspecialchars($name);
    }
    // 重新加载页面以更新名称
    header("Location: index.php?conversation_id=$current_conversation_id");
    exit;
}

// 获取当前会话的历史消息
$conversation = $_SESSION['conversations'][$current_conversation_id];
$messages = $conversation['messages'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>对话示例</title>
    <!-- 静态资源 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
    <!-- 自定义样式 -->
    <style>
        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
        #container {
            flex: 1;
            display: flex;
            overflow: hidden;
        }
        #conversation-list {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            overflow-y: auto;
        }
        #conversation-list a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px;
        }
        #conversation-list a:hover, #conversation-list a.active {
            background-color: #495057;
        }
        #chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #chat-header {
            background-color: #fff;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        #chat-header form {
            display: flex;
            align-items: center;
        }
        #chat-header input {
            flex: 1;
            margin-right: 10px;
        }
        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #e9ecef;
        }
        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            max-width: 80%;
        }
        .message .avatar {
            width: 45px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-right: 10px;
        }
        .message .content {
            max-width: 100%;
            padding: 10px;
            border-radius: 10px;
            position: relative;
        }
        .message.user {
            margin-left: auto;
            flex-direction: row-reverse;
            text-align: right;
        }
        .message.user .avatar {
            margin-left: 10px;
            margin-right: 0;
        }
        .message.user .content {
            background-color: #007bff;
            color: #fff;
        }
        .message.assistant {
            flex-direction: row;
        }
        .message.assistant .content {
            background-color: #fff;
        }
        .message .copy-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            cursor: pointer;
            color: #888;
        }
        .message .copy-btn:hover {
            color: #000;
        }
        .message .name {
            font-size: 0.8rem;
            color: #666;
        }
        #input-area {
            padding: 10px;
            background-color: #fff;
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
        }
        #input-area textarea {
            flex: 1;
            resize: none;
            height: 50px;
        }
        #send-btn {
            margin-left: 10px;
        }
        pre {
            position: relative;
        }
        pre .copy-code-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
            color: #888;
        }
        pre .copy-code-btn:hover {
            color: #000;
        }
        /* Spinner 样式 */
        .spinner {
            text-align: center;
            color: #777;
            margin-bottom: 10px;
        }
        /* 版权信息 */
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
            flex-shrink: 0;
        }
    </style>
    <!-- 引入 marked.js 用于 Markdown 渲染 -->
    <script src="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-y/marked/4.0.2/marked.min.js"></script>
    <!-- 引入 highlight.js 用于代码高亮 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
</head>
<body>
    <div id="container">
        <!-- 会话列表 -->
        <div id="conversation-list">
            <h5 class="text-center py-2">会话列表</h5>
            <div>
                <?php foreach ($_SESSION['conversations'] as $conv) { ?>
                    <a href="?conversation_id=<?php echo $conv['id']; ?>" class="<?php if ($conv['id'] == $current_conversation_id) echo 'active'; ?>">
                        <?php echo htmlspecialchars($conv['name']); ?>
                    </a>
                <?php } ?>
            </div>
            <button id="new-conversation" class="btn btn-primary btn-block mt-3">新建对话</button>
        </div>

        <!-- 对话区域 -->
        <div id="chat-container">
            <!-- 对话头部 -->
            <div id="chat-header">
                <form method="POST">
                    <input type="text" name="conversation_name" class="form-control" placeholder="输入会话名称" value="<?php echo htmlspecialchars($conversation['name']); ?>">
                    <button type="submit" class="btn btn-primary">保存</button>
                </form>
            </div>
            <!-- 对话消息 -->
            <div id="chat-messages">
                <?php
                // 显示历史对话
                foreach ($messages as $msg) {
                    if ($msg['role'] === 'user') {
                        echo '<div class="message user">';
                        echo '<div class="avatar"><img src="user-avatar.png" alt="User" width="40"><div class="name">用户</div></div>';
                        echo '<div class="content">';
                        echo '<div class="markdown-body">' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
                        echo '<i class="fas fa-copy copy-btn" title="复制"></i>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="message assistant">';
                        echo '<div class="avatar"><img src="ai-avatar.png" alt="Assistant" width="40"><div class="name">助手</div></div>';
                        echo '<div class="content">';
                        echo '<div class="markdown-body">' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
                        echo '<i class="fas fa-copy copy-btn" title="复制"></i>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <!-- 输入区域 -->
            <div id="input-area">
                <textarea id="user-input" rows="1" placeholder="请输入您的消息..."></textarea>
                <button id="send-btn" class="btn btn-primary">发送</button>
            </div>
        </div>
    </div>

    <!-- 版权信息 -->
    <div class="footer">
        <script src="https://w1.foxlet.cn/wp-admin/admin-ajax.php?action=my_ad_output&id=201"></script>
    </div>
    <!-- 引入必要的 JS 库 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- 引入 marked.js 用于 Markdown 渲染 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/4.3.0/marked.min.js"></script>
    <!-- 引入 highlight.js 用于代码高亮 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <!-- 引入 clipboard.js 用于复制功能 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.10/clipboard.min.js"></script>
    <!-- 自定义脚本 -->
    <script>
        // 初始化代码高亮
        function highlightCode() {
            $('pre code').each(function() {
                hljs.highlightElement(this);
            });
        }

        // 添加代码块复制按钮
        function addCopyCodeButtons() {
            $('pre').each(function() {
                if ($(this).find('.copy-code-btn').length === 0) {
                    const copyBtn = $('<i class="fas fa-copy copy-code-btn" title="复制代码"></i>');
                    $(this).prepend(copyBtn);
                }
            });

            // 为代码块的复制按钮添加事件
            new ClipboardJS('.copy-code-btn', {
                target: function(trigger) {
                    return trigger.nextElementSibling;
                }
            });
        }

        // 解析和渲染消息内容
        function renderMessages() {
            $('.markdown-body').each(function() {
                const content = $(this).text();
                $(this).html(marked.parse(content));

                // 实时处理代码高亮和复制按钮
                highlightCode();
                addCopyCodeButtons();
            });
        }

        $(document).ready(function() {
            const sendBtn = $('#send-btn');
            const userInput = $('#user-input');
            const chatMessages = $('#chat-messages');
            const newConversationBtn = $('#new-conversation');

            // 初始渲染历史消息
            renderMessages();

            // 发送消息
            function sendMessage() {
                const message = userInput.val().trim();
                if (message === '') return;

                // 显示用户消息
                const userMessageDiv = $(`
                    <div class="message user">
                        <div class="avatar"><img src="user-avatar.png" alt="User" width="40"><div class="name">用户</div></div>
                        <div class="content">
                            <div class="markdown-body">${marked.parse(message)}</div>
                            <i class="fas fa-copy copy-btn" title="复制"></i>
                        </div>
                    </div>
                `);
                chatMessages.append(userMessageDiv);
                chatMessages.scrollTop(chatMessages[0].scrollHeight);

                // 处理复制按钮和代码高亮
                highlightCode();
                addCopyCodeButtons();

                userInput.val('');

                // 显示提醒文字
                const spinner = $('<div class="spinner">助手回复中...</div>');
                chatMessages.append(spinner);
                chatMessages.scrollTop(chatMessages[0].scrollHeight);

                // 发送消息到后端
                fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({message})
                })
                .then(response => {
                    const reader = response.body.getReader();
                    let partialMessage = '';
                    const assistantMessageDiv = $(`
                        <div class="message assistant">
                            <div class="avatar"><img src="ai-avatar.png" alt="Assistant" width="40"><div class="name">助手</div></div>
                            <div class="content">
                                <div class="markdown-body"></div>
                                <i class="fas fa-copy copy-btn" title="复制"></i>
                            </div>
                        </div>
                    `);
                    spinner.remove();
                    chatMessages.append(assistantMessageDiv);
                    const assistantContent = assistantMessageDiv.find('.markdown-body');

                    function readStream() {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                highlightCode();
                                addCopyCodeButtons();
                                return;
                            }

                            const chunk = new TextDecoder("utf-8").decode(value);
                            const lines = chunk.split('\n');

                            lines.forEach(line => {
                                line = line.trim();
                                if (line.startsWith('data: ')) {
                                    const dataStr = line.replace('data: ', '').trim();
                                    if (dataStr === '[DONE]') {
                                        return;
                                    }
                                    try {
                                        const dataObj = JSON.parse(dataStr);
                                        const content = dataObj.choices[0].delta.content;
                                        if (content) {
                                            partialMessage += content;
                                            assistantContent.html(marked.parse(partialMessage));
                                            chatMessages.scrollTop(chatMessages[0].scrollHeight);

                                            // 实时处理代码高亮和复制按钮
                                            highlightCode();
                                            addCopyCodeButtons();
                                        }
                                    } catch (e) {
                                        console.error('解析错误', e);
                                    }
                                }
                            });

                            readStream();
                        });
                    }
                    readStream();
                });
            }

            sendBtn.click(function() {
                sendMessage();
            });

            userInput.keypress(function(e) {
                if (e.which == 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // 新建对话
            newConversationBtn.click(function() {
                fetch('chat.php?new_conversation=1')
                .then(() => {
                    window.location.href = 'index.php';
                });
            });

            // 初始化复制按钮
            new ClipboardJS('.copy-btn', {
                target: function(trigger) {
                    return trigger.previousElementSibling;
                }
            });

            // 为代码块添加复制功能
            new ClipboardJS('.copy-code-btn', {
                target: function(trigger) {
                    return trigger.nextElementSibling[0];
                }
            });
        });
    </script>
</body>
</html>