<?php
// コントローラーから渡ってくる主な変数（コメントだけ整理）
//
// ■ 旧予約表示用
// $old_id, $old_room_id, $old_room_name, $old_comment,
// $old_checkin_date, $old_checkout_date, $old_total_price,
// $old_plan, $old_plan_title, $old_person
//
// ■ 新予約（変更後）表示用（初期は旧予約と同じ or セッション）
// $new_room_id, $new_room_name, $new_comment,
// $new_checkin_date, $new_checkout_date, 
// $new_plan, $new_plan_title, $new_person, $stay_nights
//
// ■ カレンダー用
// $days                : その月の日数
// $marks               : [1..$days] => '○' '△' '×' など
// $prices              : [1..$days] => 価格
// $start_weekDay       : 1日の曜日（0:日〜6:土）
// $checkin_year        : 初期表示の年
// $checkin_month       : 初期表示の月
// $maxYear, $maxMonth  : カレンダー最終年月（必要なら JS で使用）
//
// ■ セレクトボックス用
// $rooms_information_all  : 部屋一覧（room_id, room_name）
// $plansData           : プラン一覧（plan_name, plan_title, description など）
// $selectedPlanName    : 初期選択プラン名（英字）
// $selected_plan_title : 初期選択プランタイトル（日本語）
//
// ■ 見積
// $estimate            : 見積金額（初期表示用）
//
// ■ 泊数セレクト用
// $maxStayNights       : 泊数候補の配列（例: [1,2,3,...]）
// $stay_nights         : 現在選択中の泊数（htmlspecialchars済み）
?>
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
        <div class="upd-wrapper">

            <!-- =========================
       1. 旧予約情報表示ブロック
       ========================= -->
            <section class="upd-section upd-old-reservation">
                <h2 class="upd-section-title">旧予約内容</h2>

                <div class="upd-old-card">
                    <div class="upd-old-row">
                        <span class="upd-old-label">予約番号</span>
                        <span class="upd-old-value"><?= $old_id ?></span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">部屋タイプ</span>
                        <span class="upd-old-value">
                            <?= htmlspecialchars($old_room_name, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">プラン</span>
                        <span class="upd-old-value">
                            <?= htmlspecialchars($old_plan_title, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">チェックイン</span>
                        <span class="upd-old-value"><?= $old_checkin_date ?></span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">チェックアウト</span>
                        <span class="upd-old-value"><?= $old_checkout_date ?></span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">ご宿泊人数</span>
                        <span class="upd-old-value"><?= $old_person ?> 名</span>
                    </div>

                    <div class="upd-old-row">
                        <span class="upd-old-label">ご要望</span>
                        <span class="upd-old-value"><?= nl2br($old_comment) ?></span>
                    </div>

                    <div class="upd-old-row upd-old-row-total">
                        <span class="upd-old-label">ご予約金額</span>
                        <span class="upd-old-value">¥<?= number_format((int)$old_total_price) ?></span>
                    </div>
                </div>
            </section>

            <!-- =========================
       2. AJAXカレンダーブロック
       ========================= -->
            <section class="upd-section upd-calendar-section">
                <h2 class="upd-section-title">ご希望のご宿泊日をお選びください</h2>

                <!-- 部屋・プラン選択（AJAX連動） -->
                <div class="upd-calendar-controls">
                    <div class="upd-control-group">
                        <label for="upd-room-select" class="upd-control-label">部屋タイプ</label>
                        <select id="upd-room-select" class="upd-select">
                            <?php foreach ($rooms_information_all as $room_information): ?>
                                <?php
                                $roomId  = (int)$room_information['id'];
                                $roomLbl = htmlspecialchars($room_information['room_name'], ENT_QUOTES, 'UTF-8');
                                $selected = ($roomId === (int)$new_room_id) ? 'selected' : '';
                                ?>
                                <option value="<?= $roomId ?>" <?= $selected ?>><?= $roomLbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="upd-control-group">
                        <label for="upd-plan-select" class="upd-control-label">プラン</label>
                        <select id="upd-plan-select" class="upd-select">
                            <?php foreach ($plansData as $plan): ?>
                                <?php
                                $planName  = $plan['plan_name']; // 英字キー
                                $planTitle = $plan['plan_title'];
                                $valueEsc  = htmlspecialchars($planName, ENT_QUOTES, 'UTF-8');
                                $titleEsc  = htmlspecialchars($planTitle, ENT_QUOTES, 'UTF-8');
                                $selected  = ($valueEsc === $new_plan) ? 'selected' : '';
                                ?>
                                <option value="<?= $valueEsc ?>" <?= $selected ?>><?= $titleEsc ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- プラン説明 -->
                <div class="upd-plan-description" id="upd-plan-description">
                    <?php
                    $currentPlanDesc = '';
                    foreach ($plansData as $plan) {
                        if (htmlspecialchars($plan['plan_name'], ENT_QUOTES, 'UTF-8') === $new_plan) {
                            $currentPlanDesc = $plan['description'] ?? '';
                            break;
                        }
                    }
                    if ($currentPlanDesc !== ''):
                    ?>
                        <p><?= nl2br(htmlspecialchars($currentPlanDesc, ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php endif; ?>
                </div>

                <!-- 年月表示＋月送り・月戻し -->
                <div class="upd-calendar-header">
                    <button type="button" class="upd-month-btn" id="upd-prev-month">&lt;</button>
                    <div class="upd-calendar-ym">
                        <span id="upd-calendar-year"><?= (int)$checkin_year ?></span>年
                        <span id="upd-calendar-month"><?= (int)$checkin_month ?></span>月
                    </div>
                    <button type="button" class="upd-month-btn" id="upd-next-month">&gt;</button>
                </div>

                <!-- カレンダー本体 -->
                <div class="upd-calendar-wrapper" id="upd-calendar-wrapper">
                    <table class="upd-calendar-table">
                        <thead>
                            <tr>
                                <th class="sun">日</th>
                                <th>月</th>
                                <th>火</th>
                                <th>水</th>
                                <th>木</th>
                                <th>金</th>
                                <th class="sat">土</th>
                            </tr>
                        </thead>
                        <tbody id="upd-calendar-body">
                            <?php
                            $day = 1;
                            $w   = (int)$start_weekDay;

                            echo '<tr>';
                            for ($i = 0; $i < $start_weekDay; $i++) {
                                echo '<td class="empty"></td>';
                            }

                            // 初期選択チェックイン日
                            $selectedY = (int)date('Y', strtotime($new_checkin_date));
                            $selectedM = (int)date('n', strtotime($new_checkin_date));
                            $selectedD = (int)date('j', strtotime($new_checkin_date));

                            foreach ($days as $day) {
                                if ($w === 7) {
                                    echo '</tr><tr>';
                                    $w = 0;
                                }

                                $mark   = $marks[$day]   ?? '';
                                $price  = $prices[$day]  ?? null;
                                $isFull = ($mark === '×');

                                $clickableClass = $isFull ? 'full' : 'clickable';

                                $isSelected = (
                                    $selectedY === (int)$checkin_year &&
                                    $selectedM === (int)$checkin_month &&
                                    $selectedD === $day
                                );
                                $selectedClass = $isSelected ? 'selected' : '';

                                $dateStr = sprintf('%04d-%02d-%02d', $checkin_year, $checkin_month, $day);
                            ?>
                                <td
                                    class="upd-calendar-cell <?= $clickableClass ?> <?= $selectedClass ?>"
                                    data-date="<?= htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') ?>"
                                    data-mark="<?= htmlspecialchars($mark, ENT_QUOTES, 'UTF-8') ?>"
                                    data-price="<?= $price !== null ? (int)$price : '' ?>">
                                    <div class="upd-cal-day"><?= $day ?></div>
                                    <div class="upd-cal-mark"><?= htmlspecialchars($mark, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if ($price !== null): ?>
                                        <div class="upd-cal-price">¥<?= number_format((int)$price) ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php
                                $day++;
                                $w++;
                            }

                            while ($w > 0 && $w < 7) {
                                echo '<td class="empty"></td>';
                                $w++;
                            }
                            echo '</tr>';
                            ?>
                        </tbody>
                    </table>
                </div>

                <p class="upd-calendar-note">
                    ※○・△の日付をクリックすると、下の「変更内容」にチェックイン日が反映されます。
                </p>
            </section>

            <!-- =========================
       3. 変更用表示＋フォーム
       ========================= -->
            <section class="upd-section upd-change-section">
                <h2 class="upd-section-title">変更内容の確認</h2>

                <form action="/hotelmameya/reserve/update_confirm" method="post" class="upd-change-form">
                    <!-- 予約ID（必須） -->
                    <input type="hidden" name="id" value="<?= $old_id ?>">

                    <!-- hidden：部屋ID・プラン名・チェックイン日・チェックアウト日・見積価格・人数・コメント -->
                    <input type="hidden" name="room_id" id="upd-room-id-hidden"
                        value="<?= (int)$new_room_id ?>">
                    <input type="hidden" name="plan" id="upd-plan-hidden"
                        value="<?= $new_plan ?>">
                    <input type="hidden" name="checkin_date" id="upd-checkin-hidden"
                        value="<?= $new_checkin_date ?>">
                    <input type="hidden" name="checkout_date" id="upd-checkout-hidden"
                        value="<?= $new_checkout_date ?>">
                    <input type="hidden" name="total_price" id="upd-estimate-hidden"
                        value="<?= (int)$estimate ?>">
                    <input type="hidden" name="person" id="upd-person-hidden"
                        value="<?= $new_person ?>">
                    <input type="hidden" name="comment" id="upd-comment-hidden"
                        value="<?= $new_comment ?>">
                    <input type="hidden" name="stay_nights" id="upd-stay-nights-hidden"
                        value="<?= $stay_nights ?>">


                    <div class="upd-change-grid">

                        <div class="upd-change-row">
                            <span class="upd-change-label">部屋タイプ</span>
                            <span class="upd-change-value" id="upd-room-name-display">
                                <?= htmlspecialchars($new_room_name, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="upd-change-row">
                            <span class="upd-change-label">プラン</span>
                            <span class="upd-change-value" id="upd-plan-name-display">
                                <?= htmlspecialchars($new_plan_title, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="upd-change-row">
                            <span class="upd-change-label">人数</span>
                            <span class="upd-change-value">
                                <select id="upd-person-select" class="upd-select">
                                    <?php for ($i = 1; $i <= $number_OfRoom; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($i == $new_person) ? 'selected' : '' ?>>
                                            <?= $i ?>名
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </span>
                        </div>

                        <div class="upd-change-row">
                            <span class="upd-change-label">チェックイン日</span>
                            <span class="upd-change-value" id="upd-checkin-display">
                                <?= $new_checkin_date ?>
                            </span>
                        </div>

                        <div class="upd-change-row">
                            <span class="upd-change-label">宿泊日数</span>
                            <span class="upd-change-value">
                                <select name="stay_nights" id="upd-stay-nights-select" class="upd-select">
                                    <?php
                                    $currentStay = (int)$stay_nights;
                                    foreach ($maxStayNights as $night):
                                        $nightInt = (int)$night;
                                        $selected = ($nightInt === $currentStay) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $nightInt ?>" <?= $selected ?>>
                                            <?= $nightInt ?>泊
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                        </div>

                        <div class="upd-change-row">
                            <span class="upd-change-label">チェックアウト日</span>
                            <span class="upd-change-value" id="upd-checkout-display">
                                <?= $new_checkout_date ?>
                            </span>
                        </div>

                        <div class="upd-change-row upd-change-row-total">
                            <span class="upd-change-label">見積金額</span>
                            <span class="upd-change-value" id="upd-estimate-display">
                                ¥<?= number_format((int)$estimate) ?>
                            </span>
                        </div>
                    </div>

                    <div class="upd-change-buttons">
                        <a href="/hotelmameya/reserve/update_verify_form" class="upd-btn upd-btn-secondary">
                            戻る
                        </a>
                        <button type="submit" class="upd-btn upd-btn-primary">
                            変更内容を確認する
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
    <!-- JS から使う主なID
  - 部屋セレクト:      #upd-room-select
  - プランセレクト:    #upd-plan-select
  - 前月ボタン:        #upd-prev-month
  - 次月ボタン:        #upd-next-month
  - カレンダーtbody:   #upd-calendar-body
  - カレンダーセル:    .upd-calendar-cell（data-date, data-price, data-mark）
  - hidden:
      #upd-room-id-hidden
      #upd-plan-hidden
      #upd-checkin-hidden
      #upd-checkout-hidden
      #upd-estimate-hidden
      #upd-person-hidden
      #upd-comment-hidden
  - 表示用:
      #upd-room-name-display
      #upd-plan-name-display
      #upd-checkin-display
      #upd-checkout-display
      #upd-estimate-display
  - 泊数セレクト:      #upd-stay-nights-select
-->
    <script>
        // ===============================
        // 予約変更フォーム JS
        // ===============================

        // DOM取得
        const roomSelect = document.getElementById('upd-room-select');
        const planSelect = document.getElementById('upd-plan-select');
        const prevBtn = document.getElementById('upd-prev-month');
        const nextBtn = document.getElementById('upd-next-month');
        const calendarBody = document.getElementById('upd-calendar-body');

        const hiddenRoomId = document.getElementById('upd-room-id-hidden');
        const hiddenPlan = document.getElementById('upd-plan-hidden');
        const hiddenCheckin = document.getElementById('upd-checkin-hidden');
        const hiddenCheckout = document.getElementById('upd-checkout-hidden');
        const hiddenEstimate = document.getElementById('upd-estimate-hidden');
        const hiddenPerson = document.getElementById('upd-person-hidden');
        const hiddenComment = document.getElementById('upd-comment-hidden');


        const displayRoomName = document.getElementById('upd-room-name-display');
        const displayPlanName = document.getElementById('upd-plan-name-display');
        const displayCheckin = document.getElementById('upd-checkin-display');
        const displayCheckout = document.getElementById('upd-checkout-display');
        const displayEstimate = document.getElementById('upd-estimate-display');

        const staySelect = document.getElementById('upd-stay-nights-select');
        const hiddenStay = document.getElementById("upd-stay-nights-hidden");
        const personSelect = document.getElementById("upd-person-select");

        const ymYear = document.getElementById('upd-calendar-year');
        const ymMonth = document.getElementById('upd-calendar-month');


        // ===============================
        // カレンダー再生成（AJAX）
        // ===============================
        function loadCalendar() {
            const roomId = roomSelect.value;
            const plan = planSelect.value;
            const year = ymYear.textContent;
            const month = ymMonth.textContent;

            fetch(`/hotelmameya/ajax/calendar/${roomId}/${year}/${month}/null/${plan}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    // tbody を差し替え
                    rebuildCalendar(data);

                    // 新しいセルにイベント付与
                    attachCalendarClickEvents();
                })
                .catch(err => console.log("AJAX ERROR:", err));
        }

        //カレンダーリビルド関数
        function rebuildCalendar(data) {
            const tbody = document.getElementById('upd-calendar-body');
            tbody.innerHTML = "";

            const start = Number(data.start_weekDay); // 0〜6
            const daysArray = data.days; // ["1","2","3",…]
            const totalDays = daysArray.length; // 31 など
            const marks = data.marks;
            const prices = data.prices;


            let html = "<tr>"

            // 曜日カウンタ
            let w = start;

            // 月初の空白
            for (let i = 0; i < start; i++) {
                html += `<td class="empty"></td>`;
            }

            const selectedDate = hiddenCheckin.value;

            for (let i = 0; i < totalDays; i++) {

                const day = Number(daysArray[i]); // "1" → 1

                const mark = marks[day] ?? "";
                const price = prices[day] ?? "";
                const isFull = (mark === "×");

                const y = ymYear.textContent;
                const m = ymMonth.textContent.padStart(2, "0");
                const d = String(day).padStart(2, "0");

                const dateStr = `${y}-${m}-${d}`;

                const clickableClass = isFull ? "full" : "clickable";
                const selectedClass = (dateStr === selectedDate) ? "selected" : "";

                html += `
            <td class="upd-calendar-cell ${clickableClass} ${selectedClass}"
                data-date="${dateStr}"
                data-mark="${mark}"
                data-price="${price}">
                <div class="upd-cal-day">${day}</div>
                <div class="upd-cal-mark">${mark}</div>
                ${price ? `<div class="upd-cal-price">¥${Number(price).toLocaleString()}</div>` : ""}
            </td>
        `;

                w++;

                if (w === 7) {
                    html += "</tr><tr>";
                    w = 0;
                }
            }

            html += "</tr>";

            tbody.innerHTML = html;
        }


        // ===============================
        // カレンダー日付クリック処理
        // ===============================
        function attachCalendarClickEvents() {
            const cells = document.querySelectorAll('.upd-calendar-cell.clickable');

            cells.forEach(cell => {
                cell.addEventListener('click', () => {
                    // 既存の selected を外す
                    document.querySelectorAll('.upd-calendar-cell.selected')
                        .forEach(c => c.classList.remove('selected'));

                    cell.classList.add('selected');

                    const date = cell.dataset.date;

                    // hidden & 表示へ反映
                    hiddenCheckin.value = date;
                    displayCheckin.textContent = date;

                    // 泊数から checkout を再計算
                    updateCheckoutAndEstimate();
                });
            });
        }


        // ===============================
        // チェックアウト日 & 見積の再計算（AJAX）
        // ===============================
        function updateCheckoutAndEstimate() {
            const roomId = roomSelect.value;
            const plan = planSelect.value;
            const person = hiddenPerson.value;
            const checkin = hiddenCheckin.value;
            const nights = Number(staySelect.value);

            if (!checkin) return;

            // ============================
            // ① チェックアウト日を JS で計算
            // ============================
            const ci = new Date(checkin);
            ci.setDate(ci.getDate() + nights);

            const y = ci.getFullYear();
            const m = ('0' + (ci.getMonth() + 1)).slice(-2);
            const d = ('0' + ci.getDate()).slice(-2);

            const checkout = `${y}-${m}-${d}`;

            // hidden & 表示へ反映
            hiddenCheckout.value = checkout;
            displayCheckout.textContent = checkout;

            // ============================
            // ② 見積を AJAX で取得
            // ============================
            const url = `/hotelmameya/ajax/estimate` +
                `?room_id=${roomId}` +
                `&plan=${plan}` +
                `&person=${person}` +
                `&checkin_date=${checkin}` +
                `&checkout_date=${checkout}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    hiddenEstimate.value = data.estimate;
                    displayEstimate.textContent =
                        "¥" + Number(data.estimate).toLocaleString();
                })
                .catch(err => console.log("ESTIMATE ERROR:", err));
        }


        // =======================================
        // 部屋変更 → カレンダー再生成 + 見積り再計算
        // =======================================
        roomSelect.addEventListener('change', () => {
            hiddenRoomId.value = roomSelect.value;

            // 表示名も更新
            displayRoomName.textContent =
                roomSelect.options[roomSelect.selectedIndex].textContent;

            // 部屋ごとの最大人数
            const roomId = roomSelect.value;
            fetch(`/hotelmameya/ajax/maxguest/${roomId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    const maxGuest = data.maxGuest;

                    // セレクトを作り直す
                    personSelect.innerHTML = "";
                    for (let i = 1; i <= maxGuest; i++) {
                        const opt = document.createElement("option");
                        opt.value = i;
                        opt.textContent = `${i}名`;
                        personSelect.appendChild(opt);
                    }

                    // hidden も更新
                    hiddenPerson.value = personSelect.value;

                    loadCalendar();
                    updateCheckoutAndEstimate();
                });
        });


        // ===============================
        // プラン変更 → カレンダー再生成
        // ===============================
        planSelect.addEventListener('change', () => {
            hiddenPlan.value = planSelect.value;
            displayPlanName.textContent =
                planSelect.options[planSelect.selectedIndex].textContent;

            loadCalendar();
        });


        // ===============================
        // 泊数変更 → checkout & 見積再計算
        // ===============================
        staySelect.addEventListener('change', () => {
            hiddenStay.value = staySelect.value;

            updateCheckoutAndEstimate();
        });

        // ===============================
        // 人数変更 → checkout & 見積再計算
        // ===============================
        personSelect.addEventListener("change", () => {
            hiddenPerson.value = personSelect.value;

            updateCheckoutAndEstimate();
        });


        // ===============================
        // 月送り・月戻し
        // ===============================
        prevBtn.addEventListener('click', () => {
            let y = Number(ymYear.textContent);
            let m = Number(ymMonth.textContent);

            m--;
            if (m === 0) {
                y--;
                m = 12;
            }

            ymYear.textContent = y;
            ymMonth.textContent = m;

            loadCalendar();
        });

        nextBtn.addEventListener('click', () => {
            let y = Number(ymYear.textContent);
            let m = Number(ymMonth.textContent);

            m++;
            if (m === 13) {
                y++;
                m = 1;
            }

            ymYear.textContent = y;
            ymMonth.textContent = m;

            loadCalendar();
        });


        // ===============================
        // 初期ロード時：クリックイベント付与
        // ===============================
        attachCalendarClickEvents();
    </script>
</body>

</html>