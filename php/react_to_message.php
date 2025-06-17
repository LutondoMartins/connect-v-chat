<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['message_id']) || !isset($_POST['reaction'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $message_id = filter_var($_POST['message_id'], FILTER_VALIDATE_INT);
    $reaction = trim($_POST['reaction']);

    // Verificar se a mensagem existe e o usuário tem acesso
    $stmt = $pdo->prepare('
        SELECT 1 
        FROM messages m
        JOIN chat_members cm ON m.chat_id = cm.chat_id
        WHERE m.id = ? AND cm.user_id = ?
    ');
    $stmt->execute([$message_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Verificar se o usuário já reagiu
    $stmt = $pdo->prepare('SELECT 1 FROM message_reactions WHERE message_id = ? AND user_id = ? AND reaction = ?');
    $stmt->execute([$message_id, $user_id, $reaction]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'message' => 'Reação já existe']);
        exit;
    }

    // Inserir reação
    $stmt = $pdo->prepare('
        INSERT INTO message_reactions (message_id, user_id, reaction, created_at)
        VALUES (?, ?, ?, NOW())
    ');
    $stmt->execute([$message_id, $user_id, $reaction]);

    // Retornar reações atualizadas
    $stmt = $pdo->prepare('SELECT reaction FROM message_reactions WHERE message_id = ?');
    $stmt->execute([$message_id]);
    $reactions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'reactions' => $reactions]);
} catch (PDOException $e) {
    error_log("Erro ao reagir à mensagem: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

exit;
?>