<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Tarifi</h2>
                <p class="lead">Pārvaldi tarifus. Automašīna tiek izvēlēta no autoparka.</p>
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
                            <th>Kods</th>
                            <th>Nosaukums</th>
                            <th>Automašīna</th>
                            <th>Vietas</th>
                            <th>Cena km</th>
                            <th>Cena min</th>
                            <th>Birka</th>
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

<!-- Tarifa pievienošanas / labošanas logs -->
<div class="modal" id="modal-admin">
    <div class="modal-box">
        <div class="close-modal" data-target="#modal-admin">
            <i class="fa-solid fa-square-xmark"></i>
        </div>
        <h2>Tarifs</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="kods">Kods</label>
                <input type="text" id="kods" data-ievade="kods" minlength="2" maxlength="10" pattern="[a-z0-9]{2,10}" title="2–10 latīņu burti vai cipari" required>

                <label for="nosaukums">Nosaukums</label>
                <input type="text" id="nosaukums" data-ievade="nosaukums" minlength="2" maxlength="50" pattern="[\p{L}\p{N}][\p{L}\p{N} .,\(\)\/\-–]*" title="Burti, cipari, atstarpes un simboli . , ( ) / -" required>

                <label for="auto_id">Automašīna (no autoparka)</label>
                <select id="auto_id" required></select>

                <label for="iekrausanas_maksa">Iekāpšanas maksa (€)</label>
                <input type="number" id="iekrausanas_maksa" min="0" max="100" step="0.01" required>

                <label for="cena_km">Cena par km (€)</label>
                <input type="number" id="cena_km" min="0.01" max="50" step="0.01" required>

                <label for="cena_min">Cena par min (€)</label>
                <input type="number" id="cena_min" min="0" max="10" step="0.01" required>

                <label for="birka">Birka</label>
                <input type="text" id="birka" data-ievade="birka" maxlength="30" pattern="[\p{L}\p{N}][\p{L}\p{N} .,\-]*" title="Burti, cipari, atstarpes un simboli . , -">

                <input type="hidden" id="piet_ID">
            </div>

            <button type="submit" class="btn a">Saglabāt</button>
        </form>
    </div>
</div>

<!-- Dzēšanas apstiprinājuma logs -->
<div class="modal" id="deleteModal">
    <div class="modal-box modal-small">
        <div class="icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Dzēst tarifu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst tarifu <span></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/tarifi-script.js?v=0.2"></script>
</body>
</html>