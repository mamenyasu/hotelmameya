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
    <main class="updConfirm_main">
        <section class="updConfirm_container">

            <h2 class="updConfirm_title">変更内容の最終確認</h2>

            <!-- 旧予約情報 -->
            <div class="updConfirm_block">
                <h3 class="updConfirm_blockTitle">現在の予約内容</h3>
                <div class="updConfirm_grid">
                    <div class="updConfirm_row"><span class="updConfirm_label">部屋タイプ</span><span class="updConfirm_value"><?= $old_room_name ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">プラン</span><span class="updConfirm_value"><?= $old_plan_title ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">チェックイン</span><span class="updConfirm_value"><?= $old_checkin_date ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">チェックアウト</span><span class="updConfirm_value"><?= $old_checkout_date ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">人数</span><span class="updConfirm_value"><?= $old_person ?>名</span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">料金</span><span class="updConfirm_value">¥<?= number_format($old_total_price) ?></span></div>
                </div>
            </div>

            <!-- 新予約情報 -->
            <div class="updConfirm_block">
                <h3 class="updConfirm_blockTitle">変更後の予約内容</h3>
                <div class="updConfirm_grid">
                    <div class="updConfirm_row"><span class="updConfirm_label">部屋タイプ</span><span class="updConfirm_value"><?= $new_room_name ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">プラン</span><span class="updConfirm_value"><?= $new_plan_title ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">チェックイン</span><span class="updConfirm_value"><?= $new_checkin_date ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">チェックアウト</span><span class="updConfirm_value"><?= $new_checkout_date ?></span></div>
                    <div class="updConfirm_row"><span class="updConfirm_label">人数</span><span class="updConfirm_value"><?= $new_person ?>名</span></div>
                    <div class="updConfirm_row updConfirm_total">
                        <span class="updConfirm_label">最終料金</span>
                        <span class="updConfirm_value">¥<?= number_format($new_total_price) ?></span>
                    </div>
                </div>
            </div>

            <!-- ボタン -->
            <form action="/hotelmameya/reserve/update" method="post" class="updConfirm_form">
                <input type="hidden" name="id" value="<?= $id ?>">

                <button type="submit" class="updConfirm_btn updConfirm_btnPrimary">
                    この内容で変更する
                </button>

                <a href="/hotelmameya/reserve/update_form" class="updConfirm_btn updConfirm_btnSecondary">
                    戻る
                </a>
            </form>

        </section>
    </main>
</body>

</html>