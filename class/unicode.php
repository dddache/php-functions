<?php

/*
    DOC

        @@  字符编码转换:
                iconv:  
                    document: http://cn2.php.net/manual/zh/function.iconv.php
                    code:
                        // 把UTF-8的编码转换为 UCS-4 的编码
                        iconv('UTF-8', 'UCS-4', $string);

                mb_convert_encoding:
                    document: http://cn2.php.net/manual/zh/function.mb-convert-encoding.php
                    code:
                        // 把UTF-8的编码转换为 UCS-4 的编码
                        mb_convert_encoding($string, 'ucs-4', 'utf-8');

        @@      进制转换
                bin2hex:
                        document: http://cn2.php.net/manual/zh/function.bin2hex.php
                        code:
                            // 把 UCS-4 字符 "我" 转换成 十六进制表示 00004f60
                            $ucs4_string = mb_convert_encoding('你', 'ucs-4', 'utf-8');
                            echo bin2hex($ucs4_string);                                                           // 00004f60
                hex2bin:
                        document: http://cn2.php.net/manual/zh/function.hex2bin.php
                        code:
                            // 将十六进制字符串转换为二进制字符串
                            $ucs4_string = mb_convert_encoding('你', 'ucs-4', 'utf-8');
                            $hex_string = bin2hex($ucs4_string);                                           // 00004f60
                            $bin_string = hex2bin($hex_string);
                            // 把 ucs-4 编码转换为 utf-8 编码
                            $utf8_string = mb_convert_encoding($bin_string , 'utf-8', 'ucs-4' );

                base_convert:
                        // 在任意进制之间转换数字
                        document:   http://cn2.php.net/manual/zh/function.base-convert.php
                        code:
                            
                            $ucs4_string = mb_convert_encoding('你', 'ucs-4', 'utf-8');
                            $hex_string = bin2hex($ucs4_string);                                           // 00004f60
                            $dec_string = base_convert($hex_string, 16, 10);                     // 将字符串的十六进制数字转换为 十进制
                                                                                                                                              // XML 实体字符就是 UTF-8 字符串的十进制表示
                                                                                                                                              // 加上 起始符'&#'然后加上结尾符';'
                                                                                                                                              // 比如: &#11111; ( 其中 11111 即为 utf-8 字符的十进制表示 )
                            // 由于我们当前的字符编码已被转换为 UCS-4编码，所以如果需要组装成 XML 实体字符，还需要进行编码转换
                            $ucs4_string = hex2_bin(base_convert($dec_string, 10, 16));      // UCS-4 字符串
                            $utf8_string = mb_convert_encoding($ucs4_string, 'utf-8', 'ucs-4');
                            $hex_string = bin2hex($utf8_string);
                            $dec_string = base_convert($hex_string, 16, 10);
                            $xml_string = "&#{$dec_string};";                 // XML 实体字符
        

        @       其它替代函数
                pack:
                    document:  http://cn2.php.net/manual/zh/function.pack.php
                    desc:              强烈建议大家需要阅读该函数
                                             该函数在大部分正常语言都是一个及其重要的基础函数
                                             PHP 的 bin2hex 和 hex2bin 以及 base_convert 不过是对该 pack 函数做了一个 简单封装而已
                                             类似与 Python 的 binascii Lib(当然功能不能类比....).
                Recode:
                    document: http://cn2.php.net/manual/zh/book.recode.php
                    desc           : GNU Recode  ( 字符编码判断以及互换 )
                

        @       关于字符集的简单介绍
                UTF-32: 
                        document:  http://zh.wikipedia.org/zh-cn/UTF-32
                        desc            : UTF-32 是 UCS-4 的一个子集
                UTF-16:
                        document:  http://zh.wikipedia.org/wiki/UTF-16
                        desc            : UTF-16 是 UCS-2 的一个子集





    ####################################################
    ## 关于字符集及编码方面的更多问题，如有描述不正确的地方，欢迎指正。
    ## 电邮: gnulinux@126.com
    ####################################################

*/


class Ustring{
    /*
        Require Package(EXT): 
                iconv:         http://cn2.php.net/manual/zh/book.iconv.php
                mbstring: http://cn2.php.net/manual/zh/ref.mbstring.php
        
    */
    //static $CODE

    public function __construct ($string=''){
        iconv_set_encoding("internal_encoding", "UTF-8");          // http://cn2.php.net/manual/zh/function.iconv-set-encoding.php

        $encoding = array('UTF-8') ;
        $is_utf8 = mb_check_encoding ( $string,  $encoding ) ;  // http://cn2.php.net/manual/zh/function.mb-check-encoding.php
        if ( !$is_utf8 ){
            return false;
        }

        //self::$string = $string;
    }
    public function detect(){

    }
    public function toUTF8(){

    }
    public function toUCS4(){

    }
    public function toUCS2(){

    }
    public function toGB18030(){

    }
    public function toXML($string){
        // XML 实体字符
        $ucs4 = bin2hex(mb_convert_encoding($string, 'ucs-4', 'utf-8'));  // 每个字符长度为8个字节（十六进制）
        $len = strlen($ucs4);
        $xml_str = '';
        for ($i=0; $i<$len-1; $i +=8){
            $s = substr($ucs4, $i, 8);
            $xml_str .= '&#'.base_convert($s, 16, 10).';';     // 十六进制 转换为 十进制表示
        }
        return $xml_str;
    }
    public function toList($string){
        // Safe List.
        $ucs4 = bin2hex(mb_convert_encoding($string, 'ucs-4', 'utf-8'));  // 每个字符长度为8个字节（十六进制）
        $len = strlen($ucs4);
        $str_array = array();
        for ($i=0; $i<$len-1; $i +=8){
            $ucs4_str = substr($ucs4, $i, 8);
            $utf8_str = mb_convert_encoding(hex2bin($ucs4_str), 'utf-8', 'ucs-4');
            $str_array[] = $utf8_str;
        }
        return $str_array;
    }

}

