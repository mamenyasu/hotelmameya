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
        <section>
            <div class="hero">
                <img src="/hotelmameya/assets/img/hotelmameya_hero.png" alt="トップ画像">
            </div>
        </section>
        <div class="index_ex">
            <section class="index_room fade-in">
                <img src="/hotelmameya/assets/img/hotelmameya_index_room.png" alt="トップ部屋画像">
                <article>
                    <h2>～和風モダンの室内～</h2>
                    <br>
                    <p>木の香りに包まれた和モダンの空間で、心をほどくひとときを。<br>
                        高速Wi-Fiと動画配信サービスで、静かな夜も豊かな時間に。<br>
                        枕の種類も豊富に取り揃え、快眠をお手伝いします。</p>
                </article>
            </section>
            <section class="index_restaurant fade-in">
                <article>
                    <h2>～色とりどりの味覚を堪能～</h2>
                    <br>
                    <p>木の香りと柔らかな灯りに包まれた、和モダンのレストラン。<br>
                        季節の食材を活かした豊富なメニューと、厳選されたドリンクを取り揃えています。<br>
                        食後には、あんみつや和菓子など、心をほぐす甘味もご用意。<br>
                        ゆったりとした時間の中で、和の味わいとおもてなしをお楽しみください。</p>
                </article>
                <img src="/hotelmameya/assets/img/hotelmameya_index_restaurant.png" alt="トップレストラン画像">
            </section>
        </div>
        <footer>

        </footer>


    </main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const targets = document.querySelectorAll('.fade-in');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            }
        });
    }, { threshold: 0.2 });

    targets.forEach(target => observer.observe(target));
});
</script>



</body>

</html>