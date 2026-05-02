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
    <span id="currentYear" style="display:none;"><?= $year ?></span>
    <span id="currentMonth" style="display:none;"><?= $month ?></span>
    <span id="maxYear" style="display:none;"><?= $maxYear ?></span>
    <span id="maxMonth" style="display:none;"><?= $maxMonth ?></span>

    <headar>
        <?php include(__DIR__ . '/headerMenu.php'); ?>
    </headar>
    <main>
        <section>
            <div class="calendar-container">

                <div class="calendar-header">
                    <button id="prevMonth" class="nav-btn" disabled>←</button>
                    <span id="calendarTitle" class="calendar-title">
                        <?= $year ?>年 <?= $month ?>月（<?= $room_name ?>）
                    </span>

                    <div class="plan-selector">
                        <label for="plan">プラン：</label>
                        <select id="plan">
                            <?php foreach ($plansData as $plan): ?>
                                <option value="<?= $plan['plan_name'] ?>"
                                    <?= $plan['plan_name'] === $selectedPlan ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($plan['plan_title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button id="nextMonth" class="nav-btn"
                        <?= ($year == $maxYear && $month == $maxMonth) ? 'disabled' : '' ?>>
                        →
                    </button>
                </div>
                <table class="calendar-table">
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

                    <tbody>
                        <tr>

                            <?php for ($i = 0; $i < $start_weekDay; $i++): ?>
                                <td class="empty"></td>
                            <?php endfor; ?>

                            <?php foreach ($days as $day): ?>
                                <?php
                                $mark = $marks[$day];
                                $price = $prices[$day];
                                $isFull = ($mark === '×');
                                ?>


                                <td id="day-<?= $day ?>"
                                    class="day-cell <?= $isFull ? 'full' : '' ?>"
                                    data-day="<?= $day ?>"
                                    data-price="<?= $price ?>"
                                    data-mark="<?= $mark ?>">

                                    <?php $link = "/hotelmameya/reserve/reserve_form/{$room_id}/{$year}/{$month}/{$day}/{$selectedPlan}"; ?>
                                    <?php if ($isFull): ?>
                                        <div class="day-number"><?= $day ?></div>
                                        <div class="day-mark"><?= $mark ?></div>
                                        <div class="day-price"><?= number_format($price) ?>円</div>
                                    <?php else: ?>
                                        <a href="<?= $link ?>" class="day-link">
                                            <div class="day-number"><?= $day ?></div>
                                            <div class="day-mark"><?= $mark ?></div>
                                            <div class="day-price"><?= number_format($price) ?>円</div>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (($day + $start_weekDay) % 7 == 0): ?>
                        </tr>
                        <tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>



<script>
    //プラン変更
    document.getElementById('plan').addEventListener('change', function() {
        const selectedPlan = this.value; //初期表示用のselectedplanとは別物です。
        const roomId = <?= $room_id ?>;
        const year = parseInt(document.getElementById('currentYear').textContent);
        const month = parseInt(document.getElementById('currentMonth').textContent);

        fetch(`/hotelmameya/ajax/calendar/${roomId}/${year}/${month}/null/${selectedPlan}`)
            .then(response => response.json())
            .then(data => {
                console.log("AJAX DATA:", data);

                if (!data.success) return;

                updateCalendar(data.marks, data.prices, data.days);
            })
            .catch(e => console.log("ERROR:", e));
    });


    //月送り
    document.getElementById('nextMonth').addEventListener('click', () => {
        let {
            year,
            month
        } = getCurrentYearMonth();

        month++;
        if (month > 12) {
            month = 1;
            year++;
        }

        setCurrentYearMonth(year, month);
        updateCalendarTitle(year, month);
        fetchCalendar(year, month);
        updateNavButtons();
    });

    //月戻し
    document.getElementById('prevMonth').addEventListener('click', () => {
        let year = parseInt(document.getElementById('currentYear').textContent);
        let month = parseInt(document.getElementById('currentMonth').textContent);

        month--;
        if (month < 1) {
            month = 12;
            year--;
        }

        document.getElementById('currentYear').textContent = year;
        document.getElementById('currentMonth').textContent = month;

        fetchCalendar(year, month);
        updateCalendarTitle(year, month);
        updateNavButtons();
    });

    //ボタンの無効化
    function updateNavButtons() {
        const year = parseInt(document.getElementById('currentYear').textContent);
        const month = parseInt(document.getElementById('currentMonth').textContent);

        const maxYear = parseInt(document.getElementById('maxYear').textContent);
        const maxMonth = parseInt(document.getElementById('maxMonth').textContent);
        // 今日の日付を最小値とする
        const today = new Date();
        const minYear = today.getFullYear();
        const minMonth = today.getMonth() + 1;

        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');

        // ← の制御（今日より前には戻れない）
        prevBtn.disabled = (year === minYear && month === minMonth);
        if (year === minYear && month === minMonth) {
            prevBtn.disabled = true;
        } else {
            prevBtn.disabled = false;
        }

        // → の制御
        if (year === maxYear && month === maxMonth) {
            nextBtn.disabled = true;
        } else {
            nextBtn.disabled = false;
        }
    }



    // カレンダーの価格とマークを書き換える関数
    function updateCalendar(marks, prices, days) {
        days.forEach(day => {
            const cell = document.querySelector(`#day-${day}`);
            if (!cell) return;

            cell.querySelector('.day-mark').textContent = marks[day];
            cell.querySelector('.day-price').textContent = prices[day] + '円';
        });
    }


    function getCurrentYearMonth() {
        return {
            year: parseInt(document.getElementById('currentYear').textContent),
            month: parseInt(document.getElementById('currentMonth').textContent)
        };
    }

    function setCurrentYearMonth(year, month) {
        document.getElementById('currentYear').textContent = year;
        document.getElementById('currentMonth').textContent = month;
    }

    function updateCalendarTitle(year, month) {
        const title = document.getElementById('calendarTitle');
        title.textContent = `${year}年 ${month}月（<?= $room_name ?>）`;
    }

    function fetchCalendar(year, month) {
        const roomId = <?= $room_id ?>;
        const selectedPlan = document.getElementById('plan').value;

        fetch(`/hotelmameya/ajax/calendar/${roomId}/${year}/${month}/null/${selectedPlan}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;

                rebuildCalendar(data); // ← HTML を再構築
                updateCalendarTitle(year, month);
                updateNavButtons();
            })
            .catch(e => console.log("ERROR:", e));
    }


    function rebuildCalendar(data) {
        const tbody = document.querySelector('.calendar-table tbody');
        tbody.innerHTML = '';

        const start = Number(data.start_weekDay);
        const days = data.days.map(Number);
        const marks = data.marks;
        const prices = data.prices;

        const roomId = <?= $room_id ?>;
        const plan = document.getElementById('plan').value;
        const year = Number(document.getElementById('currentYear').textContent);
        const month = Number(document.getElementById('currentMonth').textContent);

        // ① PHP と同じく、最初に <tr> を出す
        let html = '<tr>';

        // ② 月初の空白セル
        for (let i = 0; i < start; i++) {
            html += `<td class="empty"></td>`;
        }

        // ③ 日付セル
        days.forEach(day => {
            const mark = marks[day];
            const price = prices[day];
            const isFull = mark === '×';

            const link = `/hotelmameya/reserve/reserve_form/${roomId}/${year}/${month}/${day}/${plan}`;
            if (isFull) {
                // 満室 → リンクなし
                html += `
                <td id="day-${day}"
                class="day-cell full"
                data-day="${day}"
                data-price="${price}"
                data-mark="${mark}">
                <div class="day-number">${day}</div>
                <div class="day-mark">${mark}</div>
                <div class="day-price">${price}円</div>
            </td>
        `;
            } else {
                // 空室 → リンクあり
                html += `
            <td id="day-${day}"
                class="day-cell ${isFull ? 'full' : ''}"
                data-day="${day}"
                data-price="${price}"
                data-mark="${mark}">
                <a href="${link}" class="day-link">
                <div class="day-number">${day}</div>
                <div class="day-mark">${mark}</div>
                <div class="day-price">${price}円</div>
                </a>
            </td>
        `;
            }

            // ④ PHP と完全一致の折り返し
            if ((day + start) % 7 === 0) {
                html += '</tr><tr>';
            }
        });

        // ⑤ 最後の行を閉じる
        html += '</tr>';

        // ⑥ tbody に反映
        tbody.innerHTML = html;
    }

</script>

</body>
</html>