<?php
require_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

$account_id = 1;

// POST 受け取り
$heading_id = (int)($_POST['heading_id'] ?? 0);     // ?? : 左辺がnullまたは未定義の場合に右辺の値を返す
$memo       = trim($_POST['memo'] ?? '');           // trim : 余分な空白を取り除く
$tag        = trim($_POST['tag'] ?? '');



$stmt = $pdo->prepare(
  'INSERT INTO t_memo (heading_id, memo) VALUES (?, ?)'
);
$stmt->execute([$headingId, $memo]);

echo json_encode(['status' => 'ok']);
