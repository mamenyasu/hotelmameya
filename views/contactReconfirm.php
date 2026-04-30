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
            <hr class="green_line">
            <h5 class="contact_title">次の内容で送信します。よろしいですか？</h5>
            <hr class="green_line">
            <div class="contact_ex">
                <div class="contact_container">
                    <form action="/hotelmameya/contact/contact_confirm">
                        <h5>お名前</h5><br>
                        <h6><?= $request['user_name'] ?></h6><br>
                        <h5>電話番号</h5><br>
                        <h6><?= $request['user_telphone'] ?><h6><br>
                        <h5>メールアドレス</h5><br>
                        <h6><?= $request['email'] ?><h6><br>
                        <h5>予約番号</h5><br>
                        <h6><?= $request['reservation_id'] ?? null ?><h6><br>
                        <h5>お問い合わせ内容</h5><br>
                        <h6><?= $request['comment'] ?><h6><br>
                        <button type="submit" class="submit_btn">送信する</button>
                    </form>
                    <form action="/hotelmameya/contact/contact_form" method="post">
                        <button type="submit" class="submit_btn">戻る</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>