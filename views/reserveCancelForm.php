<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約キャンセル | ホテルまめや</title>
<link rel="stylesheet" href="/hotelmameya/assets/css/style.css">
</head>

<body class="cancelForm_ex">

<div class="cancelForm_container">
    <h1 class="cancelForm_title">予約キャンセル</h1>

    <?php if (!empty($errors)): ?>
        <div class="cancelForm_error-box">
            <?php foreach ($errors as $e): ?>
                ・<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/hotelmameya/reserve/cancel_reconfirm" method="post" class="cancelForm_form">
        <div class="cancelForm_group">
            <label for="id" class="cancelForm_label">予約ID（数字）</label>
            <input type="text" name="id" id="id" class="cancelForm_input"
                   value="<?= isset($id) ? htmlspecialchars($id, ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>

        <div class="cancelForm_group">
            <label for="email" class="cancelForm_label">予約時のメールアドレス</label>
            <input type="email" name="email" id="email" class="cancelForm_input"
                   value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>

        <button type="submit" class="cancelForm_btn-submit">照会する</button>
    </form>

    <a href="/hotelmameya/home/index" class="cancelForm_back-link">← ホームへ戻る</a>
</div>

</body>
</html>
