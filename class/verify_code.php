<?php

class VerifyCode{
    /*

        @@  验证码图片生产类
            来源于 ThinkPHP 以及 RickyFeng 在开源中国( oschina.net )分享的代码.
            
            函数 create_image:
                Author: RickyFeng
                URI   : http://www.oschina.net/code/snippet_106025_6280

            函数 create_image_with_font:
                Author: ThinkPHP
                URI   : https://github.com/liu21st/thinkphp
        
        
        @@  PHP Image 相关函数
            Document: http://cn2.php.net/manual/zh/function.imagejpeg.php
                      http://cn2.php.net/manual/zh/function.imagepng.php
            Code    : 
                $im = imagecreatetruecolor(120, 20);
                $text_color = imagecolorallocate($im, 233, 14, 91);
                imagestring($im, 1, 5, 5,  'A Simple Text String', $text_color);

                // 设置内容类型标头 —— 这个例子里是 image/jpeg
                header('Content-Type: image/jpeg');

                // 输出图像
                imagejpeg($im);   // Or   imagepng($im);

                // 释放内存
                imagedestroy($im);

    */

    public static $CharMap    = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ";    // 生产验证码所需要的字符
    public static $CharLength = 4;                                                             // 验证码长度                    
    public static $FontSize   = 18;                                                            // 验证码字符字体大小
    public static $FontFile   = "../static/fonts/verify.ttf";                                  // 验证码字符字体家族

    //public static $ImageWidth = 4*18*1.5;                                                    // 验证码图片宽度

    public static $ImageWidth = 108;                                                           // 验证码图片宽度

    //public static $ImageHeight= 18*2;                                                        // 验证码图片高度

    public static $ImageHeight= 36;                                                            // 验证码图片高度
    public static $ImageBG    = array(243, 251, 254);                                          // 验证码图片背景


    public static function build ($opt=array() ){
        // 生产验证码图片
        // $opt: array
        if ( !is_array($opt) ) return false;

        // 参数检查
        if ( isset($opt['ImageWidth']) && !empty($opt['ImageWidth']) ) $ImageWidth = $opt['ImageWidth'];
        else $ImageWidth = self::$ImageWidth;

        if ( isset($opt['ImageHeight']) && !empty($opt['ImageHeight']) ) $ImageHeight = $opt['ImageHeight'];
        else $ImageHeight = self::$ImageHeight;

        if ( isset($opt['ImageBG']) && !empty($opt['ImageBG']) ) $ImageBG = $opt['ImageBG'];
        else $ImageBG = self::$ImageBG;

        if ( isset($opt['CharLength']) && !empty($opt['CharLength']) ) $CharLength = $opt['CharLength'];
        else $CharLength = self::$CharLength;

        if ( isset($opt['FontSize']) && !empty($opt['FontSize']) ) $FontSize = $opt['FontSize'];
        else $FontSize = self::$FontSize;

        if ( isset($opt['FontFile']) && !empty($opt['FontFile']) ) {
            $use_font = true;      // 判断是否使用指定字体来生成验证码
            $FontFile = $opt['FontFile'];
        } else{
            $use_font = false;     // 判断是否使用指定字体来生成验证码
            $FontFile = self::$FontFile;
        }

        if ( $use_font == true ){
            // 使用字体
            $verify_data = self::create_image_with_font($ImageWidth, $ImageHeight, $ImageBG, $FontSize, $FontFile, $CharLength);
        } elseif ( $use_font == false ) {
            // 不使用字体
            $verify_data = self::create_image($ImageWidth, $ImageHeight, $ImageBG, $CharLength);
        } else {
            // unknow.
            return false;  // Ooops.
        }

        /*
            @@  Return :
                    array(
                        'code' => 'abcde...123...',
                        'time' => time(),
                        'image'=> array(
                                    'format' => 'png',               // png/jpg.
                                    'data'   => 'Image Raw Data.',   // image raw data.
                                    )
                    )
        */
        return $verify_data;
    }

    public static function create_image($ImageWidth, $ImageHeight, $ImageBG, $CharLength){
        /*
            $ImageWidth=108, $ImageHeight=36, $ImageBG=array(243, 251, 254), $CharLength=4
        */
        // 不需要字体文件 生产验证码图片
        try{
            ob_clean();
            ob_start();
        }catch(Exception $e){
            // Pass.
        }
        // 创建图片
        $im = imagecreatetruecolor($ImageWidth, $ImageHeight);
        $bgColor = imagecolorallocate($im, $ImageBG[0], $ImageBG[1], $ImageBG[2]);
        imagefill($im, 0, 0, $bgColor);

        // 设置干扰元素
        $area = ($ImageWidth * $ImageHeight) / 20;
        $disturbNum = ($area > 250) ? 250 : $area;
        //加入点干扰
        for ($i = 0; $i < $disturbNum; $i++) {
            $color = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand(1, $ImageWidth - 2), rand(1, $ImageHeight - 2), $color);
        }
        //加入弧线
        for ($i = 0; $i <= 5; $i++) {
            $color = imagecolorallocate($im, rand(128, 255), rand(125, 255), rand(100, 255));
            imagearc($im, rand(0, $ImageWidth), rand(0, $ImageHeight), rand(30, 300), rand(20, 200), 50, 30, $color);
        }

