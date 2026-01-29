<?php
require '../../db.php';

$account_id = 1;

$headings_stmt = $pdo->prepare("
    SELECT *
    FROM t_heading
    WHERE account_id = :account_id
    ORDER BY id DESC
");

$headings_stmt->execute([':account_id' => $account_id]);

$headings = $headings_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add_memo_modal.css" type="text/css">
    <title>Document</title>
</head>
<body>
    <form action="store.php" method="post">
        <div class="setting_content_wrapper">
            <div class="setting_content_heading">Heading</div>
            <select class="data_area" id="headingSelect" name="heading_id">
                <?php foreach ($headings as $heading): ?>
                    <option value=<?= htmlspecialchars($heading['id']) ?>>
                        <?= htmlspecialchars($heading['heading']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <a class="modal_open_btn" href="#"><span></span></a>
        </div>

        <div class="setting_content_wrapper">
            <div class="setting_content_heading">Memo</div>
            <input class="data_area" type="text" name="memo">
        </div>

        <div class="setting_content_wrapper">
            <div class="setting_content_heading">Tag</div>
            <input class="data_area" type="text" name="tag">
        </div>

        <input class="memo_submit_btn" type="submit" value="Add">
    </form>

    <!-- オーバーレイ(黒い背景) -->
    <div class="overlay"></div>

    <!-- モーダルウィンドウ -->
    <div class="modal">
        <!-- モーダルウィンドウを閉じる×ボタン -->
        <div class="modal_close_btn">×</div>
        <iframe src="add_memo_modal.php" class="modal_frame"></iframe>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="../../index.js"></script>
    <script src="add_memo_modal.js"></script>
</body>
</html>