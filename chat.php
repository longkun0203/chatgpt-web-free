<?php
session_start();

// 新建对话
if (isset($_GET['new_conversation'])) {
    // 创建新的会话 ID
    $conversation_ids = array_keys($_SESSION['conversations']);
    $new_conversation_id = empty($conversation_ids) ? 1 : max($conversation_ids) + 1;
    $_SESSION['conversations'][$new_conversation_id] = [
        'id' => $new_conversation_id,
        'name' => '新会话',
        'messages' => []
    ];
    $_SESSION['conversation_id'] = $new_conversation_id;
    exit;
}

// 获取当前会话 ID
$current_conversation_id = $_SESSION['conversation_id'];

// 获取用户输入
$input = json_decode(file_get_contents('php://input'), true);
$user_message = $input['message'] ?? '';

if ($user_message === '') {
    exit;
}

// 将用户消息添加到会话历史
$_SESSION['conversations'][$current_conversation_id]['messages'][] = [
    'role' => 'user',
    'content' => $user_message
];

// 准备请求数据，包含最近的 5 条消息
$all_messages = $_SESSION['conversations'][$current_conversation_id]['messages'];
$messages = array_slice($all_messages, -5);

// 将消息格式转换为 OpenAI API 要求的格式
$api_messages = [];
foreach ($messages as $msg) {
    $api_messages[] = [
        'role' => $msg['role'],
        'content' => $msg['content']
    ];
}

// 设置 OpenAI API 密钥
$api_key = 'sk-****'; // 请替换为您的实际 API 密钥

// 设置头信息
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// 初始化助手回复内容
$assistant_reply = '';

// 发送请求到 OpenAI API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://s.lconai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key,
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'gpt-4o-all',
    'messages' => $api_messages,
    'stream' => true,
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$assistant_reply) {
    echo $data;
    echo PHP_EOL;
    ob_flush();
    flush();

    // 解析流式数据，收集助手回复内容
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'data: ') === 0) {
            $payload = substr($line, strlen('data: '));
            if ($payload === '[DONE]') {
                continue;
            }
            $response = json_decode($payload, true);
            if (isset($response['choices'][0]['delta']['content'])) {
                $assistant_reply .= $response['choices'][0]['delta']['content'];
            }
        }
    }
    return strlen($data);
});

curl_exec($ch);
curl_close($ch);

// 将助手的回复添加到会话历史
$_SESSION['conversations'][$current_conversation_id]['messages'][] = [
    'role' => 'assistant',
    'content' => $assistant_reply
];
?>
