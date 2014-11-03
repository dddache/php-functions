Requests 
===============

:Date: 2014年 11月 03日 星期一 15:31:17 CST;


.. contents::


Description
---------------------
Simple API For HTTP.

Copy From Python `Requests(EN) <http://docs.python-requests.org/en/latest/>`_ Or `Requests(CN) <http://requests-docs-cn.readthedocs.org/zh_CN/latest/user/quickstart.html>`_ .


Usage
-------------

.. code:: php
    
    <?php
        require_once 'requests.php';

        
        $url = "http://www.baidu.com/";
        $params = array('lang'=>'hans');  // http query .
        $data = array('key'=>'value', 'key2'=>'value2' );
        // GET Method
        $response = Requests::get($url, $params, array( 'header'=>array('user-agent'=>'IM/TEST', 'Content-Type'=>'application/json') ) );

        // POST Method  1 ( Dict )
        $response = Requests::post($url, $data, array( 'header'=>array('user-agent'=>'IM/TEST', 'Content-Type'=>'application/json') ) );
        // POST Method  2 ( String )
        $response = Requests::post($url, http_build_query($data), array( 'header'=>array('user-agent'=>'IM/TEST', 'Content-Type'=>'application/json') ) );

        // PUT Method
        // Note: HTTP POST 的Data数据与 HTTP PUT 模式 的 Data 数据存放位置虽然一样
        //              但是 HTTP POST 的数据编码是有规范的，及必须经过 urlencode 过的 字典序列(Dict),
        //              而 PUT 模式则没有这个限制, 为此，在这个 put 接口里面, 如果用户传参的类型为 数组，那么他将会被默认以  JSON 的格式编码
        //              而不是 POST 采用的 URLENCODE 编码
        $response = Requests::put($url, $data, array( 'header'=>array('user-agent'=>'IM/TEST', 'Content-Type'=>'application/json') ) );
        # $response = Requests::put($url, json_encode($data), array( 'header'=>array('user-agent'=>'IM/TEST', 'Content-Type'=>'application/json') ) );
        /*
            // Response
            
            Array
            (
                [code] => 200
                [reason] => OK
                [header] => Array
                    (
                        [date] => Mon, 03 Nov 2014 08:46:28 GMT
                        [server] => nginx/1.4.2
                        [content-type] => application/json; charset=utf-8
                        [content-length] => 24
                        [content-encoding] => gzip
                        [x-via] => 1.1 bjsb5:8101 (Cdn Cache Server V2.0), 1.1 xg48:5 (Cdn Cache Server V2.0)
                        [connection] => keep-alive
                    )

                [cookies] => Array
                    (
                    )

                [body] => "{}"
            )

        */
    ?>

Require
----------------

1.  `HTTP_Request2 <http://pear.php.net/package/HTTP_Request2>`_



Install
-----------

Easy Install

Pear Install

.. code:: bash
    
    pear install HTTP_Request2

Pyrus Install

Try PEAR2's installer, Pyrus.

.. code:: bash

    php pyrus.phar install pear/HTTP_Request2
