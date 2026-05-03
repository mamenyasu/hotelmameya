<?php
//3秒後にホームへリダイレクト
header("Refresh: 3; URL=/hotelmameya/home/index");
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>エラーが発生しました | ホテルまめや</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Hiragino Sans", "Yu Gothic", sans-serif;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 120px auto;
            background: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e5e0d8;
        }

        .title {
            font-size: 24px;
            text-align: center;
            margin-bottom: 25px;
            color: #5a4636;
            letter-spacing: 2px;
        }

        .message-box {
            background: #faf7f2;
            border-left: 6px solid #c87f7f; /* 落ち着いた赤茶（和の警告色） */
            padding: 20px;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }

        .redirect {
            text-align: center;
            color: #7a6a58;
            font-size: 14px;
            margin-top: 20px;
        }

        .logo {
            text-align: center;
            font-size: 18px;
            margin-top: 40px;
            color: #a38b74;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="title">エラーが発生しました</div>

    <div class="message-box">
        <strong>申し訳ございません。</strong><br>
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </div>

    <div class="redirect">
        3秒後にトップページへ移動します。
    </div>

    <div class="logo">
        ─ ホテルまめや ─
    </div>

</div>

</body>
</html>
