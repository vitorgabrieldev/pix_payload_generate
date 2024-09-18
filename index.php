<?php

require_once 'PixPayload.php';

<?php

require 'vendor/autoload.php'; // Certifique-se de que o autoload do Composer está configurado

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class PixController {
    public function generatePixCode($pixKey, $merchantName, $merchantCity, $amount, $txid) {
        // Sanitizar e validar entradas (ver exemplo anterior)
        $pixPayload = new PixPayload($pixKey, $merchantName, $merchantCity, $amount, $txid);
        return $pixPayload->getPayload();
    }
}

class PixPayload {
    // (mesma classe PixPayload do exemplo anterior)

    // Adicione a função que sanitiza o nome e cidade, calcula o CRC16, e gera o payload.
}

// Simulação de chamada ao controlador
try {
    $controller = new PixController();
    
    // Dados simulados (chave Pix, nome, cidade, valor, txid)
    $pixKey = 'chave@pix.com';
    $merchantName = 'Nome do Recebedor';
    $merchantCity = 'Cidade';
    $amount = 150.75; // Valor em Reais
    $txid = 'TXID123456'; // Identificador único

    // Gera o código Pix (payload)
    $pixCode = $controller->generatePixCode($pixKey, $merchantName, $merchantCity, $amount, $txid);

    // Gerar o QR Code
    $qrCode = new QrCode($pixCode);
    $qrCode->setSize(300);
    $qrCode->setMargin(10); // Margem em torno do QR Code
    $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh()); // Correção de erro mais alta

    // Escrever a imagem PNG
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Definir o cabeçalho para imagem PNG
    header('Content-Type: ' . $result->getMimeType());
    echo $result->getString(); // Exibir a imagem do QR Code

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
