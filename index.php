<?php
require 'db.php';

$account_id = 1;

$memos_stmt = $pdo->prepare("
    SELECT *
    FROM t_memo
    WHERE account_id = :account_id
    ORDER BY id DESC
");

$headings_stmt = $pdo->prepare("
    SELECT *
    FROM t_heading
    WHERE account_id = :account_id
    ORDER BY id DESC
");

$account_stmt = $pdo->prepare("
    SELECT *
    FROM m_accounts
    WHERE id = :account_id
    ORDER BY id DESC
");

$memos_stmt->execute([':account_id' => $account_id]);
$headings_stmt->execute([':account_id' => $account_id]);
$account_stmt->execute([':account_id' => $account_id]);

$memos = $memos_stmt->fetchAll(PDO::FETCH_ASSOC);
$headings = $headings_stmt->fetchAll(PDO::FETCH_ASSOC);
$account = $account_stmt->fetch(PDO::FETCH_ASSOC);

$memos_by_heading = [];

foreach ($memos as $memo) {
    $memos_by_heading[$memo['heading_id']][] = $memo;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css" type="text/css">
    <title>Document</title>
</head>
<header>
    <div class="header_content_wrapper">
        <div class="user_data">
            <?= htmlspecialchars($account['id']) ?>
        </div>
        <div class="user_data">
            <?= htmlspecialchars($account['name']) ?>
        </div>

        <a class="modal_open_btn" href="#"><span></span></a>
    </div>
</header>

<body>
    <div class="memo_content_wrapper">
        <?php foreach ($headings as $heading): ?>
            <?php if ($heading['is_active']): ?>
                <div class="memo_heading">
                    <?= htmlspecialchars($heading['heading']) ?>
                    <div class="edit_buttons">
                        <form action="api/delete_heading.php" method="post" class="delete_heading_form" onsubmit="return confirm('この見出しを削除しますか？');">
                            <input type="hidden" name="heading_id" value="<?= (int)$heading['id'] ?>">
                            <button type="submit" class="delete_button">
                                <img src="images/delete_button.png" alt="delete">
                            </button>
                        </form>
                        <form action="" method="post" class="update_heading_form">
                            <input type="hidden" name="memo_id" value="<?= (int)$memo['id'] ?>">
                            <button type="submit" class="update_button">
                                <img src="images/update_button.png" alt="update">
                            </button>
                        </form>
                    </div>
                </div>
                <ul>
                    <?php if (!empty($memos_by_heading[$heading['id']])): ?>
                        <?php foreach ($memos_by_heading[$heading['id']] as $memo): ?>
                            <li class="memo_content">
                                <div class="memo">
                                    <?= nl2br(htmlspecialchars($memo['memo'])) ?>
                                    <div class="edit_buttons">
                                        <form action="api/delete_memo.php" method="post" class="delete_form" onsubmit="return confirm('このメモを削除しますか？');">
                                            <input type="hidden" name="memo_id" value="<?= (int)$memo['id'] ?>">
                                            <button type="submit" class="delete_button">
                                                <img src="images/delete_button.png" alt="delete">
                                            </button>
                                        </form>
                                        <form action="" method="post" class="update_form">
                                            <input type="hidden" name="memo_id" value="<?= (int)$memo['id'] ?>">
                                            <button type="submit" class="update_button">
                                                <img src="images/update_button.png" alt="update">
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <small>#<?= htmlspecialchars($memo['tag']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="memo_content empty">メモはありません</li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- オーバーレイ(黒い背景) -->
        <div class="overlay overlay_1"></div>
        <!-- モーダルウィンドウ -->
        <div class="modal modal_1">
            <!-- モーダルウィンドウを閉じる×ボタン -->
            <div class="modal_close_btn">×</div>
            <iframe id="memoIframe" src="modals/add_memo/add_memo_modal.php" class="modal_frame"></iframe>
        </div>

        <!-- オーバーレイ(黒い背景) -->
        <div class="overlay overlay_2"></div>
        <!-- モーダルウィンドウ -->
        <div class="modal modal_2">
            <!-- モーダルウィンドウを閉じる×ボタン -->
            <div class="modal_close_btn">×</div>
            <iframe src="modals/add_heading/add_heading_modal.php" class="modal_frame"></iframe>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="index.js"></script>
</body>

</html>