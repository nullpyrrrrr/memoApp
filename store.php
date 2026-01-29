<?php
require 'db.php';   // 外部ファイル読み込み
header('Content-Type: application/json; charset=utf-8');

$account_id = 1;

// POST 受け取り
$heading_id = (int)($_POST['heading_id'] ?? 0);     // ?? : 左辺がnullまたは未定義の場合に右辺の値を返す
$memo       = trim($_POST['memo'] ?? '');           // trim : 余分な空白を取り除く
$tag        = trim($_POST['tag'] ?? '');

try {
    // 簡易バリデーション
    if ($heading_id <= 0) {
        throw new Exception('heading_id が不正です');
    }
    if ($memo === '') {
        throw new Exception('memo が空です');
    }

    $pdo->beginTransaction();

    // heading_id存在チェック
    $stmt = $pdo->prepare("
        SELECT id
        FROM t_heading
        WHERE id = :heading_id
          AND account_id = :account_id
          AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([
        ':heading_id' => $heading_id,
        ':account_id' => $account_id,
    ]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('選択された heading が存在しません');
    }

    // memo 保存
    $stmt = $pdo->prepare("
        INSERT INTO t_memo (account_id, heading_id, memo, tag)
        VALUES (:account_id, :heading_id, :memo, :tag)
    ");
    $stmt->execute([
        ':account_id' => $account_id,
        ':heading_id' => $heading_id,
        ':memo'       => $memo,
        ':tag'        => $tag,
    ]);

    // tag 管理（任意）
    if ($tag !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO t_tag (account_id, tag)
            SELECT :account_id, :tag
            WHERE NOT EXISTS (
                SELECT 1 FROM t_tag
                WHERE account_id = :account_id
                  AND tag = :tag
            )
        ");
        $stmt->execute([
            ':account_id' => $account_id,
            ':tag'        => $tag,
        ]);
    }

    $pdo->commit();     // DBに反映

    echo json_encode(['success' => true]);      // JSONを返す
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
