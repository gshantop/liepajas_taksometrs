<?php
require "header.php";
?>

<section class="admin-section">
    <div class="wrap">
        <div class="admin-topp">
            <div>
                <h2>Klienti</h2>
                <p class="lead">Klienti tiek pievienoti automātiski, kad viņi veic pasūtījumu.</p>
            </div>
        </div>

        <div class="admin-main card">
            <div class="table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vārds</th>
                            <th>Tālrunis</th>
                            <th>E-pasts</th>
                            <th>Reģistrēts</th>
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
        <h2>Klients</h2>
        <form id="pieteikumuForma">
            <div class="form-elements">
                <label for="vards">Vārds</label>
                <input type="text" id="vards" data-ievade="vards" minlength="2" maxlength="100" pattern="\p{L}[\p{L} '\-]*" title="Tikai burti, atstarpes, defise un apostrofs" required>

                <label for="talrunis">Tālrunis (8 cipari)</label>
                <input type="tel" id="talrunis" data-ievade="cipari" inputmode="numeric" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="25000000" title="8 cipari, bez +371" required>

                <label for="epasts">E-pasts</label>
                <input type="email" id="epasts" maxlength="100">

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
        <h3>Dzēst klientu</h3>
        <p id="deleteMessage">Vai tiešām vēlies dzēst klientu <span></span>?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn danger">Jā, dzēst</button>
            <button id="cancelDelete" class="btn ghost">Atcelt</button>
        </div>
    </div>
</div>

<script src="scripti/klienti-script.js?v=0.1"></script>
</body>
</html>