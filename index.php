<?php
session_start();
require "database/db_config.php";
?>
<!DOCTYPE html>
<html lang="lv" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Liepājas Taksometrs — taksometru pakalpojumi</title>
<script>
  (function () {
    try {
      var t = localStorage.getItem('taxi-theme');
      if (!t) t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', t);
    } catch (e) {}
  })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&family=Archivo+Narrow:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="wrap nav">
    <a class="logo" href="#top"><b>LT</b> Liepājas Taksometrs</a>
    <nav aria-label="Galvenā navigācija">
      <ul id="menu">
        <li><a class="link" href="#pakalpojumi">Pakalpojumi</a></li>
        <li><a class="link" href="#tarifi">Tarifi</a></li>
        <li><a class="link" href="#autoparks">Autoparks</a></li>
        <li><a class="link" href="#" data-order>Pasūtīt</a></li>
        <li><a class="link" href="#atsauksmes">Atsauksmes</a></li>
        <li><a class="link" href="#kontakti">Kontakti</a></li>
        <li><button class="icon-btn" id="theme" type="button" aria-label="Mainīt krāsu režīmu" title="Gaišais / tumšais režīms"><i class="fa-solid fa-moon"></i></button></li>
      </ul>
    </nav>
    <button class="icon-btn" id="burger" type="button" aria-label="Izvēlne" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
  </div>
</header>

