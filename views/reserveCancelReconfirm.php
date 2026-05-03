<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>予約キャンセル確認 | ホテルまめや</title>
<link rel="stylesheet" href="/hotelmameya/assets/css/style.css">
</head>

<body class="cancelConfirm_ex">

<div class="cancelConfirm_container">

    <h1 class="cancelConfirm_title">予約のキャンセル</h1>

    <p class="cancelConfirm_message">
        次の予約をキャンセルします。よろしいですか？
    </p>

    <div class="cancelConfirm_box">
        <div class="cancelConfirm_row">
            <span class="label">予約ID</span>
            <span class="value"><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">お名前</span>
            <span class="value"><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">部屋</span>
            <span class="value"><?= htmlspecialchars($room_name, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">プラン</span>
            <span class="value"><?= htmlspecialchars($plan_title, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">宿泊日</span>
            <span class="value"><?= htmlspecialchars($checkin_date, ENT_QUOTES, 'UTF-8') ?> 〜 <?= htmlspecialchars($checkout_date, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">人数</span>
            <span class="value"><?= htmlspecialchars($person, ENT_QUOTES, 'UTF-8') ?>名</span>
        </div>

        <div class="cancelConfirm_row">
            <span class="label">合計金額</span>
            <span class="value">¥<?= number_format($total_price) ?></span>
        </div>
    </div>

    <form action="/hotelmameya/reserve/cancel_confirm" method="post">
        <button type="submit" class="cancelConfirm_btn-cancel">予約をキャンセルする</button>
    </form>

    <a href="/hotelmameya/home/index" class="cancelConfirm_back-link">← ホームへ戻る</a>

</div>

</body>
</html>
