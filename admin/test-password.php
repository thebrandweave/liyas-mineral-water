<?php
// test_password.php — place at: C:\xampp\htdocs\liyas-mineral-water\test_password.php
// Purpose: verify password_verify works and show hex / length

// Known correct plaintext and hash (the one you used)
$plain = 'password';
$hash  = '$2y$10$gmKix7cB5RjVJZRAshIkxO7lmuKJytp5CUc4O4qDYuCBILhBO2xPS';

// Basic checks
echo "<h3>Local password_verify() test</h3>";
echo "<pre>";
echo "Plain (php string): "; var_dump($plain);
echo "Plain length: " . strlen($plain) . PHP_EOL;
echo "Plain hex: " . bin2hex($plain) . PHP_EOL . PHP_EOL;

echo "Hash (db): "; var_dump($hash);
echo "Hash length: " . strlen($hash) . PHP_EOL;
echo "Verify: "; var_dump(password_verify($plain, $hash));
echo "</pre>";

// Also provide a quick form to POST an arbitrary password to test what the login page sends
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['p'] ?? '';
    echo "<h3>Posted value inspection</h3><pre>";
    echo "Posted string: "; var_dump($posted);
    echo "Posted length: " . strlen($posted) . PHP_EOL;
    echo "Posted hex: " . bin2hex($posted) . PHP_EOL;
    echo "Compare to known hash verify: "; var_dump(password_verify($posted, $hash));
    echo "</pre>";
}
?>

<hr>
<form method="post">
  <label>Test a password (type what you type in admin form):</label><br>
  <input name="p" type="text" style="width:300px"><br><br>
  <button type="submit">Inspect</button>
</form>
