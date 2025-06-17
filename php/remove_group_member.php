<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['chat_id']) || !isset($_POST['member_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $chat_id = filter_var($_POST['chat_id'], FILTER_VALIDATE_INT);
    $member_id = filter_var($_POST['member_id'], FILTER_VALIDATE_INT);

    // Verificar se o usuário pertence ao grupo
    $stmt = $pdo->prepare('SELECT 1 FROM chat_members WHERE chat_id = ? AND user_id = ?');
    $stmt->execute([$chat_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Remover membro
    $stmt = $pdo->prepare('DELETE FROM chat_members WHERE chat_id = ? AND user_id = ?');
    $stmt->execute([$chat_id, $member_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>