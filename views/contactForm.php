<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title="ホテルまめや">
        </title>
        <meta name="description" content="おいしい食事と豊富なアメニティ！">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/hotelmameya/assets/css/style.css">
</head>

<body>
    <header>
        <?php include(__DIR__ . '/headerMenu.php'); ?>
    </header>
    <main>
        <section>
            <hr class="green_line">
            <h5 class="contact_title">お問い合わせ</h5>
            <hr class="green_line">
            <div class="contact_ex">
                <div class="contact_container">
                    <form action="/hotelmameya/contact/contact_reconfirm" method="POST">
                        <h5>お名前</h5>
                        <h3 class="error"><?= $error['user_name'] ?? null ?><?= $error['user_name_length'] ?? null ?></h3>
                        <input type="text" name="user_name" required pattern="^.{1,100}$" value="<?= $user_name ?? $_SESSION['contact']['user_name'] ?? null ?>">
                        <h5>電話番号</h5>
                        <h3 class="error"><?= $error['user_telphone_format'] ?? null ?><?= $error['user_telphone_length'] ?? null ?></h3>
                        <input type="text" name="user_telphone" pattern="^.{1,100}$" value="<?= $user_telphone ?? $_SESSION['contact']['user_telphone'] ?? null ?>">
                        <h5>メールアドレス</h5>
                        <h3 class="error"><?= $error['email'] ?? null ?><?= $error['email_format'] ?? null ?><?= $error['email_length'] ?? null ?></h3>
                        <input type="email" name="email" required value="<?= $email ?? $_SESSION['contact']['email'] ?? null ?>">
                        <h5>予約番号（分かる方のみご入力ください）</h5>
                        <input type="text" name="reservation_id" value="<?= $reservation_id ?? $_SESSION['contact']['reservation'] ?? null ?>">
                        <h5>お問い合わせ内容</h5>
                        <h3 class="error"><?= $error['commnet_length'] ?? null ?></h3>
                        <textarea rows=5 name="comment" pattern="^.{1,1000}$" value="<?= $comment ?? $_SESSION['contact']['comment'] ?? null ?>"></textarea>
                        <button type="submit" class="submit_btn">送信する</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>