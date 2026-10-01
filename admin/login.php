<?php
session_start();

$pazinojums = $_SESSION["pazinojums"] ?? "";
unset($_SESSION["pazinojums"]);
?>
<!DOCTYPE html>
<html lang="lv" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Autorizācija — Pārvaldības sistēma</title>
<script>
  (function () {
    try {
      var t = localStorage.getItem('taxi-theme');
      if (!t) t = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', t);
    } catch (e) {}
  })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&family=Archivo+Narrow:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="scene">
  <div class="scene__road"></div>

  <main class="auth">
    <div class="auth__brand">
      <span class="auth__logo"><i class="fa-solid fa-taxi"></i></span>
      <div>
        <strong>Liepājas Taksometrs</strong>
      </div>
    </div>

    <h1 class="auth_h1">Ielogoties</h1>

    <form class="auth__form" method="post" action="login_function.php">
      <label for="lietotajs">Lietotājvārds</label>
      <div class="field">
        <i class="fa-solid fa-user"></i>
        <input id="lietotajs" name="lietotajs" type="text" placeholder="Lietotājvārds" required autocomplete="username">
      </div>

      <label for="pass">Parole</label>
      <div class="field">
        <i class="fa-solid fa-lock"></i>
        <input id="pass" name="parole" type="password" placeholder="••••••••" required autocomplete="current-password">
        <button type="button" class="field__toggle" id="togglePass" aria-label="Rādīt paroli">
          <i class="fa-solid fa-eye"></i>
        </button>
      </div>

      <?php if(!empty($pazinojums)): ?>
        <p class="auth__error" id="authError" role="alert" style="display:block;"><?= htmlspecialchars($pazinojums) ?></p>
      <?php endif; ?>

      <button class="btn" type="submit" name="ielogoties">
        <i class="fa-solid fa-right-to-bracket"></i> Ielogoties
      </button>
    </form>

  </main>

  <button class="icon-btn theme-toggle" id="theme" type="button" aria-label="Mainīt krāsu režīmu" title="Gaišais / tumšais režīms">
    <i class="fa-solid fa-moon"></i>
  </button>
</div>

<script>
  var root = document.documentElement, btn = document.getElementById('theme');
  function apply(t){ root.setAttribute('data-theme', t); btn.innerHTML = t === 'dark' ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>'; try{ localStorage.setItem('taxi-theme', t); } catch(e){} }
  btn.addEventListener('click', function(){ apply(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'); });
  apply(root.getAttribute('data-theme'));

  var toggle = document.getElementById('togglePass'), pass = document.getElementById('pass');
  toggle.addEventListener('click', function(){
    var show = pass.type === 'password';
    pass.type = show ? 'text' : 'password';
    toggle.innerHTML = show ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
  });
</script>
</body>
</html>