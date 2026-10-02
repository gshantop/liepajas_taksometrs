<?php
require "header.php";
require_once "../database/db_config.php";

// Tikai Admin loma drīkst atvērt šo lapu
$loma_sql = $savienojums->prepare("SELECT loma FROM darbinieki WHERE lietotajvards = ? AND statuss = 'aktīvs'");
$loma_sql->bind_param("s", $_SESSION['lietotajvards_GSHT']);
$loma_sql->execute();
$esosa_loma = $loma_sql->get_result()->fetch_assoc();
$loma_sql->close();

if (!$esosa_loma || $esosa_loma['loma'] !== 'Admin') {
    echo '<section class="admin-section"><div class="wrap"><h2>Piekļuve liegta</h2><p class="lead">Šī lapa ir pieejama tikai administratoriem.</p></div></section></body></html>';
    exit;
}
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Lietotāji</h2>
                <p class="lead">Pārvaldi administratoru un moderatoru kontus.</p>
            </div>
            <a class="btn" id="new-btn" href="#">
                <i class="fa fa-plus"></i>
                Pievienot jaunu
            </a>
        </div>

        <div class="admin-main card">
            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lietotājvārds</th>
                            <th>Vārds</th>
                            <th>Uzvārds</th>
                            <th>E-pasts</th>
                            <th>Loma</th>
                            <th>Statuss</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="pieteikumi"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal" id="modal-admin">
    <div class="modal-box">
        <div class="close-modal" data-target="#modal-admin">
            <i class="fa-solid fa-square-xmark"></i>
        </div>
        <h2>Lietotājs</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="lietotajvards">Lietotājvārds</label>
                <input type="text" id="lietotajvards" data-ievade="lietotajvards" minlength="3" maxlength="30" pattern="[A-Za-z0-9._\-]{3,30}" title="3–30 simboli: latīņu burti, cipari, . _ -" required>

                <label for="vards">Vārds</label>
                <input type="text" id="vards" data-ievade="vards" minlength="2" maxlength="50" pattern="\p{L}[\p{L} '\-]*" title="Tikai burti, atstarpes, defise un apostrofs" required>

                <label for="uzvards">Uzvārds</label>
                <input type="text" id="uzvards" data-ievade="vards" minlength="2" maxlength="50" pattern="\p{L}[\p{L} '\-]*" title="Tikai burti, atstarpes, defise un apostrofs" required>

                <label for="epasts">E-pasta adrese</label>
                <input type="email" id="epasts" maxlength="100" required>

                <label for="loma">Loma</label>
                <select id="loma">
                    <option value="Admin">Administrators</option>
                    <option value="Moder">Moderators</option>
                </select>

                <label>Parole</label>
                <a class="btn ghost" id="showPassword" href="#">
                    Izveidot jaunu paroli <i class="fa-solid fa-arrow-turn-down"></i>
                </a>
                <div class="password" style="display: none;">
                    <input type="password" id="parole" class="active" minlength="6" maxlength="72" pattern="\S{6,72}" placeholder="Jaunā parole (6–72 simboli, bez atstarpēm)" title="6–72 simboli, bez atstarpēm">
                </div>
                <input type="hidden" id="piet_ID">
            </div>

            <button type="submit" class="btn a">Saglabāt</button>
        </form>
    </div>
</div>

<div class="modal" id="deleteModal">
    <div class="modal-box modal-small">
        <div class="icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Dzēst lietotāja kontu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst lietotāja <span><?= htmlspecialchars($_SESSION["lietotajvards_GSHT"]); ?></span> kontu?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/lietotaji-script.js?v=0.3"></script>
</body>
</html>