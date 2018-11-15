<?php
use think\Config;

/**
 * 密码加密
 * @access public
 * @param  string $pwd 密码
 * @param  string $hash 加密字符串
 * @return strig
 */
function myCrypt($pwd,$hash)
{
    return md5(md5($pwd).$hash);
}

/**
 * 时间戳字符串相互转换
 * @access public
 * @param  int    $time 时间戳或时间字符串
 * @param  string $info 转换格式
 * @return string 没传入参数默认返回当前时间字符串
 */
function getStrTime($time=null,$info="Y-m-d H:i:s")
{
    $time = !empty($time)?$time:time();//为空默认获取当前时间戳
    if(is_numeric($time)) //时间戳转字符串
        return date($info,$time);
    else
        return  strtotime($time);
}

/**
 * 匹配日期格式 2017/01/01
 * @access public
 * @param  string $content
 * @return boolean
 */
function pregDate($content){
    $preg = '(^20[1-9]{2}-[0,1]{0,1}[1-9]{1}-[0-3]{0,1}[1-9]{1,2}|^20[1-9]{2}-{0,1}$|^20[1-9]{2}-[0,1]{0,1}[1-9]{1}-{0,1})';
    return  preg_match($preg,trim($content));
}

/**
 * 切割中文字符串
 * @param $str
 * @param int $split_length
 * @param string $charset
 * @return array|bool
 */
