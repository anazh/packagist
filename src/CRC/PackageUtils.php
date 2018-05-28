<?php
namespace Dodosss\Crc;

class PackageUtils
{
    private $crc;

    public function __construct()
    {
        $this->crc = new CRC16();
    }

    public function check($hexStr)
    {
        if ($hexStr == "" || $hexStr== "\n" || strlen($hexStr) < 40) {
            throw new \Exception("字符为空或长度不足20");
        }
        if (strlen($hexStr)%2!=0) {
            throw new \Exception("16进制字符串长度不能为单数");
        }
        if (!isHexString($hexStr)) {
            throw new \Exception("非16进制字符串");
        }
        $hexStr = str_replace(" ", "", $hexStr);
        $dataLength = substr($hexStr, 38, 2); // 40
        $signature = substr($hexStr, strlen($hexStr)-6, 4);
        $dataLengthDec = hexdec($dataLength);
        if ((54+($dataLengthDec*2))!=strlen($hexStr)) {
            throw new \Exception("数据长度错误");
        }
        $fixHexStr = substr($hexStr, 0, (strlen($hexStr)-2*3)); // 40        
        $crcResult = $this->crc->calculationResult($fixHexStr);
        $crcResultCheck = $crcResult[2].$crcResult[3].$crcResult[0].$crcResult[1];
        if(strtolower($signature)!=strtolower($crcResultCheck)){
            throw new \Exception("CRC16校验不通过");
        }
        return true;
    }

    public function parse($hexStr)
    {
        $hexStr = str_replace(" ", "", $hexStr);
        $result = null;
        $isPackage = false;
        try {
            $isPackage = $this->check($hexStr);// 数据检测
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
        if($isPackage){
            // 解析数据
            $deviceSN = substr($hexStr, 0, 32);
            $version = substr($hexStr, 32, 2);
            $connectType = substr($hexStr, 34, 2);
            $command = substr($hexStr, 36, 2);
            $dataLength = substr($hexStr, 38, 2);
            $dataLengthDec = hexdec($dataLength);
            $data = substr($hexStr, 40, $dataLengthDec*2);
            $seq = substr($hexStr, 40 + $dataLengthDec*2, 8);
            $signature = substr($hexStr, 48 + $dataLengthDec*2, 4);
            $eof = substr($hexStr, 52 + $dataLengthDec*2, 2);
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        }
        return $result;
    }

    public function reBuild($deviceSNStr, $hexStr)
    {
        $deviceSNStr = str_replace(" ", "", $deviceSNStr);
        $hexStr = str_replace(" ", "", $hexStr);
        $result = null;
        if (strlen($deviceSNStr)!=16) {
            throw new \Exception("设备号长度非16位");
        }
        $package = null;
        try {
            $package = $this->parse($hexStr);   // 格式校验，错误抛出异常         
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
        if(is_object($package)){
            $deviceSN = strToHex($deviceSNStr); // 替换设备号
            $version = $package->getVersion();
            $connectType = $package->getConnectType();
            $command = $package->getCommand();
            $dataLength = $package->getDataLength();
            $data = $package->getData();
            $seq = $package->getSeq();
            $eof = $package->getEof();
            // 计算crc16 
            $hexStrNew = $deviceSN.$version.$connectType.$command.$dataLength.$data.$seq;
            $crcResult = $this->crc->calculationResult($hexStrNew);
            $crcResultCheck = $crcResult[2].$crcResult[3].$crcResult[0].$crcResult[1];
            $signature = $crcResultCheck; // 新signature
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        }
        return $result;
    }

    public function hexScreen($hexStr, $type="1")
    {
        $str = "";
        $hexStrArr = array();
        for($i=0, $j=1; $i<strlen($hexStr); $i = $i + 2, $j++){
            $hexStrArr[] = $hexStr[$i].$hexStr[$i+1];
            $str .= '<span class="letter '.$this->getPackageScreenType($hexStr, $j).'">'.$hexStr[$i].$hexStr[$i+1].'</span>';
        }
        $hexStrScreen = $type=="1" ? implode("&nbsp;&nbsp;", $hexStrArr) : $str;
        return $hexStrScreen;
    }

    private function getPackageScreenType($hexStr, $pos)
    {
        $class = "";
        if (strlen($hexStr)>=40) {
            $dataLength = substr($hexStr, 38, 2);
            $dataLengthDec = hexdec($dataLength);
            if($pos>=0 && $pos<17){
                $class = "sec_1";
            }else if($pos==17){
                $class = "sec_2";//1
            }else if($pos==18){
                $class = "sec_3";//2
            }else if($pos==19){
                $class = "sec_4";//3
            }else if($pos==20){
                $class = "sec_5";// 4 dataLength
            }else if($pos>20 && $pos<(21 + $dataLengthDec)){
                $class = "sec_6";
            }else if($pos>(20 + $dataLengthDec) && $pos<(25 + $dataLengthDec)){
                $class = "sec_7";
            }else if($pos>(24 + $dataLengthDec) && $pos<(27 + $dataLengthDec)){
                $class = "sec_8";
            }else if($pos>(26 + $dataLengthDec) && $pos<(28 + $dataLengthDec)){
                $class = "sec_9";
            }
        }
        return $class;
    }

    private function build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof)
    {
        return new Package($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);;
    }
}