function test_XMLString(){
    // 测试 转换 XML 实体字符
    $us = new Ustring();
    $string = "你好，World.";
    echo $us->toXML($string);
    echo "\n";
}

function test_Str2List(){
    // Safe List.
    $us = new Ustring();
    $string = "你好，World.";
    print_r($us->toList($string) );
    echo "\n";
}

// test_XMLString();        //  测试 字符串转化为 XML 的十进制实体字符
// test_Str2List();            // 测试字符串转换为列表 ( 多维查找需要用到 )




























/*

Old test function.

function utf8_unicode($name){
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len  = strlen($name);
    $str  = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2){
        $c  = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0){   //两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
            //$str .= base_convert(ord($c), 10, 16).str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);  
        } else {
            $str .= '\u'.str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
            //$str .= str_pad(base_convert(ord($c2), 10, 16), 4, 0, STR_PAD_LEFT);
        }
    } 
    $str = strtoupper($str);//转换为大写
    return $str;
}


function utf8_usc4($str){
    // 从 UTF-8 编码 转换至 UCS-4 ( UTF-32子集 ) 编码
    $ucs4 = bin2hex(mb_convert_encoding($str, 'ucs-4', 'utf-8'));  // 每个字符长度为8个字节（十六进制）
    $len = strlen($ucs4);
    $str_array = array();                                                // 字符序列
    for ($i=0;$i<$len-1;$i +=8){
        $s = substr($ucs4, $i, 8);                                    // 提取八个字节的比特
        $aaa = hex2bin($s);
        $bbb = iconv('UCS-4', 'UTF-8', $aaa);
        echo $bbb;
        echo "\t";

        $s_number = base_convert($s, 16, 10);        //  计算该八字节长度字符的十进制数字( 从十六进制转换过来 )
        $str_array[] = $s_number;                                //  追加进 字符序列
    }

    return $str_array;
}
echo bin2hex(mb_convert_encoding('你', 'ucs-4', 'utf-8'));

echo "\n";
$a = utf8_usc4('你好.nihao。');
print_r($a);

$b =  base_convert(22909, 10, 16) ;
echo hex2bin($b)."\n";
$bb = mb_convert_encoding( hex2bin($b), 'utf-8', 'ucs-4' );
var_dump($bb);
//utf8_unicode("你好，nihao.");

// UCS-4 : UTF-32 (http://zh.wikipedia.org/wiki/UTF-32)
// iconv参考：http://www.php.net/manual/zh/function.iconv.php


// $str = "你";
// $ucs2 = iconv('UTF-8', 'UCS-4', $str);
// echo strlen($ucs2)."\n";exit();
// $hex= bin2hex($ucs2);
// echo $hex."\n";
// $unicode_html = '&#'.base_convert($hex, 16, 10).';';

// echo $unicode_html;


function xml_code($str){
    //$ucs4 = iconv('UTF-8', 'UCS-4', $str);  // 长度为4个字节
    // 支持多字节字符（其实只要脚本编码统一，多字节字符可以不需要判断）
    $ucs4 = bin2hex(mb_convert_encoding($str, 'ucs-4', 'utf-8'));  // 每个字符长度为8个字节（十六进制）
    $len = strlen($ucs4);
    $xml_str = '';
    for ($i=0;$i<$len-1;$i +=8){
        $s = substr($ucs4,$i,8);
        $xml_str .= '&#'.base_convert($s,16,10).';';
    }
    return $xml_str;
}




function other(){
    // 参考：http://blog.longwin.com.tw/2011/06/php-html-unicode-convert-2011/
    $str = '我';
     將 '我' 轉換成 '25105' 或 '&#25105;' 
    // 使用 iconv
    $unicode_html = base_convert(bin2hex(iconv('UTF-8', 'UCS-4', $str)), 16, 10); // 25105
    // 使用 mb_convert_encoding
    $unicode_html = base_convert(bin2hex(mb_convert_encoding($str, 'ucs-4', 'utf-8')), 16, 10); // 25105
    // 补上 &#xxxxx;
    $unicode_html = '&#' . base_convert(bin2hex(iconv("utf-8", "ucs-4", $str)), 16, 10) . ';'; // &#25105;
    // 将 &#25105 转回 '我'
    $str = mb_convert_encoding($unicode_html, 'UTF-8', 'HTML-ENTITIES'); // '我', $unicode_html = '&#25105'
}

//echo xml_code("你好恩asd啊数据库的发生空间的阿斯顿吧数据库");

*/