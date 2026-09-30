<?php
require "header.php";
?>
<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Tarifi</h2>
                <p class="lead">Pārvaldi tarifu cenas, automašīnas un pieejamību klientu vietnē.</p>
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
                            <th>Iekāpšana</th>
                            <th>Cena/km</th>
                            <th>Cena/min</th>
                            <th>Birka</th>
                            <th>Statuss</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tarifi"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal" id="modal-tarifs">
    <div class="modal-box">
        <div class="close-modal" data-target="#modal-tarifs">
            <i class="fa-solid fa-square-xmark"></i>
        </div>
        <h2 id="modalTitle">Tarifs</h2>
        <form id="tarifaForma">
            <div class="form-elements">
                <div class="calc">
                    <div>
                        <label for="kods">Kods</label>
                        <input type="text" id="kods" maxlength="20" placeholder="piem., eko" required>
                    </div>
                    <div>
                        <label for="nosaukums">Nosaukums</label>
                        <input type="text" id="nosaukums" maxlength="60" required>
                    </div>
                </div>

                <label for="automasina">Automašīna</label>
                <input type="text" id="automasina" maxlength="100" required>

                <div class="calc">
                    <div>
                        <label for="vietu_skaits">Vietu skaits</label>
                        <input type="number" id="vietu_skaits" min="1" max="20" required>
                    </div>
                    <div>
                        <label for="iekrausanas_maksa">Iekāpšanas maksa (€)</label>
                        <input type="number" id="iekrausanas_maksa" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label for="cena_km">Cena par km (€)</label>
                        <input type="number" id="cena_km" min="0" step="0.01" required>
                    </div>
                    <div>
                        <label for="cena_min">Cena par min (€)</label>
                        <input type="number" id="cena_min" min="0" step="0.01" required>
                    </div>
                </div>

                <label for="birka">Birka (nav obligāta)</label>
                <input type="text" id="birka" maxlength="30" placeholder="piem., Populārākā">

                <label for="aktivs">Statuss</label>
                <select id="aktivs">
                    <option value="1">Aktīvs — redzams klientu vietnē</option>
                    <option value="0">Neaktīvs — paslēpts</option>
                </select>

                <input type="hidden" id="tarifs_id">
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
        <h3>Dzēst tarifu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst tarifu <span id="deleteName"></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/tarifi-script.js?v=0.1"></script>
</body>
</html>