<?php
/**
 * Script pour générer les hashés de mots de passe
 * Exécutez ce script pour obtenir les hashés à utiliser dans gsb_frais.sql
 */

$passwords = [
    'admin123' => 'admin',
    'visiteur123' => 'visiteur1',
    'comptable123' => 'comptable1'
];

echo "Hashés de mots de passe pour la base de données:\n\n";

foreach ($passwords as $password => $login) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "-- Login: $login / Mot de passe: $password\n";
    echo "Hash: $hash\n\n";
}

echo "\nCopiez ces hashés dans le fichier gsb_frais.sql\n";

