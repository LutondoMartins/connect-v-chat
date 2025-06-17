<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('
        SELECT 
            c.id, 
            c.name, 
            c.is_group, 
            c.description, 
            c.created_at,
            m.content AS last_message,
            m.sent_at AS last_message_time,
            COUNT(mr.id) AS unread_count,
            CASE 
                WHEN c.is_group = 0 THEN (
                    SELECT profile_pic 
                    FROM users u 
                    JOIN chat_members cm2 ON u.id = cm2.user_id 
                    WHERE cm2.chat_id = c.id AND u.id != ? 
                    LIMIT 1
                )
                ELSE NULL 
            END AS avatar,
            CASE 
                WHEN c.is_group = 0 THEN (
                    SELECT status 
                    FROM users u 
                    JOIN chat_members cm2 ON u.id = cm2.user_id 
                    WHERE cm2.chat_id = c.id AND u.id != ? 
                    LIMIT 1
                )
                ELSE NULL 
            END AS status,
            CASE 
                WHEN c.is_group = 0 THEN (
                    SELECT last_seen 
                    FROM users u 
                    JOIN chat_members cm2 ON u.id = cm2.user_id 
                    WHERE cm2.chat_id = c.id AND u.id != ? 
                    LIMIT 1
                )
                ELSE NULL 
            END AS last_seen,
            CASE 
                WHEN c.is_group = 0 THEN (
                    SELECT u.username 
                    FROM users u 
                    JOIN chat_members cm2 ON u.id = cm2.user_id 
                    WHERE cm2.chat_id = c.id AND u.id != ? 
                    LIMIT 1
                )
                ELSE c.name 
            END AS display_name,
            CASE 
                WHEN m.sender_id IS NOT NULL THEN (
                    SELECT u.username 
                    FROM users u 
                    WHERE u.id = m.sender_id 
                    LIMIT 1
                )
                ELSE NULL 
            END AS last_sender_name
        FROM chats c
        JOIN chat_members cm ON c.id = cm.chat_id
        LEFT JOIN messages m ON c.id = m.chat_id
        LEFT JOIN (
            SELECT chat_id, MAX(sent_at) AS max_sent_at
            FROM messages
            GROUP BY chat_id
        ) latest ON m.chat_id = latest.chat_id AND m.sent_at = latest.max_sent_at
        LEFT JOIN message_reads mr ON m.id = mr.message_id AND mr.user_id = ? AND mr.read_at IS NULL
        WHERE cm.user_id = ?
        GROUP BY c.id
        ORDER BY COALESCE(m.sent_at, c.created_at) DESC
    ');
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Definir avatar padrão para grupos
    $default_group_avatar = 'Uploads/geral.jpg';

    // Adicionar prefixo 'Uploads/' ao avatar de usuários e definir avatar padrão para grupos
    $chats = array_map(function($chat) use ($default_group_avatar) {
        if ($chat['is_group'] && is_null($chat['avatar'])) {
            $chat['avatar'] = $default_group_avatar;
        } elseif ($chat['avatar']) {
            $chat['avatar'] = 'Uploads/' . $chat['avatar'];
        }
        return $chat;
    }, $chats);

    echo json_encode(['success' => true, 'chats' => $chats]);
} catch (PDOException $e) {
    error_log("Erro em get_chats: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>