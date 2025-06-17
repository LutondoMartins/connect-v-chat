<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = (int)$_SESSION['user_id'];
    $other_user_id = (int)$_POST['user_id'];

    // Verificar se o usuário existe
    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ?');
    $stmt->execute([$other_user_id]);
    $other_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$other_user) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    // Verificar se já existe um chat
    $stmt = $pdo->prepare('
        SELECT c.id
        FROM chats c
        JOIN chat_members cm1 ON c.id = cm1.chat_id
        JOIN chat_members cm2 ON c.id = cm2.chat_id
        WHERE c.is_group = 0
        AND cm1.user_id = ? AND cm2.user_id = ?
    ');
    $stmt->execute([$user_id, $other_user_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($chat) {
        echo json_encode(['success' => true, 'chat_id' => (int)$chat['id']]);
        exit;
    }

    // Criar novo chat
    $pdo->beginTransaction();
    $chat_name = $other_user['username']; // Nome do chat será o username do outro usuário
    $stmt = $pdo->prepare('INSERT INTO chats (name, is_group, created_at) VALUES (?, 0, NOW())');
    $stmt->execute([$chat_name]);
    $chat_id = (int)$pdo->lastInsertId();

    // Adicionar membros
    $stmt = $pdo->prepare('INSERT INTO chat_members (chat_id, user_id, joined_at) VALUES (?, ?, NOW())');
    $stmt->execute([$chat_id, $user_id]);
    $stmt->execute([$chat_id, $other_user_id]);

    // Confirmar transação
    $pdo->commit();

    // Verificar criação
    $stmt = $pdo->prepare('SELECT id FROM chats WHERE id = ?');
    $stmt->execute([$chat_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Chat não encontrado após criação');
    }

    $stmt = $pdo->prepare('SELECT user_id FROM chat_members WHERE chat_id = ?');
    $stmt->execute([$chat_id]);
    $members = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($user_id, $members) || !in_array($other_user_id, $members)) {
        throw new Exception('Membros não associados corretamente');
    }

    error_log("Chat criado: chat_id=$chat_id, user_id=$user_id, other_user_id=$other_user_id");
    echo json_encode(['success' => true, 'chat_id' => $chat_id]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Erro ao criar chat: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao criar chat: ' . $e->getMessage()]);
}

exit;
?>