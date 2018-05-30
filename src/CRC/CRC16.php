<?php
namespace Dodosss\Crc;

class CRC16
{
    private $calcType;
    private $calcTypeHash = [
        'IBM',
        'MAXIM',
        'USB',
        'MODBUS',
        'CCITT',
        'CCITT-FALSE',
        'X25',
        'XMODEM',
        'DNP'
    ];

    /**
     * @param string $calc
     */
    public function __construct($calc = 'MODBUS')
    {
        $this->calcType = in_array(strtoupper($calc), $this->calcTypeHash) ? strtoupper($calc) : 'MODBUS';
    }

    /**
     * @param $str
     * @return null|string
     */
    public function calc($str)
    {
        $result = null;
        switch ($this->calcType) {
            case 'MODBUS':
                $result = $this->crc16Modbus($str);
                break;
        }
        return $result;
    }

    /**
     * crc16 for Modbus
     * @param $str
     * @return string
     */
    private function crc16Modbus($str)
    {
        $data = pack('H*', $str);
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]);
            for ($j = 8; $j != 0; $j--) {
                if (($crc & 0x0001) != 0) {
                    $crc >>= 1;
                    $crc ^= 0xA001;
                } else $crc >>= 1;
            }
        }
        return sprintf('%04X', $crc);
    }
}