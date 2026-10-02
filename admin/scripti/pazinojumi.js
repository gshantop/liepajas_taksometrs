// Vienota paziņojumu sistēma administrācijas lapām
(function () {
    var konteiners = document.createElement('div');
    konteiners.id = 'pazinojumi';
    konteiners.setAttribute('aria-live', 'polite');
    document.body.appendChild(konteiners);

    var ikonas = { veiksme: 'fa-circle-check', kluda: 'fa-circle-exclamation' };
    var pedejais = { atslega: '', laiks: 0 };

    function aizvert(el) {
        el.classList.remove('show');
        setTimeout(function () { el.remove(); }, 300);
    }

    // tips: 'veiksme' vai 'kluda'
    function pazinot(tips, teksts) {
        teksts = String(teksts || '');
        if (!teksts) return;

        // Neatkārto vienādu paziņojumu, ja tas parādījies pavisam nesen
        var atslega = tips + '|' + teksts;
        var tagad = Date.now();
        if (pedejais.atslega === atslega && tagad - pedejais.laiks < 1500) return;
        pedejais = { atslega: atslega, laiks: tagad };

        var el = document.createElement('div');
        el.className = 'pazinojums pazinojums--' + tips;
        el.setAttribute('role', tips === 'kluda' ? 'alert' : 'status');

        var ikona = document.createElement('i');
        ikona.className = 'fa-solid ' + ikonas[tips] + ' pazinojums__ikona';

        var txt = document.createElement('span');
        txt.className = 'pazinojums__teksts';
        txt.textContent = teksts;

        var poga = document.createElement('button');
        poga.type = 'button';
        poga.className = 'pazinojums__aizvert';
        poga.setAttribute('aria-label', 'Aizvērt');
        poga.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        poga.addEventListener('click', function () { aizvert(el); });

        el.append(ikona, txt, poga);
        konteiners.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('show'); });

        setTimeout(function () { aizvert(el); }, tips === 'kluda' ? 6000 : 4000);
    }

    window.pazinot = pazinot;
    window.veiksme = function (teksts) { pazinot('veiksme', teksts); };
    window.kluda = function (teksts) { pazinot('kluda', teksts); };

    // Visi esošie alert(...) izsaukumi tagad rāda skaistu kļūdas paziņojumu
    window.alert = function (teksts) { pazinot('kluda', teksts); };

    // Pēc katras veiksmīgas POST/PUT/DELETE darbības parāda API ziņojumu,
    // bet kļūdas gadījumā — API kļūdas tekstu
    var originalaisFetch = window.fetch.bind(window);
    window.fetch = function (resurss, opcijas) {
        var metode = ((opcijas && opcijas.method) || 'GET').toUpperCase();

        return originalaisFetch(resurss, opcijas).then(function (atbilde) {
            if (metode !== 'GET') {
                atbilde.clone().json().then(function (dati) {
                    if (atbilde.ok && dati.message) window.veiksme(dati.message);
                    else if (!atbilde.ok && dati.error) window.kluda(dati.error);
                }).catch(function () {});
            }
            return atbilde;
        });
    };
})();