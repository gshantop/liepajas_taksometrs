<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Braucieni</h2>
                <p class="lead">Pārvaldi braucienus un to izmaksas.</p>
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
                            <th>Datums</th>
                            <th>Klients</th>
                            <th>Maršruts</th>
                            <th>Šoferis</th>
                            <th>Tarifs</th>
                            <th>Km</th>
                            <th>Min</th>
                            <th>Cena</th>
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
        <h2>Brauciens</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="klients_id">Klients</label>
                <select id="klients_id" required></select>

                <label for="no_adrese">No adreses</label>
                <input type="text" id="no_adrese" maxlength="150" required>

                <label for="uz_adrese">Uz adresi</label>
                <input type="text" id="uz_adrese" maxlength="150" required>

                <label for="tarifs_id">Tarifs</label>
                <select id="tarifs_id" required></select>

                <label for="sofers_id">Šoferis</label>
                <select id="sofers_id"></select>

                <label for="km">Nobraukums (km)</label>
                <input type="number" id="km" min="0" max="9999.99" step="0.01">

                <label for="minutes">Brauciena ilgums (min)</label>
                <input type="number" id="minutes" min="0" max="9999" step="1">

                <p id="cenaPreview" style="font-weight:600; margin:4px 0;"></p>

                <label for="statuss">Statuss</label>
                <select id="statuss">
                    <option value="jauns">Jauns</option>
                    <option value="procesā">Procesā</option>
                    <option value="pabeigts">Pabeigts</option>
                    <option value="atcelts">Atcelts</option>
                </select>

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
        <h3>Dzēst braucienu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst braucienu <span></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/braucieni-script.js?v=0.1"></script>
</body>
</html>