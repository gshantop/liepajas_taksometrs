<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Autoparks</h2>
                <p class="lead">Pārvaldi automašīnas.</p>
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
                            <th>Foto</th>
                            <th>Numurs</th>
                            <th>Marka</th>
                            <th>Modelis</th>
                            <th>Gads</th>
                            <th>Krāsa</th>
                            <th>Vietas</th>
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

<!-- Automašīnas pievienošanas / labošanas logs -->
<div class="modal" id="modal-admin">
    <div class="modal-box">
        <div class="close-modal" data-target="#modal-admin">
            <i class="fa-solid fa-square-xmark"></i>
        </div>
        <h2>Automašīna</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="numurs">Valsts numurs</label>
                <input type="text" id="numurs" data-ievade="numurs" maxlength="8" pattern="[A-Z0-9]{1,8}" placeholder="AB1234" title="Līdz 8 simboliem: latīņu burti un ne vairāk kā 4 cipari" required>

                <label for="marka">Marka</label>
                <input type="text" id="marka" data-ievade="marka" minlength="2" maxlength="50" pattern="[\p{L}\p{N}][\p{L}\p{N} .\-]*" title="Burti, cipari, atstarpes, punkts, defise" required>

                <label for="modelis">Modelis</label>
                <input type="text" id="modelis" data-ievade="marka" maxlength="50" pattern="[\p{L}\p{N}][\p{L}\p{N} .\-]*" title="Burti, cipari, atstarpes, punkts, defise" required>

                <label for="gads">Izlaiduma gads</label>
                <input type="number" id="gads" min="1990" max="<?= date('Y') + 1 ?>" step="1" required>

                <label for="krasa">Krāsa</label>
                <input type="text" id="krasa" data-ievade="krasa" minlength="2" maxlength="30" pattern="\p{L}[\p{L} \-]*" title="Tikai burti, atstarpes un defise">

                <label for="vietu_skaits">Vietu skaits</label>
                <input type="number" id="vietu_skaits" min="2" max="9" step="1" value="4" required>

                <label for="foto">Foto (JPG, PNG, WEBP līdz 3 MB)</label>
                <img id="fotoPreview" alt="" style="display:none; max-width:100%; max-height:160px; border-radius:8px; margin-bottom:8px;">
                <input type="file" id="foto" accept="image/jpeg,image/png,image/webp">

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
        <h3>Dzēst automašīnu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst automašīnu <span></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/autoparks-script.js?v=0.3"></script>
</body>
</html>