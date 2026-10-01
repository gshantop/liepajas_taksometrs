<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Tarifi</h2>
                <p class="lead">Pārvaldi tarifus.</p>
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
                            <th>Automašina</th>
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
                <input type="text" id="kods" maxlength="10" required>

                <label for="nosaukums">Nosaukums</label>
                <input type="text" id="nosaukums" maxlength="50" required>

                <label for="automasina">Automašina</label>
                <input type="text" id="automasina" maxlength="100" required>

                <label for="vietu_skaits">Vietu skaits</label>
                <input type="number" id="vietu_skaits" min="1" max="99" value="4" required>

                <label for="iekrausanas_maksa">Iekāpšanas maksa (€)</label>
                <input type="number" id="iekrausanas_maksa" min="0" max="9999.99" step="0.01" required>

                <label for="cena_km">Cena par km (€)</label>
                <input type="number" id="cena_km" min="0" max="9999.99" step="0.01" required>

                <label for="cena_min">Cena par min (€)</label>
                <input type="number" id="cena_min" min="0" max="9999.99" step="0.01" required>

                <label for="birka">Birka</label>
                <input type="text" id="birka" maxlength="30">

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

<script src="scripti/tarifi-script.js?v=0.1"></script>
</body>
</html>