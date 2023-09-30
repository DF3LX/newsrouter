<?php

$CryptKey = "";
$HashKey = "";

if (empty($CryptKey) || empty($HashKey))
    throw new RuntimeException(
        "Verschlüsselungskeys sind leer!\n"
        . "Wie wärs mit diesen hier:\n"
        . "CryptKey: " . base64_encode(openssl_random_pseudo_bytes(32))   // Create The First Key
        . "HashKey: " . base64_encode(openssl_random_pseudo_bytes(64))  // Create The Second Key
    );