function mb_str_split($str,$split_length=1,$charset="UTF-8"){
    if(func_num_args()==1){
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
    if($split_length<1)return false;
    $len       =  mb_strlen($str, $charset);
    $arr       =  array();
    for($i=0;$i<$len;$i+=$split_length){
        $s     =  mb_substr($str, $i, $split_length, $charset);
        $arr[] =  $s;
    }
    return $arr;
}

//将内容进行UNICODE编码
function unicode_encode($name)
{
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len  = strlen($name);
    $str  = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c  = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {    // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}

/**
 * @param $length 生成随机字符串长度
 * @param bool $numeric 不用写
 * @return string
 */
function Func_Random($length, $numeric = FALSE) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

/**
 * [ajax_error ajax返回错误信息]
 * @param  [string]  $msg  [错误提示]
 * @param  integer   $code [错误代码，用于前端判断]
 */
function ajax_error($msg, $code = 0){
    $data = array(
        'status'     => false,
        'code'      => $code,
        'msg'      => $msg,
    );
    // return json_encode($data);
    echo json_encode($data);
    die;
}

/**
 * [ajax_success ajax返回成功]
 * @param  [array] $data [返回数据]
 */
function ajax_success($msg, $data = null){
    $data = array(
        'status'     => true,
        'msg'       => $msg,
        'data'      => $data,
    );
    echo json_encode($data);
    die;
}

/**
 * @param $passwordinput 密码
 * @param $salt 随机字符串
 * @return string 哈希密码加密
 */
function func_user_hash($passwordinput, $salt) {
    $passwordinput = "{$passwordinput}-{$salt}}";
    return md5($passwordinput);
}

/**匹配手机格式
 * @param $phone
 * @return int
 */
function pregPhone($phone){
    $preg= '/^1[3|4|5|7|8][0-9]{9}$/';
    return  preg_match($preg,trim($phone));
}

/**比例只能为数字或小数
 * @param $bili  各级比例或数量
 * @return bool
 */
function prepBiLi($bili){
    //$prep = '/^(?!0+(?:\.0+)?$)(?:[1-9]\d*|0)(?:\.\d{1,3})?$$/';
    $prep = '/^[0-9]+([.]{1}[0-9]+){0,1}$/';
    return preg_match($prep,$bili)?true:false;

}

/**
 * @param $data 钱的数据
 * @return bool 判断钱格式是否正确
 */
function fun_money($data){
    //  $price = '/^[0-9]+([.]{1}[0-9]+){0,1}$/';
    //  $price = '/^(\d|([1-9]\d+))(\.\d{1,2})?$/';
    //  return preg_match('/^0(\.(0)+)?$/' , strval($data)) === 0 ? 'not empty' : 'empty';
    if(preg_match('/^0(\.(0)+)?$/' , strval($data)) === 0){
        $price = '/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/';
        return preg_match($price,$data)?true:false;
    }else{
        return false;
    }
    $price = '/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/';

    //   /^(?!^0\.0?0$)^[0-9]?(\.[0-9]{1,2})?$|^10$/
    //   return preg_match($price,$data);
    //   return preg_match($price,$data)?true:false;
}

function Func_Money_ThreeBits($Data){
    if(preg_match('/^0(\.(0)+)?$/' , strval($Data)) === 0){
        $Price = '/^(?:0|[1-9]\d*)(?:\.\d{1,3})?$/';
        return preg_match($Price,$Data)?true:false;
    }else{
        return false;
    }
}
/**
 * @param $data 加密字符串
 * @return bool 正则判断,只能是大小写字母，0-9，特殊符号，其中的两种组成，不能有空格不能小于6位大于16位
 */
function func_encryption($data){
    if(preg_match('/^(?![a-zA-Z]+$)(?!\d+$)(?![\W_]+$)\S{6,16}$/',$data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data 验证字符串
 * @return bool 验证值不能全是空格,2-24位
 */
function Func_container($Data){
    if(preg_match('/^(?![ ]+$)[A-Za-z0-9\x7f-\xff[:punct:] ]{3,24}$/',$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $data 验证的值
 * @return bool 密码格式验证
 */
function func_pwd_verification($Data){
    if(preg_match('/^[a-zA-Z0-9\.\-\~\!\@\#\$\%\&\^\*\_]{6,16}$/i',$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data  验证的值
 * @return bool  判断字符串是不是中文加空格
 */
function Func_Chineses($Data){
    if(preg_match('/^(?![ ]+$)[\x7f-\xff ]{6,36}$/',$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data  验证的值
 * @return bool  判断姓名是不是中文2-6位不能有空格
 */
function Func_ChinesesName($Data){
    if(preg_match('/^(?![ ]+$)[\x7f-\xff]{6,18}$/',$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data 验证的字符串
 * @return bool 判断字符串是不是空格加英文
 */
function Func_English($Data){
    if(preg_match('/^(?![ ]+$)[A-Za-z ]{6,36}$/',$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data 验证字符串
 * @return bool 验证字符串是不是包含特殊字符
 */
function Func_Character($Data){
    if(preg_match("/['.·`~!@#$%^&*()-_+=]/",$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $Data 验证的值
 * @return bool 验证城市利率格式是否正确
 */
function Func_None($Data){
    if(preg_match("/^(?!^0\.0?0$)^[0-1]?(\.[0-9]{1,6})?$|^1$/",$Data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $data 要验证的值
 * @return bool 判断url链接格式是否正确
 */
function func_url($data){
    if(preg_match('/^([hH][tT]{2}[pP]:\/\/|[hH][tT]{2}[pP][sS]:\/\/)(([A-Za-z0-9-~]+)\.)+([A-Za-z0-9-~\/])+$/',$data)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $num 要验证的值
 * @return bool 验证是不是正整数
 */
function perpNum($num){
    $prep = '/^[1-9]\d*$/';
    return preg_match($prep,$num)?true:false;
}

/**
 * @param $Data  验证的值
 * @return bool  判断是不是6位的数字
 */
function Func_Number($Data){
    $Prep = '/^[0-9]{6}$/';
    return preg_match($Prep,$Data)?true:false;
}

/**
 * @param $Data 折扣值
 * @return bool 验证折扣值对不对0-10以内
 */
function Func_Discount($Data){
    $Price = '/^(?!^0\.0?0$)^[0-9]?(\.[0-9]{1,2})?$|^10$/';
    return preg_match($Price,$Data)?true:false;
}

/**
 * @param $Val  验证的值
 * @return bool 验证值是不是0-9的数字
 */
function Integers($Val){
    $Prep = '/^(0|\+?[1-9][0-9]*)$/';
    return preg_match($Prep,$Val)?true:false;
}

/**
 * @param $Val  验证的值
 * @return bool 验证值是不是正数
 */
function positive($Val){
    $Prep = '/^\d+(?=\.{0,1}\d+$|$)/';
    return preg_match($Prep,$Val)?true:false;
}

function Func_Alphanumeric($Data){
    return  preg_match("/^[0-9a-zA-Z]+$/",$Data) ? true:false;
}

/**
 * @param $Data 要判断的值
 * @return bool 验证百分比格式是否正确
 */
function Func_Percentage($Data){
    return  preg_match("/^(?!^0\.0?0$)^[0-9][0-9]?(\.[0-9]{1,2})?$|^100$/",$Data) ? true:false;
}

/**
 * @param $Data 验证的字符串
 * @return bool 判断字符串是不是中文
 */
function Func_Chinese($Data){
    return  preg_match('/^[\x7f-\xff]+$/',$Data)?true:false;
}

/**
 * @param $Name 支付密码字符串
 * @return bool 判断支付密码格式是否正确
 */
function Func_Payment($Name){
    return  preg_match('/^\d{6}$/',$Name)?true:false;
}

/**
 * @param $Name 邮箱字符串
 * @return bool 验证邮箱格式
 */
function Func_Email($Name){
    return preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/',$Name)?true:false;
}

/**
 * @param $Name 身份证号码字符串
 * 验证身份证号码格式是否正确
 */
function Func_IdCard($Name){
    return preg_match('/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i',$Name)?true:false;
}

/**
 * @param $phone   电话号码
 * @param $code    验证码内容
 * @return SimpleXMLElement 向手机用户发送验证码
 */
function sendCode($phone,$code){
    $url="http://106.ihuyi.com/webservice/sms.php?method=Submit&account=C00765782&password=9dfc2a04e6da786f7c75832aba92198e&mobile=".$phone."&content=您的验证码是：".$code."。请不要把验证码泄露给其他人。";
    $result=file_get_contents($url);
    $xml=simplexml_load_string($result);
    return $xml;
}

/**
 * @param $str_cut 截取的值
 * @param $length  截取多少位
 * @return string
 * 把字符串进行指定的位数进行截取,当截取出现乱码时,进行抛出获取完整的字符串
 */
function Func_Substr($String,$Length)
{
    if (strlen($String) > $Length)
    {
        for($i=0; $i < $Length; $i++)
            if (ord($String[$i]) > 128)    $i++;
        $String = substr($String,0,$i)."...";
    }
    return iconv("utf-8", "utf-8//ignore", $String);
}

/**
 * @param $Number    要处理的数字
 * @param $Position  要保留的小数位数
 * @return float     保留几位小数并且不四舍五入
 */
function Func_Decimal($Number, $Position){
    $Ary = explode('.', (string)$Number);
    if (strlen($Ary[1]) > $Position) {
        $Decimal = substr($Ary[1], 0, $Position);
        $Result = $Ary[0] . '.' . $Decimal;
        return (float)$Result;
    } else {
        return $Number;
    }
}

/**
 * @param $Error  录入的值
 * @param $Txt    录入的文件名
 * 录入txt文档记录
 */
function Func_Journal($Error,$Txt){

    if(!empty($Error)){
        //log日志文件
        $File = 'Yb/DailyTasks/'.date("Y-m-d",time());
        createDir($File);//创建支付记录文件
        $Txt = $File.'/'.$Txt.'.txt';

        //要写入的内容
        $AddLogStr = "\r\n\r\n".date('Y-m-d H:i:s')."记录了一条信息\r\n";//记录日志开头的标识
        //打开资源并将光标设置为末尾
        $Fp = fopen($Txt,"a+");//判断文件是否存在,不存在添加文件

        $String = iconv("UTF-8", "GB2312//IGNORE", $AddLogStr.$Error);

        //写入内容
        fwrite($Fp,$String);

        //关闭资源
        fclose($Fp);
    }

}

/**
 * @param $String   验证的值
 * @param int $Min  最小多少位
 * @param int $Max  最大多少位
 * @return bool     验证字符串位数
 */
function Func_Lengths($String,$Min=2,$Max=10){
    if (mb_strlen($String,'utf-8') < $Min || mb_strlen($String,'utf-8') > $Max) {
        return false;
    }else{
        return true;
    }
}
/**
 * @param $data 加密字符串
 * @param bool $dense true加密false解密
 * @return string Ras加密解密方法
 */
function rsa_decrypt($data,$dense=false){

    $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDFbG/E41tZhkU0A0a+6TzukEYGns55LSe9yOWo4/0a2Qy6ZoTe
ujKJAm1CLgSUPH9nYOOMi+g32wIF+FTJfNrsBSKKw66jRefMVO5G0WhQg6k/dG26
KoSEpg/fEl7CZKXS0vEzBT6U5eVDplCAt0918Ere5PNujx9GzDOFEKss+QIDAQAB
AoGBAJUM7EmCuWoapE7Dlnr2TqlyRVwkM5BrFhEEBtf+4Q2PBzwHwJMDkrm8Gk8X
vR9JmYqsLL/ktPrOn3VwalsAp6qpc56VfqwBmId0AMFct2ZuS89oQ2QZB9TBBQc4
UALgcIcnESpPb4s78Qs6G4VeI2MQXeiCyESGU/SUp5vZpP6tAkEA/07dYALOgZ53
J+QwVNlaLfPqxOsSM7OFSHu9Bpq3jxYcbz2NYsDPRDHaAdOTFb+NG8orgEDmUi6y
Nf/6Cqha/wJBAMX1aTjy6wdZEk4399wXPglUbMpb3YxTyjrwv/z7k/9aqUk5pj39
In2R6SR4oFMDWUlNz4/NaguRXDa7OJtfUAcCQQCOD+Eo0ob8IyQkg1nNbOA8H7Sr
/C21rRfl/ExzR1YKfYA2+eYZZDYwuRiY2ZTHjj3Dj9xi0joW0rsBRz1n/sQbAkAA
172d73LOsjNgv94/Qp4R/hkEd4Wm7khjHdlDY3LK2ID1/dfWVbiK3k8mx5ivIcmE
hV9H1nEIIZNJ7FweVTaLAkEA6VEkRhkgFn9s3JFME7QbBmaK75qSh42jmZ5G4OD8
rNn/qmUbl+p+AtTHL5OmJoaMJ8X+YDQRKOGqPTDhVW0GaA==
-----END RSA PRIVATE KEY-----';

    $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDFbG/E41tZhkU0A0a+6TzukEYG
ns55LSe9yOWo4/0a2Qy6ZoTeujKJAm1CLgSUPH9nYOOMi+g32wIF+FTJfNrsBSKK
w66jRefMVO5G0WhQg6k/dG26KoSEpg/fEl7CZKXS0vEzBT6U5eVDplCAt0918Ere
5PNujx9GzDOFEKss+QIDAQAB
-----END PUBLIC KEY-----';



    if($dense){
        openssl_public_encrypt($data,$encrypted,$public_key);
        $dataencryption = base64_encode($encrypted);
    }else{
        if (openssl_private_decrypt(base64_decode($data),$decrypted,$private_key)){
            $dataencryption = $decrypted;
        }else{
            $dataencryption = '';
        }
    }
    return $dataencryption;

}
function getfirstchar($s0){
    $fchar = ord($s0{0});
    if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
    $s1 = iconv("UTF-8","gb2312", $s0);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $s0){$s = $s1;}else{$s = $s0;}
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return null;
}
function make_semiangle($str){
    $arr = array('0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L', 'M' => 'M', 'N' => 'N', 'O' => 'O', 'P' => 'P', 'Q' => 'Q', 'R' => 'R', 'S' => 'S', 'T' => 'T', 'U' => 'U', 'V' => 'V', 'W' => 'W', 'X' => 'X', 'Y' => 'Y', 'Z' => 'Z', 'a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e', 'f' => 'f', 'g' => 'g', 'h' => 'h', 'i' => 'i', 'j' => 'j', 'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q', 'r' => 'r', 's' => 's', 't' => 't', 'u' => 'u', 'v' => 'v', 'w' => 'w', 'x' => 'x', 'y' => 'y', 'z' => 'z', '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '"', '”' => '"', '‘' => '\'', '’' => '\'', '｛' => '{', '｝' => '}', '《' => '<', '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-', '：' => ':', '。' => '.', '、' => ',', '，' => ',', '；' => ';', '？' => '?', '！' => '!', '…' => '...', '‖' => '|', '｜' => '|', '〃' => '"', '　' => ' ');
    return strtr($str, $arr);
}

/**
 * 中文转拼音获取首字母并大写
 * @param $zh
 * @return string
 */
function pinyin1($zh){
    switch($zh){
        case "泸":
            $zh = "卢";
            break;
        case "衢":
            $zh = "去";
            break;
        case "亳":
            $zh = "波";
            break;
        case "濮":
            $zh = "盘";
            break;
        case "漯":
            $zh = "罗";
            break;
        case "孝":
            $zh = "晓";
            break;
        case "梅":
            $zh = "没";
            break;
        case "儋":
            $zh = "站";
            break;
        case "眉":
            $zh = "没";
            break;
        case "渭":
            $zh = "为";
            break;
        case "陇":
            $zh = "弄";
            break;
        case "荃":
            $zh = "全";
            break;
    }
    $zh = make_semiangle($zh);
    $ret = "";
    $s1 = iconv("UTF-8","gb2312", $zh);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $zh){$zh = $s1;}
    for($i = 0; $i < strlen($zh); $i++){
        $s1 = substr($zh,$i,1);
        $p = ord($s1);
        if($p > 160){
            $s2 = substr($zh,$i++,2);
            $ret .= getfirstchar($s2);
        }else{
            $ret .= $s1;
        }
    }
    return $ret;
}


/**
 * 图片上传 返回$info
 * @param array $exts
 * @param string $rootPath  根路径
 * @param string $savePath  保存路径
 * @return array|bool
 */
function getImgInfo($exts=array(),$rootPath="./Uploads/",$savePath=""){
    $upload = new \Think\Upload();
    $upload->maxSize       =    0 ;
    $upload->exts          =    $exts;
    $upload->rootPath      =    $rootPath;
    $upload->savePath      =    $savePath;
    $info                  =    $upload->upload();
    return $info;
}

/**
 * 拼接图片上传真实路径
 * @param $info  图片数据
 * @param $name  图片name名
 * @param string $rootPath
 * @return string
 */
function getImgPath($info,$name,$rootPath="./Uploads/"){
    $path                  =  $rootPath.$info[$name]['savepath'];
//    $img_path              =  $path.$info[$name]['savename'].$info[$name]['ext'];
    $img_path              =  $path.$info[$name]['savename'];
    return substr($img_path,1);
}
function confirmImgSize(){
    $result = true;
    foreach ($_FILES as $k=>$v){
        if($v['size']>10574756)$result=false;
    }
    return $result;
}

/**
 * 取得文件后缀
 * @author
 */
function getImgHz($path){
    $wz  = strrpos($path,".");
    $len = strlen($path);
    $data[1] =  mb_substr($path,$wz+1,$len,'utf-8');
    $data[0] =  mb_substr($path,0,$wz,'utf-8');
    return $data;
}

/**
 * @param $file 图片信息
 * @param $url 保存图片文件路劲
 * @return string 保存图片
 * @author 肖亚子
 */
function tp($file,$url,$typer=""){
    if($typer){
        $type = strtolower($file['type']);
        $type="application/octet-stream";
        $image = array('image/jpg','image/png','image/jpeg','application/octet-stream');
        if(!in_array($type,$image)){
            return false;
        }
    }
    $basePath  = $url;
    if($type=="application/octet-stream"){
        $imgPath = $basePath;
        $baseImgPath   = getcwd()."/".$imgPath."/";
    }else{
        $yNow = date("Y");
        $mNow = date("m");
        $dNow = date("d");
        $imgPath     = $basePath. "/" . $yNow . "/" . $mNow . "/" . $dNow . "/";
        $baseImgPath = getcwd()."/".$imgPath;
    }
    $isImgPath   = createDir($baseImgPath);
    if($isImgPath){
        $fileType = strtolower(substr($file['name'],strrpos($file['name'],'.')+1)); //得到文件类型，并且都转化成小写
        $imgName  = time() . md5($file['name']).'.'.$fileType;
        if($type=="application/octet-stream"){
            $imgName = 'dztg.apk';
        }
        move_uploaded_file($file['tmp_name'], $baseImgPath . $imgName);
    }
    return $imgPath.$imgName;
}

/**
 * @param $path 创建目录文件地址
 * @return bool 创建图片保存目录
 * @author 肖亚子
 */
function createDir($path){
    $path = str_replace("./", "/", $path);
    if(is_dir($path) || @mkdir($path, 0777)){
        chmod($path,0777);
        return true;
    }
    if(!createDir(dirname($path))){
        return false;
    }
    if(@mkdir($path, 0777)){
        chmod($path,0777);
        return true;
    }
    return false;
}

/*
    *  @param $saveWhere ：想要更新主键ID数组
    *  @param $saveData    ：想要更新的ID数组所对应的数据
    *  @param $tableName  : 想要更新的表明
    *  @param $saveWhere  : 返回更新成功后的主键ID数组
    * */
function saveAll($saveWhere,&$saveData,$tableName){
    if($saveWhere==null||$tableName==null)
        return false;
    //获取更新的主键id名称
    $key = array_keys($saveWhere)[0];
    //获取更新列表的长度
    $len = count($saveWhere[$key]);
    $flag=true;
    $model = isset($model)?$model:M($tableName);
    //开启事务处理机制
    $model->startTrans();
    //记录更新失败ID
    $error=[];
    for($i=0;$i<$len;$i++){
        //预处理sql语句
        $isRight=$model->where($key.'='.$saveWhere[$key][$i])->save($saveData[$i]);
        if($isRight==0){
            //将更新失败的记录下来
            $error[]=$i;
            $flag=false;
        }
        //$flag=$flag&&$isRight;
    }
    if($flag ){
        //如果都成立就提交
        $model->commit();
        return $saveWhere;
    }elseif(count($error)>0&count($error)<$len){
        //先将原先的预处理进行回滚
        $model->rollback();
        for($i=0;$i<count($error);$i++){
            //删除更新失败的ID和Data
            unset($saveWhere[$key][$error[$i]]);
            unset($saveData[$error[$i]]);
        }
        //重新将数组下标进行排序
        $saveWhere[$key]=array_merge($saveWhere[$key]);
        $saveData=array_merge($saveData);
        //进行第二次递归更新
        saveAll($saveWhere,$saveData,$tableName);
        return $saveWhere;
    }
    else{
        //如果都更新就回滚
        $model->rollback();
        return false;
    }
}

//获取快递信息 $id:快递单号 $name:快递公司编号(可不填)
function get_market($id,$name='auto')
{
    //获取地址
    $market_host=M('config')->where('config_name="market_host"')->find();
    //获取code
    $market_appcode=M('config')->where('config_name="market_appcode"')->find();
//     $host = "http://jisukdcx.market.alicloudapi.com";
    $host=$market_host['config_value'];
    $path = "/express/query";
    $method = "GET";
//     $appcode = "f92896288a5949088c46c166af190b2c";
    $appcode=$market_appcode['config_value'];
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "number=$id&type=$name";
    $bodys = "";
    $url = $host . $path . "?" . $querys;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $result = curl_exec($curl);
    $jsonarr = json_decode($result,true);
    $result = $jsonarr['result'];
    return $result;

}

/***
 * @return mixed 获取ip地址
 */
function getip() {
    $Ip = '';
    $Ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
        $Ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $Ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $Xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $Xip)) {
                $Ip = $Xip;
                break;
            }
        }
    }
    return $Ip;
}

/**
 * @param $directory 图片保存目录
 * @param $filename  图片保存目录名
 * @param $name      图片类型
 * @return array     生成图片保存全文件路劲方法
 */
function func_save_path($directory,$filename,$name){
    $basePath = $directory.$filename;//图片 上传保存的根目录
    $yNow = date("Y");
    $mNow = date("m");
    $dNow = date("d");
    $imgPath = $basePath. "/" . $yNow . "/" . $mNow . "/" . $dNow . "/";
    $baseImgPath = getcwd()."/".$imgPath;
    $fileType = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
    return array($baseImgPath,$fileType,$imgPath);
}

/**
 * @param $fileType    图片转换后的类型
 * @param $baseImgPath 图片保存的目录名
 * @param $imgPath     图片保存在哪个文件下
 * @param $file        图片信息
 * @param $type        图片类型用于循环
 * @return array       一张图片生成多张图片方法
 */
function func_multi_generation($fileType,$baseImgPath,$imgPath,$file,$type){
    $thumb_size = C('CONFIG')['Base'];
    $arr_size = array();
    $arr_size[] = explode('*',$thumb_size['photo1']);
    $arr_size[] = explode('*',$thumb_size['photo2']);
    $arr_size[] = explode('*',$thumb_size['photo3']);

    $thumbarr = array();
    foreach($arr_size as $key=>$val){
        $newwidth = $val[0];
        $newheight = $val[1];
        $imgName = time() . $key . md5($file['name']).'.'.$fileType;
        $img =  $baseImgPath . $imgName;
        $thumbarr[] = $imgPath.$imgName;
        copy($file['tmp_name'],$img);
        $imagedata = getimagesize($img);
        $width  = $imagedata[0];
        $height = $imagedata[1];
        $thumb  = imagecreatetruecolor($newwidth, $newheight);
        switch ($type) {
            case 'image/gif':
                $source = imagecreatefromgif($img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagegif($thumb, $img);
                break;
            case 'image/png':

                $source = imagecreatefrompng($img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagepng($thumb, $img);
                break;
            case 'image/jpg':

                $source = imagecreatefromjpeg($img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagejpeg($thumb, $img);
                break;
            case 'image/jpeg':
                $source = imagecreatefromjpeg($img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagejpeg($thumb, $img);
                break;
            case 'image/bmp':
                $source = imagecreatefromwbmp($img);
                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                imagewbmp($thumb, $img);
                break;
        }
        imagedestroy($thumb);
    }
    return $thumbarr;
}


/**
 * 上传图片代码结束
 */

function cut_str($str,$sign,$number){
    $array=explode($sign, $str);
    $length=count($array);
    if($number<0){
        $new_array=array_reverse($array);
        $abs_number=abs($number);
        if($abs_number>$length){
            return 'error';
        }else{
            return $new_array[$abs_number-1];
        }
    }else{
        if($number>=$length){
            return 'error';
        }else{
            return $array[$number];
        }
    }
}

function url_left(){
    $url = cut_str($_SERVER['PHP_SELF'],'/','-1');
    $url = cut_str($url,'.','0');
    switch ($url) {
        case 'throttle':
            return '1';
            break;
        case 'open':
            return '2';
            break;
        case 'record':
            return '3';
            break;
        case 'reword':
            return '4';
            break;
        case 'open_area':
            return '5';
            break;
        case 'lists':
            return '6';
            break;
        case 'bank':
            return '7';
            break;
        default:
            return '1';
            break;
    }
}

/**
 * 银行卡替换为星号
 */
function  Func_Bank_Number($BankNum){
    $Before = substr($BankNum,0,4);
    $End    = substr($BankNum,-4);
    $Len    = strlen(($BankNum));
    $StartLen = $Len-10;
    $StartStr = "";
    for($i=1;$i<=$StartLen;$i++){
        $StartStr.="*";
    }
    return $Before.$StartStr.$End;
}

/**
 * 电话号码替换
 */
function  Func_Phone_Replace($Phone){
    $Before = substr($Phone,0,3);
    $End    = substr($Phone,-4);
    $Data   = $Before."*****".$End;
    return $Data;
}

/**
 * @param $Name      替换的字符串
 * @param int $Front 字符串前面保留几位
 * @param int $After 字符串后面保留几位
 * @return string    字符串*替换
 */
function Func_Name($Name,$Front = 1,$After = 0){
    $Strlen = mb_strlen($Name,"utf-8");
    $StartLen = $Strlen - $Front;
    $StartEnd = $Strlen - $After;
    $Before   = mb_substr($Name,0,$Front,"utf-8");

    if($After != 0){
        $End      = mb_substr($Name,-$After,$StartEnd,'utf-8');
    }

    $StartStr = "";

    for($i=1;$i<=$StartLen;$i++){
        $StartStr.="*";
    }

    return $Before.$StartStr.$End;
}

/**
 * @param $cardnumber 银行卡号
 * @return bool  验证银行卡号对不对
 */

function ValiDateCard($cardnumber )
{
    $cardnumber = preg_replace ( " /\D|\s/ " , "" , $cardnumber ) ; # strip any non-digits
    $cardlength = strlen ( $cardnumber ) ;
    if ( $cardlength != 0 )
    {
        $parity = $cardlength % 2 ;
        $sum = 0 ;
        for ( $i = 0 ; $i < $cardlength ; $i ++ )
        {
            $digit = $cardnumber [ $i ] ;
            if ( $i % 2 == $parity ) $digit = $digit * 2 ;
            if ( $digit > 9 ) $digit = $digit - 9 ;
            $sum = $sum + $digit ;
        }
        $valid = ( $sum % 10 == 0 ) ;
        return $valid ;
    }
    return false ;
}

/**
 * @param $Data       生成二维码数组内容
 * @param int $Type   1生成单独二维码 2生成邀请注册海报 3生成分享商品海报
 * @return resource|string
 *
 */
function QrCode($Data,$Type = 1){
    vendor('Phpqrcode/phpqrcode');
    $Date       = date("Ymd",time());
    $DateTime   = date("YmdHis",time());
    $ImgName    = $Data["Picturename"];//图片生成名字
    $Url        = $Data["url"];//生成的二维码内容

    if ($Type == 1){
        $Image_Name = "user_"."$DateTime".$ImgName.".png";
    }else{
        $Image_Name = "user_".$ImgName.".png";
    }

    switch ($Type){
        case 1:
            $File  = "bespeak";
            $Files = $File."/".$Date;
            $Size  = 2;
            $WhiteEdge = 1;break;
        case 2:
            $File  = "erweima";
            $Files = $File;
            $Size  = 5;
            $WhiteEdge = 3;break;
        case 3:
            $File  = "goods";
            $Files = $File."/".$Date;
            $Size  = 2;
            $WhiteEdge = 1;break;
    }


    if(!is_dir("./uploads/{$File}/"))mkdir("./uploads/{$File}/");

    if ($Type != 2){
        if(!is_dir("./uploads/{$Files}/"))mkdir("./uploads/{$Files}/");
    }

    \QRcode::png($Url, "uploads/{$Files}/".$Image_Name, 'H', $Size, $WhiteEdge);

    $QRCode = Config::get('picture').$Files."/".$Image_Name;//二维码全路径

    if($Type == 1){
        return $QRCode;
    }elseif($Type == 2) {
        $PosterType = $Data["type"];

        $Poster = $PosterType.".png";
        $SourcePoster = Config::get('invitation').$Poster;//准备好的源海报图片
        $NewPoster    = "uploads/{$Files}/invite{$ImgName}.png";//复制到的新海报地址

        copy($SourcePoster,$NewPoster);//海报复制

        $Posters = Config::get('picture')."{$Files}/invite{$ImgName}.png";//全新的分享海报图片地址
        $Invite  =  $Posters;

        $QRCode  = imagecreatefromstring(file_get_contents($QRCode));
        $Posters = imagecreatefromstring(file_get_contents($Posters));

        $Poster_Width  = imagesx($Posters);//邀请海报图片宽度
        $Poster_Height = imagesy($Posters);//邀请海报图片高度
        $Src_W         = imagesx($QRCode);//二维码图片宽度
        $Src_H         = imagesy($QRCode);//二维码图片高度

        $Dst_W = $Poster_Width / 3.7;
        $scale = $Src_W / $Dst_W;
        $Dst_H = $Src_H / $scale;
        $Dst_X = ($Poster_Width - $Dst_W) / 2;
        $Dst_Y = ($Poster_Height - $Src_H) / 1.27;


//        $Fonts      = __DIR__ . "\\fontes\\simhei.ttf";//字体
//        $FontSize   = 30;//字体大小
//        $Text       = $Data["truename"];//水印字
//        $TextString = mb_strlen($Text);//字体长度
//
//        switch ($TextString){
//            case 2:
//                $Name_w = ($Poster_Width - strlen($Text)*20/2) / 2.1 ;break;
//            case 3:
//                $Name_w = ($Poster_Width - strlen($Text)*12/2) / 2.2 ;break;
//            case 4:
//                $Name_w = ($Poster_Width - strlen($Text)*15/2) / 2.3 ;break;
//            case 5:
//                $Name_w = ($Poster_Width - strlen($Text)*12/2) / 2.4 ;break;
//            case 6:
//                $Name_w = ($Poster_Width - strlen($Text)*13/2) / 2.5 ;break;
//        }

//        if ($PosterType == 1){
//            $FontColor  = imagecolorallocate ( $Posters, 255, 215, 0 );//字的RGB颜色
//            $Name_h = $Poster_Height/ 4.3;
//        }elseif($PosterType == 2){
//            $FontColor  = imagecolorallocate ( $Posters, 41, 62, 93 );//字的RGB颜色
//            $Name_h = $Poster_Height/ 3;
//        }else{
//            $FontColor  = imagecolorallocate ( $Posters, 255, 255, 255 );//字的RGB颜色
//            $Name_h = $Poster_Height/ 1.5;
//        }

//        imagettftext($Posters,$FontSize,0,$Name_w,$Name_h,$FontColor,$Fonts,$Text);

        imagecopyresampled($Posters,$QRCode , $Dst_X, $Dst_Y, 0, 0, $Dst_W,
            $Dst_H, $Src_W, $Src_H);

        //输出图片
        imagepng($Posters, "uploads/{$Files}/invite{$ImgName}.png");


        imagedestroy ( $QRCode );
        imagedestroy ( $Posters );

        return $Invite;
    }elseif ($Type == 3){

        $SourcePoster = $Data["poster"];//准备好的源海报图片
        $NewPoster    = "uploads/$Files/goods_{$DateTime}{$ImgName}.png";//复制到的新海报地址

        copy($SourcePoster,$NewPoster);//海报复制

        $Posters = Config::get('picture')."$Files/goods_{$DateTime}{$ImgName}.png";//全新的分享海报图片地址
        $Invite  =  $Posters;

        $QRCode  = imagecreatefromstring(file_get_contents($QRCode));
        $Posters = imagecreatefromstring(file_get_contents($Posters));

        $Poster_Width  = imagesx($Posters);//邀请海报图片宽度
        $Poster_Height = imagesy($Posters);//邀请海报图片高度
        $Src_W         = imagesx($QRCode);//二维码图片宽度
        $Src_H         = imagesy($QRCode);//二维码图片高度

        $Dst_W = $Poster_Width / 5;
        $scale = $Src_W / $Dst_W;
        $Dst_H = $Src_H / $scale;
        $Dst_X = ($Poster_Width - $Dst_W) / 35;
        $Dst_Y = ($Poster_Height - $Src_H) / 1.08;

        imagecopyresampled($Posters,$QRCode , $Dst_X, $Dst_Y, 0, 0, $Dst_W,
            $Dst_H, $Src_W, $Src_H);

        imagepng($Posters, "uploads/{$Files}/goods_{$DateTime}{$ImgName}.png");

        imagedestroy ( $Posters );
        imagedestroy ( $QRCode );

        return $Invite;
    }
}

/**更新token值
 * @param $im_id
 * @return array
 */
function renewToken($im_id){
    vendor("IM_sdk.ServerAPI");
    $imServer     =      new ServerAPI('b303ed02aa3c77dc5365b24269925a6e','0bf8f8a5c978','curl');     //php curl库
    $data         =      $imServer->updateUserToken($im_id);
    return $data;
}

/**
 *  二维数组（某个值得大小）进行排序
 * @param  $array带排序的数组fieldparameter字段参数
 * @return 排好的数据
 */
function actionArraysort($array,$fieldparameter,$sorttype='SORT_DESC'){
    /*二维数组根据某个值进行排序*/
    $sort = array(
        'direction' => $sorttype, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
        'field'     => $fieldparameter,       //排序字段
    );
    $arrSort = array();
    foreach($array AS $uniqid => $row){
        foreach($row AS $key=>$value){
            $arrSort[$key][$uniqid] = $value;
        }
    }
    if($sort['direction']){
        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $array);
    }
    return $array;
}

/**
 * 加密
 * @param arr $args
 * @return boolean
 */
function signs($args,$key){
    ksort($args);
    $query = http_build_query($args);
    $query .= '&key='.$key;
    $sign = strtoupper(md5($query));
    return $sign;
}

/** * 验签
 * @param arr $args
 * @return boolean
 */
function checkSign($args,$key){
    $sign = strtoupper($args['sign']);
    unset($args['sign']);
    if($sign != sign($args,$key)){
        return false;
    }
    return true;
}

/**
 * 发送验证码
 * @param int $mobile
 * @param int $code
 * @param int $charge
 * @return mixed|SimpleXMLElement|\Vendor\aldy_SDK\top\ResultSet
 */
function player($mobile=0,$charge=''){
    $config=new \Admin\Model\ConfigModel();
    $sms_appkey=$config->field('config_value')->where('config_name="sms_appkey"')->find();
    $sms_secret=$config->field('config_value')->where('config_name="sms_secret"')->find();
    $code = (string)rand(100000, 999999);
    $data=array(
        'code'=> $code,
        'name'=> $charge,
        'mobile' => $mobile,
        'sms_name' => '联和优购',
        'template'=> 'SMS_66090073',
        'appkey' => $sms_appkey['config_value'],
        'secret' => $sms_secret['config_value']
    );
//    var_dump($data);
    $table = M('yzm');
    if($table->getByPhone($mobile))
    {
        $table->where(['phone'=>$mobile])->save(['yzm'=>$code,'addtime'=>time()]);
    }else{
        $table->add(['phone'=>$mobile,'yzm'=>$code,'addtime'=>time()]);
    }
    Vendor('aldy_SDK.TopSdk','','.php');
    $message = array(
        'code' => $data['code'],
        'name' => $data['name'],
        'phone'=> $data['mobile']
    );
    $c = new \Vendor\aldy_SDK\top\TopClient();
    $c->appkey = $data['appkey'];
    $c->secretKey =  $data['secret'];
    $req = new \Vendor\aldy_SDK\top\request\AlibabaAliqinFcSmsNumSendRequest();
    $req->setExtend("123456");
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($data['sms_name']);
    $req->setSmsParam(json_encode($message));
    $req->setRecNum($data['mobile']);
    $req->setSmsTemplateCode($data['template']);
    $resp = $c->execute($req);
    return $resp;
}

function url_curl($url='',$data,$type='post'){
    // 启动
    $ch = curl_init();
    // 数据参数传递
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,'1');
    // post数据
    curl_setopt($ch,CURLOPT_POST,'1');
    // post的变量
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
    // 接受返回的值
    $output = curl_exec($ch);
    curl_close($ch);
    // 打印获得的数据
    // print_r($output);
    // 以上方式获取到的数据是json格式的，使用json_decode函数解释成数组。
    // $output_array = json_decode($output,true);
    return $output;
}


/**
 * 分红返利状态中文切换
 * @param $status
 * @return string
 */
function returnTypeChinese($status){
    switch($status){
        case 1:
            $status="20%";
            break;
        case 2:
            $status="10%";
            break;
        case 3:
            $status="5%";
            break;
        case 4:
            $status="3%";
            break;
        default:;break;

    }
    return $status;
}

/**
 * 格式化模糊查询条件字符串
 * @param $a
 * @param $b
 * @param string $c
 * @param string $d
 * @return string
 */
function format_where($a,$b,$c="like",$d=""){
    if(strpos($c,"=")!==false)
        $format = " %2s `%4s` %2s '%s'";
    else
        $format = " %2s `%4s` %2s '%s%%'";
    return sprintf($format,$d,$a,$c,$b);
}


/**
 * @param $P         当前页数
 * @param $Count     分页总条数
 * @param $Psize     分页每页条数
 * @return int
 * 获取当前展示的页面分页
 */
function Func_Page($P = 1,$Count,$Psize = 20){
    if(empty($P)){
        $Pg = ceil($Count/$Psize);
    }else{
        if($P == 1){
            $Pg = 0;
        }else{
            $Pg = ($P-1) * $Psize;
        }
    }
    return $Pg.",".$Psize;
}

/**根据经纬度获取天气数据
 * @param $lon  //经度
 * @param $lat  //纬度
 * @return mixed
 */
function get_air($lon,$lat){
    header("Content-type: text/html; charset=utf-8");
    $host = "http://aliv8.data.moji.com";
    $path = "/whapi/json/aliweather/condition";
    $method = "POST";
    $appcode = "80ae4fe787c84b4c94b0fd54bee40f45";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    //根据API的要求，定义相对应的Content-Type
    array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
    $querys = "";
    $bodys = "lat=$lat&lon=$lon&token=ff826c205f8f4a59701e64e9e64e01c4";
    $url = $host . $path;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
    $result = curl_exec($curl);
    //分离头尾
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $body = substr($result, $headerSize);
    }
    $jsonarr = json_decode($body,true);
    return $jsonarr;
}

/**
 *  get_xml 获取的XML文件
 * @param $name
 * @param string $_xmlfile
 * @return int|SimpleXMLElement[]
 */

function get_xml($name,$_xmlfile = './city_moji.xml'){
    if(file_exists($_xmlfile)){
        $_xml = file_get_contents($_xmlfile);
        $obj_xml = simplexml_load_string($_xml,'SimpleXMLElement',LIBXML_NOCDATA);
        foreach ($obj_xml->city as $child) {
            if(strpos($child->attributes()->name,$name) || $child->attributes()->name==$name){
                $attr_id = $child->attributes()->id;
                return $attr_id;
            }else{
                $attr_id = 2;
            }
        }
    }else{
        php_alert("$_xmlfile");
    }
    return $attr_id;
}

/**
 * 缓存,更新XML数据
 * @param $_xmlfile xml文件
 * $attr_id 从XML中取出的数据
 **/
function get_xml_cache($_xmlfile){
    header("Content-type: text/html; charset=utf-8");
    $redis = new \Redis;
    $redis->connect('127.0.0.1',6379);
    $id = string;
    if(file_exists($_xmlfile)){
        $_xml = file_get_contents($_xmlfile);
        $obj_xml = simplexml_load_string($_xml,'SimpleXMLElement',LIBXML_NOCDATA);
        // $xml = xml_parse();
        $xml_josn =json_decode(json_encode($obj_xml),true);
        // var_dump($xml_josn);exit;
        foreach ($xml_josn['city'] as $child) {
            // print_r($child['@attributes']);
            $names = $child['@attributes']['name'];
            $id = $child['@attributes']['id'];
            $type[$names] = $redis->set($names,$id)?'1':'0';
            // $type[$names] = $redis->set($id,$names)?'1':'0';
            // if(strpos($names,$name) || $names==$name){
            //     $attr_id = $id;
            // }else{
            //     $attr_id = 2;
            // }
            // $name = $child->attributes()->name;
            // $id = "$child->attributes()->id";
            // echo $id.'<hr/>';
            // dump($id);
            // print_r($child->attributes());
            // exit;
            // $type[$name] = $redis->set($id,$name)?'1':'0';
            // if(strpos($child->attributes()->name,$name) || $child->attributes()->name==$name){
            //     $attr_id = $child->attributes()->id;
            // }else{
            //     $attr_id = 2;
            // }
        }
    }else{
        php_alert("$_xmlfile");
    }
    return $type;
}

function php_alert($str){
    echo "<script>alert('$str')</script>";
}



/**
 * 数组去重
 * @param $array
 * @return array
 */
function remove_duplicate($array){
    $result=array();
    foreach ($array as $key => $value) {
        $has = false;
        foreach($result as $val){
            if($val['store_id']==$value['store_id']){
                $has = true;
                break;
            }
        }
        if(!$has)$result[]=$value;
    }
    return $result;
}

/**
 * 格式化打印数组（print_r和预排版标签pre）
 * @param  $is_utf8 数据字符集,默认utf-8
 * @return array
 */
function tprint($is_utf8 = true){
    $params = func_get_args();

    if ($is_utf8) {
        echo '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
    }

    echo '<pre>';
    call_user_func_array('print_r', $params);
    echo '</pre>';
}

/**
 * 判断并分割字符串到指定长度
 * @param  $string 要处理的字符串
 * @param  $length 要获得的长度
 * @return array
 */
function split_string($string='', $length=0, $suffix=''){
    if ($length == 0 || mb_strlen($string,'utf-8') < $length){
        return $string;
    }
    return mb_substr($string,0,$length,'utf-8').$suffix;
}

/**
 * app自定义分页
 * @param  $rowData 数据数组
 * @param  $Count   总条数
 * @param  $page    当前页
 * @param  $limit   每页条数
 * @return array   $new_oData
 */
function getPageDataByArr($rowData,$Count,$page,$limit=20){
    $page_total = ceil($Count/$limit);
    if($page>$page_total){
        $data = array(
            'count' => $Count,
            'page_data' => [],
        );
        return $data;
    }elseif($page<=0){
        $page = 1;
    }
    $limit_start = ($page-1)*$limit;
    $limit_end   = $limit_start+$limit;
    $new_oData   = [];
    foreach($rowData as $key=>$val){
        if($limit_start<=$key&&$key<=($limit_end-1)){
            $new_oData []= $val;
        }
    }
    $all_data = array(
        'count' => $Count,
        'page_data' => $new_oData,
    );
    return $all_data;
}
