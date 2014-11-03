<?php


try{
    require_once "HTTP/Request2.php";
}catch(Exception $e){
    throw new Exception('ERROR : HTTP_Request2 Required. ');
}


Class Requests{

    /*
        关于HTTP请求方法资料请参考: https://zh.wikipedia.org/wiki/%E8%B6%85%E6%96%87%E6%9C%AC%E4%BC%A0%E8%BE%93%E5%8D%8F%E8%AE%AE#.E5.8D.8F.E8.AE.AE.E6.A6.82.E8.BF.B0
                                                                     http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html

        HTTP/1.1 请求方法: 

            #方法名称是区分大小写的。当某个请求所针对的资源不支持对应的请求方法的时候，服务器应当返回状态码405（Method Not Allowed），
            #当服务器不认识或者不支持对应的请求方法的时候，应当返回状态码501（Not Implemented）。
            #HTTP服务器至少应该实现GET和HEAD方法，其他方法都是可选的。
            #当然，所有的方法支持的实现都应当符合下述的方法各自的语义定义。
            #此外，除了上述方法，特定的HTTP服务器还能够扩展自定义的方法。
            #例如：PATCH（由RFC5789指定的方法）:用于将局部修改应用到资源。
            #
            #HTTP/1.1协议中共定义了八种方法（也叫“动作”）来以不同方式操作指定的资源

            OPTIONS：这个方法可使服务器传回该资源所支持的所有HTTP请求方法。用'*'来代替资源名称，向Web服务器发送OPTIONS请求，
                                   可以测试服务器功能是否正常运作。
            HEAD：与GET方法一样，都是向服务器发出指定资源的请求。
                            只不过服务器将不传回资源的本文部份。它的好处在于，使用这个方法可以在不必传输全部内容的情况下，
                            就可以获取其中“关于该资源的信息”（元信息或称元数据）。
            GET：向指定的资源发出“显示”请求。使用GET方法应该只用在读取数据，而不应当被用于产生“副作用”的操作中，
                        例如在Web Application中。其中一个原因是GET可能会被网络蜘蛛等随意访问。参见安全方法
            POST：向指定资源提交数据，请求服务器进行处理（例如提交表单或者上传文件）。
                           数据被包含在请求本文中。这个请求可能会创建新的资源或修改现有资源，或二者皆有。
            PUT：向指定资源位置上传其最新内容。
            DELETE：请求服务器删除Request-URI所标识的资源。
            TRACE：回显服务器收到的请求，主要用于测试或诊断。
            CONNECT：HTTP/1.1协议中预留给能够将连接改为管道方式的代理服务器。
                                    通常用于SSL加密服务器的链接（经由非加密的HTTP代理服务器）。
            PATCH（由RFC5789指定的方法）: 用于将局部修改应用到资源。

    */
    public static function options($options=array()){
        // HTTP OPTIONS Method.
    }
    public static function get($url, $params=array(), $options=array()){
        // HTTP GET Method.
        if ( !is_array($params) || !is_array($options) ) throw new Exception("params type error.");
        $query = http_build_query($params);
        $request = new HTTP_Request2("{$url}?{$query}", 'GET');
        $request = self::_process_options($request, $options);
        return self::_get_response($request);
    }
    public static function head($url, $options=array()){
        // HTTP HEAD Method.

    }
    public static function post($url, $data=array(), $options=array() ){
        // HTTP POST Method.
        // post data  encode way:  urlencode
        if ( !is_array($options) ) throw new Exception("options type error.");
        if ( !is_string($data) && !is_array($data) ) throw new Exception("post data type error.");
        // urlencode 函数 编码与 WWW 表单 POST 数据的编码方式是一样的，同时与 application/x-www-form-urlencoded 的媒体类型编码方式一样。
        // 参见: http://cn2.php.net/manual/zh/function.urlencode.php
        if ( is_array($data) ) $data = http_build_query($data);
        $request = new HTTP_Request2($url, 'POST');
        // request 类的方法 addPostParameter 最终会在http body 上面添加经过 urlencode 编码过的数据
        // 而在 PUT 方法当中, urlencode 编码并不是必须的
        $request->setBody( $data );  // Or $request->addPostParameter( $data );
        $request = self::_process_options($request, $options);
        return self::_get_response($request);
    }
    public static function put($url, $data='', $options=array()){
        // HTTP PUT Method.
        if ( !is_array($options) ) throw new Exception("options type error.");
        if ( !is_string($data) && !is_array($data) ) throw new Exception("post data type error.");
        // PUT 模式下对于 Body 数据默认使用 JSON 序列化, 而 POST 模式下 默认使用 urlencode 编码 ( 历史原因 )
        if ( is_array($data) ) $data = json_encode($data, true);
        $request = new HTTP_Request2($url, 'PUT');
        $request->setBody( $data );

        $request = self::_process_options($request, $options);
        return self::_get_response($request);
    }
    public static function delete($url, $options=array() ){
        // HTTP DELETE Method.

    }
    public static function trace($url, $options=array() ){
        // HTTP TRACE Method.

    }
    public static function connect($url, $options=array() ){
        // HTTP CONNECT Method.

    }

    public static function pacth($url, $options=array() ){
        // HTTP PATCH Method.

    }

    /*
        底层操作方法

    */
    public static function request($url, $options=array() ){
        return new RequestsBase($url, $options);
    }


    /*
        @ Response 处理
    */
    public static function _get_response($request){
        $response = $request->send();
        $code = $response->getStatus();                   // HTTP CODE
        $reason = $response->getReasonPhrase();  // HTTP MSG
        $header = $response->getHeader();
        $cookies = $response->getCookies();
        $body = $response->getBody();                     // ResponseText
        return array('code'=>$code, 'reason'=>$reason, 'header'=>$header, 'cookies'=>$cookies, 'body'=>$body);
    }
    public static function _process_options($request, $options){
        // 处理 可选参数
        if ( isset($options['header']) && is_array($options['header']) ){
            // 追加头信息
            // setHeader('Content-Type', 'application/json');
            foreach ( $options['header'] as $k=>$v ){
                $request->setHeader($k, $v);
            }
        }
        return $request;
    }

}

?>