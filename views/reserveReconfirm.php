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

        <? header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache"); ?>

<body>

    <header>
        <?php include(__DIR__ . '/headerMenu.php'); ?>
    </header>

    <div class="reserveConfirm_container">

        <h2 class="reserveConfirm_title">最終確認</h2>
        <p class="reserveConfirm_subtitle">次の内容で送信します</p>

        <div class="reserveConfirm_box">

            <div class="reserveConfirm_row">
                <span class="label">部屋</span>
                <span class="value"><?= htmlspecialchars($room_name) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">プラン</span>
                <span class="value"><?= htmlspecialchars($plan_title) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">チェックイン日</span>
                <span class="value"><?= htmlspecialchars($checkin_date) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">チェックアウト日</span>
                <span class="value"><?= htmlspecialchars($checkout_date) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">宿泊日数</span>
                <span class="value"><?= htmlspecialchars($stay_nights) ?> 泊</span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">人数</span>
                <span class="value"><?= htmlspecialchars($person) ?> 名</span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">お名前</span>
                <span class="value"><?= htmlspecialchars($user_name) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">電話番号</span>
                <span class="value"><?= htmlspecialchars($user_telphone) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">住所</span>
                <span class="value"><?= htmlspecialchars($user_address) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">メールアドレス</span>
                <span class="value"><?= htmlspecialchars($email) ?></span>
            </div>

            <div class="reserveConfirm_row">
                <span class="label">備考</span>
                <span class="value"><?= nl2br(htmlspecialchars($comment)) ?></span>
            </div>

            <div class="reserveConfirm_price">
                合計料金：<span><?= number_format($total_price) ?> 円</span>
            </div>

        </div>

        <!-- ▼ 完了ページへ送る hidden -->
        <form action="/hotelmameya/reserve/reserve_confirm" method="POST">

            <input type="hidden" name="room_id" value="<?= $room_id ?>">
            <input type="hidden" name="plan" value="<?= $plan ?>">
            <input type="hidden" name="checkin_date" value="<?= $checkin_date ?>">
            <input type="hidden" name="checkout_date" value="<?= $checkout_date ?>">
            <input type="hidden" name="stay_nights" value="<?= $stay_nights ?>">
            <input type="hidden" name="person" value="<?= $person ?>">
            <input type="hidden" name="user_name" value="<?= htmlspecialchars($user_name) ?>">
            <input type="hidden" name="user_telphone" value="<?= htmlspecialchars($user_telphone) ?>">
            <input type="hidden" name="user_address" value="<?= htmlspecialchars($user_address) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="comment" value="<?= htmlspecialchars($comment) ?>">
            <input type="hidden" name="total_price" value="<?= $total_price ?>">

            <button type="submit" class="reserveConfirm_btn_confirm">予約する</button>
        </form>
        <!-- ▲ hidden -->

        <a href="/hotelmameya/reserve/reserve_form/<?= $room_id ?>/<?= $year ?>/<?= $month ?>/<?= $day ?>/<?= $plan ?>" class="reserveConfirm_btn_back">戻る</a>

    </div>

</body>

</html>