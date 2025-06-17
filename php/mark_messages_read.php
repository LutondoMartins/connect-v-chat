<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once 'config.php';

try {
    $user_id = (int)$_SESSION['user_id'];
    $chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;

    if ($chat_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID do chat inválido']);
        exit;
    }

    // Verifica se o usuário é membro do chat
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM chat_members WHERE chat_id = ? AND user_id = ?');
    $stmt->execute([$chat_id, $user_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Usuário não é membro do chat']);
        exit;
    }

    // Insere registros na tabela message_reads para mensagens não lidas
    $stmt = $pdo->prepare('
        INSERT INTO message_reads (message_id, user_id, read_at)
        SELECT m.id, ?, NOW()
        FROM messages m
        LEFT JOIN message_reads mr ON m.id = mr.message_id AND mr.user_id = ?
        WHERE m.chat_id = ? AND mr.message_id IS NULL AND m.sender_id != ?
    ');
    $stmt->execute([$user_id, $user_id, $chat_id, $user_id]);

    error_log("mark_messages_read: chat_id=$chat_id, user_id=$user_id, mensagens_marcadas_como_lidas");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Erro em mark_messages_read: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>