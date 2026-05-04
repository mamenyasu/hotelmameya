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
</head>

<body>
    <header>
        <?php include(__DIR__ . '/headerMenu.php'); ?>
    </header>

    <div class="reserveForm_container">
        <h2 class="reserveForm_title">新規予約入力フォーム</h2>

        <!-- ▼ エラー表示（バリデーション or 在庫エラー） -->
        <?php if (!empty($error)): ?>
            <div class="reserveForm_error-box">
                <?php foreach ($error as $e): ?>
                    <p><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="reserveForm_error-box">
                <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>
        <!-- ▲ エラー表示 -->

        <form action="/hotelmameya/reserve/reserve_reconfirm" method="POST">

            <!-- 必須 hidden（カレンダーから渡された値） -->
            <input type="hidden" name="room_id" id="room_id" value="<?= $room_id ?>">
            <input type="hidden" name="plan" id="plan" value="<?= $plan ?>">
            <input type="hidden" name="checkin_date" id="checkin_date" value="<?= $checkin_date ?>">
            <input type="hidden" name="checkout_date" id="checkout_date" value="<?= $checkout_date ?? date('Y-m-d', strtotime("$checkin_date +1 day")) ?>">

            <div class="rserveForm_form-group">
                <label>部屋</label>
                <p><?= htmlspecialchars($room_name, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="reserveForm_form-group">
                <label>プラン</label>
                <p><?= htmlspecialchars($plan_title, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="reserveForm_form-group">
                <label>チェックイン日</label>
                <p><?= htmlspecialchars($checkin_date, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="reserveForm_form-group_mini">
                <label for="stay_nights">宿泊日数</label>
                <select name="stay_nights" id="stay_nights" onchange="calcPrice();">
                    <?php for ($i = 1; $i <= count($maxStayNights); $i++): ?>
                        <option value="<?= $i ?>" <?= ($stay_nights ?? 1) == $i ? 'selected' : '' ?>>
                            <?= $i ?> 泊
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="reserveForm_form-group">
                <label>チェックアウト日</label>
                <p id="checkout_display"><?= $checkout_date ?? date('Y-m-d', strtotime("$checkin_date +1 day")) ?></p>
            </div>


            <div class="reserveForm_form-group_mini">
                <label for="person">人数（最大 <?= $number_OfRoom ?> 名）</label>
                <select name="person" id="person" onchange="calcPrice()">
                    <?php for ($i = 1; $i <= $number_OfRoom; $i++): ?>
                        <option value="<?= $i ?>" <?= ($person ?? 1) == $i ? 'selected' : '' ?>>
                            <?= $i ?> 名
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="reserveForm_form-group">
                <label for="user_name">お名前</label>
                <input type="text" name="user_name" id="user_name" value="<?= $user_name ?? '' ?>">
            </div>

            <div class="reserveForm_form-group">
                <label for="user_telphone">電話番号</label>
                <input type="text" name="user_telphone" id="user_telphone" value="<?= $user_telphone ?? '' ?>">
            </div>

            <div class="reserveForm_form-group">
                <label for="user_address">住所</label>
                <input type="text" name="user_address" id="user_address" value="<?= $user_address ?? '' ?>">
            </div>

            <div class="reserveForm_form-group">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="<?= $email ?? '' ?>">
            </div>

            <div class="reserveForm_form-group">
                <label for="comment">備考</label>
                <textarea name="comment" id="comment" rows="3"><?= $comment ?? '' ?></textarea>
            </div>

            <div class="reserveForm_price-box">
                合計料金：<span id="total_price_display"><?= number_format($total_price ?? $estimate) ?> 円</span>
            </div>

            <input type="hidden" name="total_price" id="total_price" value="<?= $total_price ?? $estimate ?>">

            <div style="margin-top:20px;">
                <button type="submit" style="height:40px">予約内容を確認する</button>
            </div>

        </form>
        <a href="/hotelmameya/reserve/reserve_calendar/<?= $room_id ?>" class="btn_back">戻る</a>
    </div>

    <script>
        function calcCheckoutDate() {
            const checkin = document.getElementById('checkin_date').value;
            const nights = Number(document.getElementById('stay_nights').value);

            if (!checkin || nights <= 0) return;

            const date = new Date(checkin);
            date.setDate(date.getDate() + nights);

            const y = date.getFullYear();
            const m = ('0' + (date.getMonth() + 1)).slice(-2);
            const d = ('0' + date.getDate()).slice(-2);

            const checkout = `${y}-${m}-${d}`;

            document.getElementById('checkout_date').value = checkout;
            document.getElementById('checkout_display').innerText = checkout;
        }

        //見積計算用AJAX
        function fetchEstimate() {
            const room_id = document.getElementById('room_id').value;
            const plan = document.getElementById('plan').value;
            const person = document.getElementById('person').value;
            const checkin_date = document.getElementById('checkin_date').value;
            const checkout_date = document.getElementById('checkout_date').value;
            const url = `/hotelmameya/ajax/estimate?room_id=${room_id}&plan=${plan}&person=${person}&checkin_date=${checkin_date}&checkout_date=${checkout_date}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total_price').value = data.estimate;
                        document.getElementById('total_price_display').innerText = data.estimate.toLocaleString() + " 円";
                    }
                });
        }


        function calcPrice() {
            const person = Number(document.getElementById('person').value);
            const nights = Number(document.getElementById('stay_nights').value);
            //チェックアウト日を再計算
            calcCheckoutDate();
            // サーバー側の estimate を使うのでfetchEstimate() を呼ぶ
            fetchEstimate();
        }
    </script>

</body>

</html>