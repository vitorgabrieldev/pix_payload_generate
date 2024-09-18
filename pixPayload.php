<?php

// Controlador principal para lidar com a geração do código Pix
class PixController {
    public function generatePixCode($pixKey, $merchantName, $merchantCity, $amount, $txid) {
        // Sanitizar as entradas recebidas
        $pixKey = $this->sanitizeInput($pixKey);
        $merchantName = $this->sanitizeInput($merchantName);
        $merchantCity = $this->sanitizeInput($merchantCity);
        $amount = floatval($amount); // Forçar valor numérico
        $txid = $this->sanitizeInput($txid);

        // Validação básica
        if (empty($pixKey) || empty($merchantName) || empty($merchantCity) || $amount <= 0 || empty($txid)) {
            throw new Exception("Dados inválidos para gerar o código Pix.");
        }

        // Instanciar a classe PixPayload para gerar o código
        $pixPayload = new PixPayload($pixKey, $merchantName, $merchantCity, $amount, $txid);
        return $pixPayload->getPayload();
    }

    private function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input))); // Remover tags e caracteres especiais
    }
}

// Classe para gerar o código Pix (Payload)
class PixPayload {
    private $pixKey;
    private $merchantName;
    private $merchantCity;
    private $amount;
    private $txid;

    public function __construct($pixKey, $merchantName, $merchantCity, $amount, $txid) {
        $this->pixKey = $pixKey;
        $this->merchantName = $this->sanitizeText($merchantName);
        $this->merchantCity = $this->sanitizeText($merchantCity);
        $this->amount = number_format($amount, 2, '.', '');
        $this->txid = $txid;
    }

    public function getPayload() {
        $payload = $this->formatElement('00', '01');
        $payload .= $this->formatElement('26', $this->getMerchantAccountInfo());
        $payload .= $this->formatElement('52', '0000');
        $payload .= $this->formatElement('53', '986'); // Moeda BRL
        $payload .= $this->formatElement('54', $this->amount);
        $payload .= $this->formatElement('58', 'BR');
        $payload .= $this->formatElement('59', $this->merchantName);
        $payload .= $this->formatElement('60', $this->merchantCity);
        $payload .= $this->formatElement('62', $this->getAdditionalDataFieldTemplate());
        $payload .= $this->getCRC16($payload); // Gera o CRC16

        return $payload;
    }

    private function getMerchantAccountInfo() {
        return $this->formatElement('00', 'BR.GOV.BCB.PIX') .
               $this->formatElement('01', $this->pixKey);
    }

    private function getAdditionalDataFieldTemplate() {
        return $this->formatElement('05', $this->txid);
    }

    private function formatElement($id, $value) {
        $len = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $len . $value;
    }

    private function sanitizeText($text) {
        return preg_replace('/[^A-Z0-9 ]/', '', strtoupper($text));
    }

    private function getCRC16($payload) {
        $polynomial = 0x1021;
        $crc = 0xFFFF;
        $payload .= '6304';

        for ($offset = 0; $offset < strlen($payload); $offset++) {
            $crc ^= (ord($payload[$offset]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $polynomial;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        return '63' . '04' . strtoupper(dechex($crc & 0xFFFF));
    }
}