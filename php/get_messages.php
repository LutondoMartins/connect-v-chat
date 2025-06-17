<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
$last_message_id = isset($_GET['last_message_id']) ? (int)$_GET['last_message_id'] : 0;

if ($chat_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Chat ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT 
            m.id, 
            m.chat_id, 
            m.sender_id, 
            m.type, 
            m.content AS text, 
            m.file_name, 
            m.file_size, 
            m.sent_at AS time,
            u.username, 
            u.profile_pic AS avatar,
            CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END AS sent,
            (SELECT GROUP_CONCAT(reaction) FROM message_reactions WHERE message_id = m.id) AS reactions,
            EXISTS (
                SELECT 1 
                FROM message_reads mr 
                WHERE mr.message_id = m.id AND mr.user_id = ?
            ) AS is_read
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.chat_id = ? AND m.id > ?
        ORDER BY m.sent_at ASC
    ');
    $stmt->execute([$user_id, $user_id, $chat_id, $last_message_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as &$msg) {
        $msg['reactions'] = $msg['reactions'] ? explode(',', $msg['reactions']) : [];
        $msg['avatar'] = $msg['avatar'] ? 'Uploads/' . $msg['avatar'] : null;
        $msg['is_read'] = (bool)$msg['is_read'];
    }

    error_log("get_messages: chat_id=$chat_id, user_id=$user_id, last_message_id=$last_message_id, mensagens=" . count($messages));
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (PDOException $e) {
    error_log('Erro ao carregar mensagens: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}
exit;
?>