<?php
$hash = '$2y$10$gmKix7cB5RjVJZRAshIkxO7lmuKJytp5CUc4O4qDYuCBILhBO2xPS';
$plain = 'password';

echo "<pre>";
var_dump(password_verify($plain, $hash));
echo "password_hash on this PHP:\n";
echo password_hash('password', PASSWORD_DEFAULT);
echo "</pre>";
