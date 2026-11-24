<?php
/** Pure PHP RSA modular exponent using OpenSSL or bcmath, no external processes. */
class TuyaRsa
{
    public static function powMod($nDec, $eDec, $dataHex, $forceOpenSsl = false)
    {
        $dataHex = preg_replace('/[^0-9a-fA-F]/', '', $dataHex);
        if (function_exists('bcpowmod') && !$forceOpenSsl) {
            $dataDec = self::hexToDec($dataHex);
            $result = bcpowmod($dataDec, $eDec, $nDec);
            $hex = self::decToHex($result);
            $keyLength = (int)ceil(strlen(self::decToBin($nDec)));
            return str_pad($hex, $keyLength * 2, '0', STR_PAD_LEFT);
        }
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('Neither bcmath nor openssl is available for Tuya RSA.');
        }
        $nBin = self::decToBin($nDec);
        $eBin = self::decToBin($eDec);
        $rsa = "\x30" . self::derLen(strlen(self::derInt($nBin)) + strlen(self::derInt($eBin)))
             . self::derInt($nBin) . self::derInt($eBin);
        $pem = "-----BEGIN RSA PUBLIC KEY-----\n" . chunk_split(base64_encode($rsa), 64, "\n") . "-----END RSA PUBLIC KEY-----\n";
        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new RuntimeException('Unable to build RSA public key from Tuya cloud response.');
        }
        $dataBin = hex2bin($dataHex);
        $keyLength = strlen($nBin);
        if (strlen($dataBin) > $keyLength) {
            throw new RuntimeException('Tuya RSA input is longer than modulus.');
        }
        $padded = str_repeat("\x00", $keyLength - strlen($dataBin)) . $dataBin;
        $encrypted = '';
        if (!openssl_public_encrypt($padded, $encrypted, $key, OPENSSL_NO_PADDING)) {
            throw new RuntimeException('Tuya RSA encryption failed.');
        }
        return bin2hex($encrypted);
    }

    private static function derLen($length)
    {
        if ($length < 128) {
            return chr($length);
        }
        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length % 256) . $bytes;
            $length = intdiv($length, 256);
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private static function derInt($bytes)
    {
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }
        return "\x02" . self::derLen(strlen($bytes)) . $bytes;
    }

    private static function decToBin($dec)
    {
        $dec = ltrim((string)$dec, '0');
        if ($dec === '') {
            return "\x00";
        }
        $bytes = array();
        while ($dec !== '') {
            $carry = 0;
            $next = '';
            $length = strlen($dec);
            for ($i = 0; $i < $length; $i++) {
                $value = $carry * 10 + (int)$dec[$i];
                $quotient = intdiv($value, 256);
                $carry = $value % 256;
                if ($quotient > 0 || $next !== '') {
                    $next .= (string)$quotient;
                }
            }
            $bytes[] = chr($carry);
            $dec = $next;
        }
        return implode('', array_reverse($bytes));
    }

    private static function hexToDec($hex)
    {
        $dec = '0';
        for ($i = 0; $i < strlen($hex); $i++) {
            $dec = bcadd(bcmul($dec, '16'), (string)hexdec($hex[$i]));
        }
        return $dec;
    }

    private static function decToHex($dec)
    {
        $hex = '';
        $dec = ltrim((string)$dec, '0');
        if ($dec === '') {
            return '0';
        }
        while ($dec !== '0') {
            $remainder = bcmod($dec, '16');
            $hex = dechex((int)$remainder) . $hex;
            $dec = bcdiv($dec, '16', 0);
        }
        return $hex;
    }
}