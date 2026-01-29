<?php
require '../db.php';
header('Content-Type: application/json; charset=utf-8');

$account_id = 1;
$heading = trim($_POST['heading'] ?? '');

if ($heading === '') {
    echo json_encode(['error' => 'empty']);
    exit;
}

// is_active を使っていないなら、この条件行は削除
$stmt = $pdo->prepare("
    SELECT id
    FROM t_heading
    WHERE account_id = :account_id
      AND heading = :heading
    LIMIT 1
");
$stmt->execute([
    ':account_id' => $account_id,
    ':heading' => $heading,
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode([
        'id' => (int)$row['id'],
        'heading' => $heading,
        'exists' => true
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO t_heading (account_id, heading, is_active)
    VALUES (:account_id, :heading, :is_active)
");
$stmt->execute([
    ':account_id' => $account_id,
    ':heading' => $heading,
    ':is_active' => 1,
]);

echo json_encode([
    'id' => (int)$pdo->lastInsertId(),
    'heading' => $heading,
    'exists' => false
]);
exit;
