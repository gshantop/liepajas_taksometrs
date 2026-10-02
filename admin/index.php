<?php
require "header.php";
require_once "../database/db_config.php";

// Palīgfunkcijas
function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function euro($v) {
    return number_format((float) $v, 2, '.', '') . ' €';
}

function visas_rindas($rezultats) {
    $rindas = [];
    while ($rinda = $rezultats->fetch_assoc()) {
        $rindas[] = $rinda;
    }
    return $rindas;
}

// Aktīvo ierakstu skaits katrā sadaļā
$skaiti = [];
foreach (['autoparks', 'soferi', 'klienti', 'tarifi'] as $tabula) {
    $r = $savienojums->query("SELECT COUNT(*) AS skaits FROM $tabula WHERE statuss = 'aktīvs'");
    $skaiti[$tabula] = (int) $r->fetch_assoc()['skaits'];
}

// Braucienu kopējie rādītāji (ieņēmumi, vidējā cena un km tikai pabeigtajiem)
$braucieni = $savienojums->query("
    SELECT COUNT(*) AS kopa,
           COALESCE(SUM(CASE WHEN statuss = 'pabeigts' THEN cena END), 0) AS ienakumi,
           COALESCE(AVG(CASE WHEN statuss = 'pabeigts' THEN cena END), 0) AS videja_cena,
           COALESCE(SUM(CASE WHEN statuss = 'pabeigts' THEN km END), 0) AS km
    FROM braucieni
    WHERE statuss <> 'dzēsts'
")->fetch_assoc();

// Ieņēmumi pa dienām (pēdējās 7 dienas, kurās bija pabeigti braucieni)
$dienas = array_reverse(visas_rindas($savienojums->query("
    SELECT DATE(izveidots) AS diena, COALESCE(SUM(cena), 0) AS ienakumi
    FROM braucieni
    WHERE statuss = 'pabeigts'
    GROUP BY DATE(izveidots)
    ORDER BY diena DESC
    LIMIT 7
")));
$max_ienakumi = $dienas ? max(array_column($dienas, 'ienakumi')) : 0;

$pedejie = visas_rindas($savienojums->query("
    SELECT b.brauciens_id, b.no_adrese, b.uz_adrese, b.cena, b.statuss, b.izveidots, k.vards AS klients_vards
    FROM braucieni b
    JOIN klienti k ON k.klients_id = b.klients_id
    WHERE b.statuss <> 'dzēsts'
    ORDER BY b.izveidots DESC, b.brauciens_id DESC
    LIMIT 5
"));

$kartes = [
    'Automašīnas'     => $skaiti['autoparks'],
    'Šoferi'          => $skaiti['soferi'],
    'Klienti'         => $skaiti['klienti'],
    'Tarifi'          => $skaiti['tarifi'],
    'Braucieni kopā'  => $braucieni['kopa'],
    'Ieņēmumi'        => euro($braucieni['ienakumi']),
];
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Kopsavilkums</h2>
                <p class="lead">Taksometra sistēmas darbības rādītāji vienuviet.</p>
            </div>
        </div>

        <div class="ks-grid">
            <?php foreach ($kartes as $nosaukums => $vertiba): ?>
                <div class="card ks-stat">
                    <div class="ks-label"><?= h($nosaukums) ?></div>
                    <div class="ks-value"><?= h($vertiba) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card ks-panel" style="margin-bottom: 24px;">
            <h3>Ieņēmumi pa dienām (pēdējās 7 dienas ar braucieniem)</h3>
            <div class="ks-chart">
                <?php if (!$dienas): ?>
                    <div class="ks-empty">Nav datu</div>
                <?php endif; ?>
                <?php foreach ($dienas as $d):
                    $augstums = $max_ienakumi > 0 ? round($d['ienakumi'] / $max_ienakumi * 150) : 0;
                ?>
                    <div class="ks-bar-wrap">
                        <div class="ks-bar-val"><?= round($d['ienakumi']) ?> €</div>
                        <div class="ks-bar" style="height:<?= $augstums ?>px"></div>
                        <div class="ks-bar-lbl"><?= date('d.m', strtotime($d['diena'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-main card">
            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Datums</th>
                            <th>Klients</th>
                            <th>Maršruts</th>
                            <th>Cena</th>
                            <th>Statuss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedejie as $b): ?>
                            <tr>
                                <td><?= (int) $b['brauciens_id'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['izveidots'])) ?></td>
                                <td><?= h($b['klients_vards']) ?></td>
                                <td><?= h($b['no_adrese']) ?> → <?= h($b['uz_adrese']) ?></td>
                                <td><?= $b['cena'] !== null ? euro($b['cena']) : '—' ?></td>
                                <td><?= h($b['statuss']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

</body>
</html>