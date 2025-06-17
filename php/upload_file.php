<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_POST['chat_id']) || !isset($_FILES['file']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = (int)$_SESSION['user_id'];
    $chat_id = filter_var($_POST['chat_id'], FILTER_VALIDATE_INT);
    $file = $_FILES['file'];
    $type = in_array($_POST['type'], ['image', 'file', 'audio', 'video']) ? $_POST['type'] : null;

    if (!$chat_id || !$type) {
        echo json_encode(['success' => false, 'message' => 'Chat ID ou tipo inválidos']);
        exit;
    }

    // Verificar se o usuário pertence ao chat
    $stmt = $pdo->prepare('SELECT id FROM chat_members WHERE chat_id = ? AND user_id = ?');
    $stmt->execute([$chat_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Validar arquivo
    $allowed_types = [
        'image' => ['image/jpeg', 'image/png'],
        'file' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'audio' => ['audio/mpeg', 'audio/wav'],
        'video' => ['video/mp4', 'video/avi']
    ];
    if (!in_array($file['type'], $allowed_types[$type])) {
        echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
        exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) { // 10MB
        echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 10MB']);
        exit;
    }

    // Sanitizar nome do arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('file_') . '_' . time() . '.' . strtolower($extension);
    $upload_dir = 'Uploads/';
    $file_path = $upload_dir . $file_name;

    // Criar diretório se não existir
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $pdo->beginTransaction();

    // Mover arquivo para o diretório
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload']);
        exit;
    }

    // Inserir mensagem
    $file_size = formatFileSize($file['size']);
    $stmt = $pdo->prepare('
        INSERT INTO messages (chat_id, sender_id, type, file_name, file_size, sent_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$chat_id, $user_id, $type, $file_name, $file_size]);
    $message_id = (int)$pdo->lastInsertId();

    // Marcar como lida pelo remetente
    $stmt = $pdo->prepare('
        INSERT INTO message_reads (message_id, user_id, read_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE read_at = NOW()
    ');
    $stmt->execute([$message_id, $user_id]);

    $pdo->commit();

    error_log("Arquivo enviado: message_id=$message_id, chat_id=$chat_id, sender_id=$user_id, type=$type, file_name=$file_name");
    echo json_encode(['success' => true, 'message_id' => $message_id]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erro ao fazer upload: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

function formatFileSize($bytes) {
    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    }
    return number_format($bytes / 1024, 2) . ' KB';
}

exit;
?>