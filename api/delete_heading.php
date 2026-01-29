<?php
require '../db.php';

$account_id = 1;
$heading_id = (int)($_POST['heading_id'] ?? 0);

if ($heading_id <= 0) {
    exit('invalid heading_id');
}

// 他人のメモ削除を防ぐため account_id を必ず条件に入れる
$stmt = $pdo->prepare("
    UPDATE t_heading
    SET is_active = 0
    WHERE id = :id
      AND account_id = :account_id
");
$stmt->execute([
    ':id' => $heading_id,
    ':account_id' => $account_id,
]);

header('Location: ../index.php');
exit;
