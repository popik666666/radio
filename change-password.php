<?php
$adminInput = $_POST['adminPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$adminCorrect = "p666pr";

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: admin.html');
  exit;
}

if ($adminInput === $adminCorrect) {
  if (strlen($newPassword) < 4) {
    echo '❌ Heslo musí mít alespoň 4 znaky!<br><a href="admin.html">Zpět</a>';
    exit;
  }
  file_put_contents('password.txt', $newPassword);
  echo '✅ Heslo bylo úspěšně změněno!<br><a href="admin.html">Zpět</a>';
} else {
  echo '❌ Neplatné admin heslo!<br><a href="admin.html">Zpět</a>';
}
?>