        // 设置验证码
        $code = '';       // 验证码
        for ($i = 0; $i < $CharLength; $i++) {
            $code .= self::$CharMap{rand(0, strlen(self::$CharMap) - 1)};
        }
        
        for ($i = 0; $i < $CharLength; $i++) {
            $color = imagecolorallocate($im, rand(50, 250), rand(100, 250), rand(128, 250));
            $size = rand(floor($ImageHeight / 5), floor($ImageHeight / 3));
            $x = floor($ImageWidth / $CharLength) * $i + 5;
            $y = rand(0, $ImageHeight - 20);
            imagechar($im, $size, $x, $y, $code{$i}, $color);
        }


        $verify['code'] = $code;
        $verify['time'] = time();

        // 输出图片
        imagepng($im);
        $_img_data = ob_get_contents();

        try{
            ob_end_clean();
        }catch(Exception $e){
            // Pass.
        }
        $verify['image'] = array( 'format'=>'png','data'=>$_img_data );
        unset($_img_data);
        return $verify;
    }

    public static function create_image_with_font($ImageWidth, $ImageHeight, $ImageBG, $FontSize, $FontFile, $CharLength){
        /*
            $ImageWidth=108, $ImageHeight=36, $ImageBG=array(243, 251, 254), 
            $FontSize=15, $FontFile="../static/fonts/verify.ttf", $CharLength=4
        */
        // 使用TTF字体文件生产验证码
        try{
            ob_clean();
            ob_start();
        }catch(Exception $e){
            // Pass.
        }

        $im = imagecreate($ImageWidth,$ImageHeight);
        imagecolorallocate($im, $ImageBG[0], $ImageBG[1], $ImageBG[2]);
        $_color = imagecolorallocate($im, mt_rand(1,150), mt_rand(1,150), mt_rand(1,150));
        // useNoise
        for($i = 0; $i < 5; $i++){
            $noiseColor = imagecolorallocate($im, mt_rand(150,225), mt_rand(150,225), mt_rand(150,225));
            for($j = 0; $j < 5; $j++) {
                imagestring($im, 2, mt_rand(-10, $ImageWidth),  mt_rand(-10, $ImageHeight), self::$CharMap{mt_rand(0, 6)}, $noiseColor);
            }
        }

        // 设置验证码
        $code = array();
        $codeNX =$FontSize/2;
        for ($i = 0; $i<$CharLength; $i++) {
            $code[$i] = self::$CharMap[mt_rand(0, 51)];
            imagettftext($im, $FontSize, mt_rand(-40, 40), $codeNX, $FontSize*1.6, $_color, $FontFile, $code[$i]);
            $codeNX += mt_rand($FontSize, $FontSize*1.6);
        }
        
        //$code = $this->authcode(strtoupper(implode('', $code)));
        //substr(md5($str), 0, 5)
        $code = implode('', $code);
        $verify['code'] = $code;
        $verify['time'] = time();
        imagepng($im);
        imagedestroy($im);
        $_img_data = ob_get_contents();
        try{
            ob_end_clean();
        }catch(Exception $e){
            // Pass.
        }
        $verify['image'] = array( 'format'=>'png','data'=>$_img_data );
        unset($_img_data);
        return $verify;
    }

    public static function output_header($image_format, $image_data){
        // 不推荐使用 ( 输出逻辑不应该混淆在 Lib 代码里面 )
        if ( !in_array($image_format, array('png','jpg','jpeg')) ) return false;

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/{$image_format}");
        echo $image_data;
    }

}


function test_with_NoFont(){
    // test verify code with no font.
    ob_end_clean();
    $img_data = VerifyCode::build();
    /*
        @@  Return :
                array(
                    'code' => 'abcde...123...',
                    'time' => time(),
                    'image'=> array(
                                'format' => 'png',               // png/jpg.
                                'data'   => 'Image Raw Data.',   // image raw data.
                                )
                )
    */
    var_dump($img_data);
    $t = time();
    file_put_contents("test_with_NoFont_{$t}.png", $img_data['image']['data']);
}

function test_with_font(){
    // // test verify code with TTF font.
    ob_end_clean();
    
    $font_file = "../static/fonts/verify.ttf";
    // $font_file = VerifyCode::$FontFile;

    $option = array('FontFile'=>$font_file);
    $img_data = VerifyCode::build($option);
    /*
        @@  Return :
                array(
                    'code' => 'abcde...123...',
                    'time' => time(),
                    'image'=> array(
                                'format' => 'png',               // png/jpg.
                                'data'   => 'Image Raw Data.',   // image raw data.
                                )
                )
    */

    var_dump($img_data);

    $t = time();
    file_put_contents("test_with_Font_{$t}.png", $img_data['image']['data']);
}

/*
test_with_NoFont();    // 字体测试模式
test_with_font();      // 系统默认模式( 无须指定字体 )
*/



