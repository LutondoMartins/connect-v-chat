<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['chat_id']) || !isset($_POST['message']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = (int)$_SESSION['user_id'];
    $chat_id = filter_var($_POST['chat_id'], FILTER_VALIDATE_INT);
    $message = trim($_POST['message']);
    $type = in_array($_POST['type'], ['text', 'voice']) ? $_POST['type'] : 'text';

    if (!$chat_id || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Chat ID ou mensagem inválidos']);
        exit;
    }

    // Verificar se o usuário pertence ao chat
    $stmt = $pdo->prepare('SELECT id FROM chat_members WHERE chat_id = ? AND user_id = ?');
    $stmt->execute([$chat_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    $pdo->beginTransaction();

    // Inserir mensagem
    $stmt = $pdo->prepare('
        INSERT INTO messages (chat_id, sender_id, type, content, sent_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$chat_id, $user_id, $type, $message]);
    $message_id = (int)$pdo->lastInsertId();

    // Marcar como lida pelo remetente
    $stmt = $pdo->prepare('
        INSERT INTO message_reads (message_id, user_id, read_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE read_at = NOW()
    ');
    $stmt->execute([$message_id, $user_id]);

    $pdo->commit();

    error_log("Mensagem enviada: message_id=$message_id, chat_id=$chat_id, sender_id=$user_id, type=$type, content=" . addslashes($message));
    echo json_encode(['success' => true, 'message_id' => $message_id]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erro ao enviar mensagem: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

exit;
?>