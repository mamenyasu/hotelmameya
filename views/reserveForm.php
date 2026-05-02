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

        <style>
            .error-box {
                background: #ffe0e0;
                border: 1px solid #ff8080;
                padding: 10px;
                margin-bottom: 15px;
                color: #b00000;
            }

            .form-group {
                margin-bottom: 12px;
            }

            label {
                display: block;
                margin-bottom: 4px;
                font-weight: bold;
            }

            input[type="text"],
            input[type="email"],
            input[type="date"],
            textarea,
            select {
                width: 100%;
                padding: 6px;
                box-sizing: border-box;
            }

            .price-box {
                background: #faf6e8;
                padding: 10px;
                border: 1px solid #e0d8b0;
                margin-top: 10px;
                font-size: 18px;
                font-weight: bold;
            }
        </style>
        <? header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache"); ?>
</head>

<body>

    <h2>新規予約フォーム</h2>

    <!-- ▼ エラー表示（バリデーション or 在庫エラー） -->
    <?php if (!empty($error)): ?>
        <div class="error-box">
            <?php foreach ($error as $e): ?>
                <p><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="error-box">
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endif; ?>
    <!-- ▲ エラー表示 -->

    <form action="/reserve/reconfirm" method="POST">

        <!-- 必須 hidden（カレンダーから渡された値） -->
        <input type="hidden" name="room_id" id="room_id" value="<?= $room_id ?>">
        <input type="hidden" name="plan" id="plan" value="<?= $plan ?>">
        <input type="hidden" name="checkin_date" id="checkin_date" value="<?= $checkin_date ?>">
        <input type="hidden" name="checkout_date" id="checkout_date" value="<?= $checkout_date ?? date('Y-m-d', strtotime("$checkin_date +1 day")) ?>">

        <!-- JS 計算用の単価（プラン別価格を入れる） -->
        <input type="hidden" id="base_price" value="<?= $base_price ?? 0 ?>">

        <div class="form-group">
            <label>部屋</label>
            <p><?= htmlspecialchars($room_name, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="form-group">
            <label>プラン</label>
            <p><?= htmlspecialchars($plan_title, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="form-group">
            <label>チェックイン日</label>
            <p><?= htmlspecialchars($checkin_date, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="form-group">
            <label for="stay_nights">宿泊日数</label>
            <select name="stay_nights" id="stay_nights" onchange="calcPrice();">
                <?php for ($i = 1; $i <= 14; $i++): ?>
                    <option value="<?= $i ?>" <?= ($stay_nights ?? 1) == $i ? 'selected' : '' ?>>
                        <?= $i ?> 泊
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label>チェックアウト日</label>
            <p id="checkout_display"><?= $checkout_date ?? date('Y-m-d', strtotime("$checkin_date +1 day")) ?></p>
        </div>


        <div class="form-group">
            <label for="person">人数（最大 <?= $number_OfRoom ?> 名）</label>
            <select name="person" id="person" onchange="calcPrice()">
                <?php for ($i = 1; $i <= $number_OfRoom; $i++): ?>
                    <option value="<?= $i ?>" <?= ($person ?? 1) == $i ? 'selected' : '' ?>>
                        <?= $i ?> 名
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="user_name">お名前</label>
            <input type="text" name="user_name" id="user_name" value="<?= $user_name ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="user_telphone">電話番号</label>
            <input type="text" name="user_telphone" id="user_telphone" value="<?= $user_telphone ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="user_address">住所</label>
            <input type="text" name="user_address" id="user_address" value="<?= $user_address ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="<?= $email ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="comment">備考</label>
            <textarea name="comment" id="comment" rows="3"><?= $comment ?? '' ?></textarea>
        </div>

        <div class="price-box">
            合計料金：<span id="total_price_display"><?= number_format($total_price ?? $estimate) ?> 円</span>
        </div>

        <input type="hidden" name="total_price" id="total_price" value="<?= $total_price ?? $estimate ?>">

        <div style="margin-top:20px;">
            <button type="submit">予約内容を確認する</button>
        </div>

    </form>

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