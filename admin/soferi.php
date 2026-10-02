<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Šoferi</h2>
                <p class="lead">Pārvaldi vadītājus.</p>
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
                            <th>Vārds</th>
                            <th>Uzvārds</th>
                            <th>Tālrunis</th>
                            <th>Apliecība</th>
                            <th>Automašīna</th>
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
        <h2>Šoferis</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="vards">Vārds</label>
                <input type="text" id="vards" data-ievade="vards" minlength="2" maxlength="50" pattern="\p{L}[\p{L} '\-]*" title="Tikai burti, atstarpes, defise un apostrofs" required>

                <label for="uzvards">Uzvārds</label>
                <input type="text" id="uzvards" data-ievade="vards" minlength="2" maxlength="50" pattern="\p{L}[\p{L} '\-]*" title="Tikai burti, atstarpes, defise un apostrofs" required>

                <label for="talrunis">Tālrunis (8 cipari)</label>
                <input type="tel" id="talrunis" data-ievade="cipari" inputmode="numeric" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="20000000" title="8 cipari, bez +371" required>

                <label for="licences_nr">Vadītāja apliecības nr.</label>
                <input type="text" id="licences_nr" data-ievade="licence" minlength="9" maxlength="9" pattern="[A-Z]{2}[0-9]{7}" placeholder="LV1234567" title="2 burti un 7 cipari" required>

                <label for="auto_id">Automašīna</label>
                <select id="auto_id">
                    <option value="">— nav piešķirta —</option>
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
        <h3>Dzēst šoferi</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst šoferi <span></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/soferi-script.js?v=0.1"></script>
</body>
</html>