<main id="top">
  <?php
    // Paziņojums pēc pasūtījuma / maksājuma
    $msgs = [
      'order_success'   => ['Pasūtījums pieņemts! Maksāsi braucienā skaidrā naudā.', 'ok'],
      'payment_success' => ['Maksājums saņemts, paldies! Pasūtījums pieņemts.', 'ok'],
      'payment-error'   => ['Maksājums netika apstiprināts. Mēģini vēlreiz.', 'err'],
      'payment-empty'   => ['Neizdevās pārbaudīt maksājumu.', 'err'],
    ];
    if (!empty($_SESSION['pazinojums']) && isset($msgs[$_SESSION['pazinojums']])) {
      [$text, $type] = $msgs[$_SESSION['pazinojums']];
      $color = $type === 'ok' ? '#16a34a' : '#dc2626';
      echo '<div class="wrap"><p role="status" style="margin:16px 0;padding:14px 18px;border-radius:8px;color:#fff;background:' . $color . '">'
         . htmlspecialchars($text) . '</p></div>';
    }
    unset($_SESSION['pazinojums']);
  ?>

  <div class="hero">
    <div class="wrap">
      <div>
        <h1>Brauciens bez gaidīšanas.</h1>
        <p>Taksometrs Liepājā un apkārtnē — visu diennakti. Fiksēta cena, pārbaudīti autovadītāji, tīri auto.</p>
        <a class="btn" href="#" data-order>Pasūtīt taksometru</a>
        <a class="btn ghost" href="#tarifi">Skatīt tarifus</a>
      </div>
      <div class="phone-card">
        <small>Izsaukt taksometru 24/7</small>
        <strong>+371 2000 0000</strong>
        <small>Vidējais atbraukšanas laiks: 6 minūtes</small>
      </div>
    </div>
  </div>

  <section id="pakalpojumi">
    <div class="wrap">
      <h2>Pakalpojumi</h2>
      <p class="lead">Braucieni pilsētā, uz lidostu un starp pilsētām. Pasūti pa tālruni vai iepriekš.</p>
      <div class="grid g3">
        <article class="card"><div class="ico"><i class="fa-solid fa-city"></i></div><h3>Braucieni pilsētā</h3><p>Ātri un droši jebkurā Liepājas rajonā, jebkurā diennakts laikā.</p></article>
        <article class="card"><div class="ico"><i class="fa-solid fa-plane-departure"></i></div><h3>Lidostu transfērs</h3><p>Sagaidīšana un nogādāšana uz Rīgas un Liepājas lidostu ar fiksētu cenu.</p></article>
        <article class="card"><div class="ico"><i class="fa-solid fa-road"></i></div><h3>Starppilsētu braucieni</h3><p>Ērts brauciens uz Rīgu, Ventspili, Kuldīgu un citām pilsētām.</p></article>
        <article class="card"><div class="ico"><i class="fa-solid fa-calendar-check"></i></div><h3>Iepriekšēja pasūtīšana</h3><p>Pasūti braucienu uz konkrētu laiku — auto gaidīs pie durvīm.</p></article>
      </div>
    </div>
  </section>

  <section id="tarifi" style="background: var(--surface)">
    <div class="wrap">
      <h2>Tarifi</h2>
      <p class="lead">Izvēlies auto klasi. Cenas tiek ielādētas no tarifu saraksta un atjaunojas uzreiz.</p>
      <div class="segment" id="filter" role="group" aria-label="Filtrs">
        <button type="button" data-v="all" class="on">Visi</button>
        <button type="button" data-v="4">Līdz 4 vietām</button>
        <button type="button" data-v="8">Grupām (8 vietas)</button>
      </div>
      <div class="grid g3" id="tariffs">
        <?php
          // Aktīvie tarifi no datu bāzes
          $query = "SELECT tarifs_id, nosaukums, automasina, vietu_skaits, iekrausanas_maksa, cena_km, cena_min, birka
                    FROM tarifi
                    WHERE statuss = 'aktīvs'
                    ORDER BY tarifs_id";
          $result = mysqli_query($savienojums, $query);

          if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <article class="card" data-seats="<?php echo (int) $row['vietu_skaits']; ?>">
          <?php if ($row['birka']) { ?><span class="tag"><?php echo htmlspecialchars($row['birka']); ?></span><?php } ?>
          <h3><?php echo htmlspecialchars($row['nosaukums']); ?></h3>
          <p><?php echo htmlspecialchars($row['automasina']); ?></p>
          <div class="price"><?php echo number_format($row['cena_km'], 2, ',', ''); ?> € <span>/ km</span></div>
          <ul>
            <li>Iekāpšana: <?php echo number_format($row['iekrausanas_maksa'], 2, ',', ''); ?> €</li>
            <li>Gaidīšana: <?php echo number_format($row['cena_min'], 2, ',', ''); ?> € / min</li>
            <li>Vietu skaits: <?php echo (int) $row['vietu_skaits']; ?></li>
          </ul>
          <button type="button" class="btn" style="margin-top:16px;width:100%" data-order data-tarifs="<?php echo (int) $row['tarifs_id']; ?>">Pasūtīt</button>
        </article>
        <?php
            }
          } else {
        ?>
        <p class="fleet-state">Tarifi pašlaik nav pieejami.</p>
        <?php } ?>
      </div>
    </div>
  </section>

  <section id="autoparks">
    <div class="wrap">
      <h2>Autoparks</h2>
      <p class="lead">Mūsu auto — tīri, apkopti un gatavi braucienam jebkurā diennakts laikā.</p>
      <div class="grid g3" id="fleet">
        <?php
          // Aktīvās automašīnas; klasi ņemam no tarifi, ja auto ir piesaistīts tarifam
          $query = "SELECT a.marka, a.modelis, a.gads, a.vietu_skaits, a.foto, t.nosaukums AS klase
                    FROM autoparks a
                    LEFT JOIN tarifi t ON t.auto_id = a.auto_id AND t.statuss = 'aktīvs'
                    WHERE a.statuss = 'aktīvs'
                    ORDER BY a.marka, a.modelis";
          $result = mysqli_query($savienojums, $query);

          if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <article class="card fleet-card">
          <div class="fleet-img"<?php if ($row['foto']) { ?> style="background-image:url('admin/uploads/autoparks/<?php echo rawurlencode($row['foto']); ?>')"<?php } ?>>
            <?php if (!$row['foto']) { ?><i class="fa-solid fa-car-side"></i><?php } ?>
          </div>
          <div class="fleet-body">
            <?php if ($row['klase']) { ?><span class="tag"><?php echo htmlspecialchars($row['klase']); ?></span><?php } ?>
            <h3><?php echo htmlspecialchars($row['marka'] . ' ' . $row['modelis']); ?></h3>
            <div class="fleet-meta">
              <span><i class="fa-regular fa-calendar"></i><?php echo (int) $row['gads']; ?></span>
              <span><i class="fa-solid fa-users"></i><?php echo (int) $row['vietu_skaits']; ?> vietas</span>
            </div>
          </div>
        </article>
        <?php
            }
          } else {
        ?>
        <p class="fleet-state">Autoparks pašlaik ir tukšs.</p>
        <?php } ?>
      </div>
    </div>
  </section>

  <section id="atsauksmes">
    <div class="wrap">
      <h2>Klientu atsauksmes</h2>
      <p class="lead">Ko saka tie, kas jau braukuši.</p>
      <div class="grid g3" id="reviews">
        <blockquote class="card">
          <div class="stars" aria-label="5 no 5"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
          <p style="margin-top:8px">Auto atbrauca piecās minūtēs, vadītājs laipns, cena sakrita ar aprēķinu.</p>
          <footer>— Anna K.</footer>
        </blockquote>
        <blockquote class="card">
          <div class="stars" aria-label="5 no 5"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
          <p style="margin-top:8px">Vienmēr izmantoju lidostas transfēru — laikā un bez stresa.</p>
          <footer>— Dmitrijs S.</footer>
        </blockquote>
        <blockquote class="card">
          <div class="stars" aria-label="4 no 5"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div>
          <p style="margin-top:8px">Tīrs salons un ērta pasūtīšana iepriekš. Ieteiktu draugiem.</p>
          <footer>— Ilze B.</footer>
        </blockquote>
      </div>
    </div>
  </section>

  <section id="kontakti" style="background: var(--surface)">
    <div class="wrap">
      <h2>Kontakti</h2>
      <div class="contact" style="grid-template-columns:1fr">
        <dl class="card">
          <dt>Tālrunis</dt><dd>+371 2000 0000</dd>
          <dt>E-pasts</dt><dd>info@liepajastaksometrs.lv</dd>
          <dt>Adrese</dt><dd>Graudu iela 1, Liepāja, LV-3401</dd>
          <dt>Darba laiks</dt><dd>24 stundas diennaktī, 7 dienas nedēļā</dd>
        </dl>
      </div>
    </div>
  </section>
