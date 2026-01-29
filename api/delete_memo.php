<?php
require '../db.php';

$account_id = 1;
$memo_id = (int)($_POST['memo_id'] ?? 0);

if ($memo_id <= 0) {
    exit('invalid memo_id');
}

// 他人のメモ削除を防ぐため account_id を必ず条件に入れる
$stmt = $pdo->prepare("
    DELETE FROM t_memo
    WHERE id = :id
      AND account_id = :account_id
");
$stmt->execute([
    ':id' => $memo_id,
    ':account_id' => $account_id,
]);

header('Location: ../index.php');
exit;
