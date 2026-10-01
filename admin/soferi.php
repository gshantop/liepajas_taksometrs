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
                <input type="text" id="vards" maxlength="50" required>

                <label for="uzvards">Uzvārds</label>
                <input type="text" id="uzvards" maxlength="50" required>

                <label for="talrunis">Tālrunis</label>
                <input type="tel" id="talrunis" maxlength="20" placeholder="+37120000000" required>

                <label for="licences_nr">Vadītāja apliecības nr.</label>
                <input type="text" id="licences_nr" maxlength="20" required>

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