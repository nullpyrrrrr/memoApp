<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=memo_app;charset=utf8mb4',
        'memo_app_user',       // ← パスワードを使っているなら変更
        'password'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DB接続エラー: ' . $e->getMessage());
}