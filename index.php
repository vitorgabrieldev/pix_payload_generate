<?php

require_once 'PixPayload.php';

$controller = new PixController();
    
$pixKey = '13770078985';
$merchantName = 'Vitor Gabriel de Oliveira';
$merchantCity = 'Londrina';
$amount = 1;
$txid = 'TXID123456';

$pixCode = $controller->generatePixCode($pixKey, $merchantName, $merchantCity, $amount, $txid);

echo $pixCode;