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
    <headar>
        <?php include(__DIR__ . '/headerMenu.php'); ?>
    </headar>
    <main>
        <section>
            <div class="calendar-container">

                <div class="calendar-header">
                    <button id="prevMonth" class="nav-btn">←</button>
                    <span class="calendar-title">
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

                                    <div class="day-number"><?= $day ?></div>
                                    <div class="day-mark"><?= $mark ?></div>
                                    <div class="day-price"><?= number_format($price) ?>円</div>
                                </td>
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
</body>


<script>
document.getElementById('plan').addEventListener('change', function() {
    const selectedPlan = this.value;
    const roomId = <?= $room_id ?>;
    const year = <?= $year ?>;
    const month = <?= $month ?>;

    fetch(`./calendar?room_id=${roomId}&plan=${selectedPlan}&year=${year}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            updateCalendar(data.marks, data.prices, data.days);
        });
});

// カレンダーの価格とマークを書き換える関数
function updateCalendar(marks, prices, days) {
    days.forEach(day => {
        const cell = document.querySelector(`#day-${day}`);
        if (!cell) return;

        cell.querySelector('.mark').textContent = marks[day];
        cell.querySelector('.price').textContent = prices[day] + '円';
    });
}
</script>
