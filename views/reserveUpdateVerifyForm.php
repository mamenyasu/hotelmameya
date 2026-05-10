<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約変更 | ホテルまめや</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/hotelmameya/assets/css/style.css">
</head>

<header>
    <?php include(__DIR__ . '/headerMenu.php'); ?>
</header>


<body class="updateVerify_ex">

    <div class="updateVerify_container">

        <h1 class="updateVerify_title">予約変更</h1>

        <?php if (!empty($errors)): ?>
            <div class="updateVerify_error-box">
                <?php foreach ($errors as $e): ?>
                    ・<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="updateVerify_message">
            予約ID と メールアドレス を入力してください。
        </p>

        <form action="/hotelmameya/reserve/update_form" method="post" class="updateVerify_form">

            <div class="updateVerify_group">
                <label for="id" class="updateVerify_label">予約ID（数字）</label>
                <input type="text" name="id" id="id" class="updateVerify_input"
                    value="<?= isset($id) ? htmlspecialchars($id, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <div class="updateVerify_group">
                <label for="email" class="updateVerify_label">予約時のメールアドレス</label>
                <input type="email" name="email" id="email" class="updateVerify_input"
                    value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <button type="submit" class="updateVerify_btn-submit">照会する</button>
        </form>

        <a href="/hotelmameya/home/index" class="updateVerify_back-link">← ホームへ戻る</a>

    </div>

</body>

</html>