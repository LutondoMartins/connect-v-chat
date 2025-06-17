<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_GET['query'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $query = '%' . trim($_GET['query']) . '%';

    $stmt = $pdo->prepare('
        SELECT 
            c.id, c.name, c.is_group, c.description,
            COALESCE(u.profile_pic, NULL) AS avatar,
            (SELECT m.content FROM messages m WHERE m.chat_id = c.id ORDER BY m.sent_at DESC LIMIT 1) AS last_message,
            (SELECT m.sent_at FROM messages m WHERE m.chat_id = c.id ORDER BY m.sent_at DESC LIMIT 1) AS last_message_time,
            (SELECT u.status FROM users u 
             JOIN chat_members cm ON u.id = cm.user_id 
             WHERE cm.chat_id = c.id AND u.id != ? LIMIT 1) AS status,
            (SELECT u.last_seen FROM users u 
             JOIN chat_members cm ON u.id = cm.user_id 
             WHERE cm.chat_id = c.id AND u.id != ? LIMIT 1) AS last_seen
        FROM chats c
        JOIN chat_members cm ON c.id = cm.chat_id
        LEFT JOIN users u ON cm.user_id = u.id AND u.id != ?
        WHERE cm.user_id = ? AND c.name LIKE ?
        ORDER BY COALESCE(
            (SELECT m.sent_at FROM messages m WHERE m.chat_id = c.id ORDER BY m.sent_at DESC LIMIT 1),
            c.created_at
        ) DESC
    ');
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $query]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar chats
    $formatted_chats = array_map(function ($chat) {
        return [
            'id' => (int)$chat['id'],
            'name' => $chat['name'],
            'is_group' => (bool)$chat['is_group'],
            'description' => $chat['description'],
            'avatar' => $chat['avatar'] ? 'uploads/' . $chat['avatar'] : 'https://via.placeholder.com/48',
            'last_message' => $chat['last_message'],
            'last_message_time' => $chat['last_message_time'] ? date('Y-m-d H:i:s', strtotime($chat['last_message_time'])) : null,
            'status' => $chat['status'],
            'last_seen' => $chat['last_seen'] ? date('Y-m-d H:i:s', strtotime($chat['last_seen'])) : null,
            'unread_count' => 0 // Temporário, até implementar last_read
        ];
    }, $chats);

    echo json_encode(['success' => true, 'chats' => $formatted_chats]);
} catch (PDOException $e) {
    error_log("Erro ao buscar chats: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

exit;
?>