</main>

<footer class="site">© <span id="year"></span> Liepājas Taksometrs. Visas tiesības aizsargātas.</footer>

<div class="modal" id="order-modal" role="dialog" aria-modal="true" aria-labelledby="order-title" aria-hidden="true">
  <div class="modal-box order-box">
    <button type="button" class="close-modal" id="order-close" aria-label="Aizvērt">&times;</button>
    <h2 id="order-title">Pasūtīt taksometru</h2>
    <p class="lead" style="margin-bottom:8px">Norādi maršrutu, izvēlies tarifu un apmaksas veidu — kartē vai skaidrā naudā.</p>
    <form id="form" novalidate>
        <label for="name">Vārds</label>
        <input id="name" name="vards" required autocomplete="name">
        <label for="tel">Tālrunis</label>
        <input id="tel" name="talrunis" type="tel" required autocomplete="tel" placeholder="20000000">
        <label for="o-no">No adreses</label>
        <input id="o-no" name="no" required>
        <label for="o-uz">Uz adresi</label>
        <input id="o-uz" name="uz" required>
        <label for="o-tarifs">Tarifs</label>
        <select id="o-tarifs" name="tarifs" required>
          <?php
            $r = mysqli_query($savienojums, "SELECT tarifs_id, nosaukums, iekrausanas_maksa, cena_km FROM tarifi WHERE statuss = 'aktīvs' ORDER BY tarifs_id");
            while ($r && ($t = mysqli_fetch_assoc($r))) {
              echo '<option value="' . (int) $t['tarifs_id'] . '" data-start="' . $t['iekrausanas_maksa'] . '" data-km="' . $t['cena_km'] . '">'
                 . htmlspecialchars($t['nosaukums']) . '</option>';
            }
          ?>
        </select>

        <label for="o-km">Aptuvenais attālums (km)</label>
        <input id="o-km" name="km" type="number" min="0.5" max="500" step="0.5" value="5" required>
        <p class="estimate">Aptuvenā cena: <strong id="o-sum">—</strong></p>

        <div class="pay-opts" role="radiogroup" aria-label="Apmaksas veids">
          <label><input type="radio" name="maksa" value="nauda" checked> <i class="fa-solid fa-money-bill-wave"></i> Skaidrā naudā</label>
          <label><input type="radio" name="maksa" value="karte"> <i class="fa-solid fa-credit-card"></i> Kartē</label>
        </div>

        <p class="msg" id="msg" role="status"></p>
        <button class="btn" type="submit" id="o-btn">Pasūtīt taksometru</button>
      </form>
  </div>
</div>

<script src="script.js"></script>
</body>
</html>