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
        <section class="foods_container">
            <hr class="green_line">
            <h2 class="foods_Meals">食事</h2>
            <hr class="green_line">
        </section>
        <div class="foods_ex">
            <section class="foods_hero">
                <img src="/hotelmameya/assets/img/hotelmameya_inDining.png" alt="シェフが料理を作る様子">
            </section>
            <section class="foods_container">
                <hr class="green_line">
                <p class="foods_title">夕食</p>
                <p class="foods_subTitle">-dinner-</p>
                <hr class="green_line">
                <img src="/hotelmameya/assets/img/hotelmameya_foods_dinner.png">
                <br>
                <hr class="green_line">
                <p class="foods_title">朝食</p>
                <p class="foods_subTitle">-breakfast-</p>
                <hr class="green_line">
                <img src="/hotelmameya/assets/img/hotelmameya_foods_breakFast.png">
                <br>
                <hr class="green_line">
                <p class="foods_title">飲み物</p>
                <p class="foods_subTitle">-drinks-</p>
                <hr class="green_line">
                <img src="/hotelmameya/assets/img/hotelmameya_foods_drinks.png">
                <br>
            </section>
        </div>
    </main>
</body>
</html>