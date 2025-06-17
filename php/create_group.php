<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['name']) || !isset($_POST['members'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $members = json_decode($_POST['members'], true);

    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Nome do grupo é obrigatório']);
        exit;
    }

    if (!is_array($members) || count($members) < 2) {
        echo json_encode(['success' => false, 'message' => 'Selecione pelo menos 2 membros']);
        exit;
    }

    // Iniciar transação
    $pdo->beginTransaction();

    // Criar chat de grupo
    $stmt = $pdo->prepare('
        INSERT INTO chats (name, is_group, description, created_at)
        VALUES (?, 1, ?, NOW())
    ');
    $stmt->execute([$name, $description]);
    $chat_id = $pdo->lastInsertId();

    // Adicionar criador como membro
    $stmt = $pdo->prepare('
        INSERT INTO chat_members (chat_id, user_id, joined_at)
        VALUES (?, ?, NOW())
    ');
    $stmt->execute([$chat_id, $user_id]);

    // Adicionar membros selecionados
    foreach ($members as $member_id) {
        if ($member_id != $user_id) {
            $stmt->execute([$chat_id, $member_id]);
        }
    }

    // Confirmar transação
    $pdo->commit();

    echo json_encode(['success' => true, 'group_id' => $chat_id]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>