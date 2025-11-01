<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = $_POST['password'] ?? '';
  $correct = trim(file_get_contents('password.txt'));

  if ($input === $correct) {
    $_SESSION['authenticated'] = true;
  } else {
    $error = 'Špatné heslo!';
  }
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
  ?>
  <!DOCTYPE html>
  <html lang="cs">
  <head>
    <meta charset="UTF-8">
    <title>SHOUTcast přehrávač - Přihlášení</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h2>Přihlášení k přehrávači</h2>
    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="player.php" method="post">
      <input type="password" name="password" placeholder="Zadej heslo" required>
      <button type="submit">Přihlásit</button>
    </form>
    <p><a href="index.html">Zpět na start</a></p>
  </body>
  </html>
  <?php
  exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>SHOUTcast přehrávač</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>SHOUTcast přehrávač</h2>
  <audio controls autoplay>
    <source src="http://s38.myradiostream.com:16686/" type="audio/mpeg">
    Váš prohlížeč nepodporuje přehrávač.
  </audio>
  <p><a href="logout.php">Odhlásit se</a></p>
</body>
</html>
