<?php



/*

// Note: FTP 数据通道建立采用被动模式. ( 即 FTP 服务器 通知已经建立好的数据通道，通知 客户端前往连接。 )
//       目前 FTP Message 以及 FTP CODE 并没有去详细解析。


// 华图FTP服务器
$ftp_server = '211.151.160.104';
$ftp_user_name = 'ftp';
$ftp_user_pass = 'vhuatu_2013';

    @ Doc转载自: http://blog.csdn.net/yxyhack/article/details/1826256
                 http://itec.hust.edu.cn/~liuwei/2014/2014fall-comnet/lab/lab4-TCP-FTP.pdf

FTP命令

命令对大小写不敏感。命令通常由命令码和相应的参数组成。中间由一个或几个空格分开。
参数域由<CRLF>结束，服务器在未接收到行结束符时不会采取任何动作。
下面描述的格式是以NVT-ASCII以准的，方括号代表可选的参数域，
如果未选择可选的参数域则采用默认值。


列表

下面是FTP命令，其中username代表用户名，password代表口令，pathname代表路径名，
host-port代表主机端口，account-information代表帐户信息，typecode代表类型代码，
decimal-integer代表十进制整数，marker代表标记，string代表字符串：

USER <SP> <username> <CRLF>
PASS <SP> <password> <CRLF>
ACCT <SP> <account-information> <CRLF>
CWD <SP> <pathname> <CRLF>
CDUP <CRLF>
SMNT <SP> <pathname> <CRLF>
QUIT <CRLF>
REIN <CRLF>
PORT <SP> <host-port> <CRLF>
PASV <CRLF>
TYPE <SP> <type-code> <CRLF>
STRU <SP> <structure-code> <CRLF>
MODE <SP> <mode-code> <CRLF>
RETR <SP> <pathname> <CRLF>
STOR <SP> <pathname> <CRLF>
STOU <CRLF>
APPE <SP> <pathname> <CRLF>
ALLO <SP> <decimal-integer>
[<SP> R <SP> <decimal-integer>] <CRLF>
REST <SP> <marker> <CRLF>
RNFR <SP> <pathname> <CRLF>
RNTO <SP> <pathname> <CRLF>
ABOR <CRLF>
DELE <SP> <pathname> <CRLF>
RMD <SP> <pathname> <CRLF>
MKD <SP> <pathname> <CRLF>
PWD <CRLF>
LIST [<SP> <pathname>] <CRLF>
NLST [<SP> <pathname>] <CRLF>
SITE <SP> <string> <CRLF>
SYST <CRLF>
STAT [<SP> <pathname>] <CRLF>
HELP [<SP> <string>] <CRLF>
NOOP <CRLF>



FTP命令参数

下面是用BNF范式表示的参数格式：

<username> ::= <string>
<password> ::= <string>
<account-information> ::= <string>
<string> ::= <char> | <char><string>
<char> ::= 除<CR>和<LF>外的所有ASCII字符
<marker> ::= <pr-string>
<pr-string> ::= <pr-char> | <pr-char><pr-string>
<pr-char> ::= 可打印ASCII字符，从33到126
<byte-size> ::= <number>
<host-port> ::= <host-number>,<port-number>
<host-number> ::= <number>,<number>,<number>,<number>
<port-number> ::= <number>,<number>
<number> ::= 从1到255的十进制整数
<form-code> ::= N | T | C
<type-code> ::= A [<sp> <form-code>]| E [<sp> <form-code>]| I| L <sp> <byte-size>
<structure-code> ::= F | R | P
<mode-code> ::= S | B | C
<pathname> ::= <string>
<decimal-integer> ::= 任何十进制整数


命令和响应序列

服务器和用户之间的通信是对话的过程，用户发送FTP命令，
然后等待服务器的一个（或多个）响应，根据响应再发送新命令。
连接时的响应带有许多信息，通常情况下，服务器会返回220应答，
等待输入，用户在接收到此响应后才发送新命令，如果服务器不能立即接收输入，
会在220后面返回120。有些信息如服务器将在15分钟后停止工作是要服务器发向用户的，
但是服务器却不能直接发向用户，处理的方法是将消息缓冲，在下一个响应中返回给用户。
下面列出命令的应答，第一个是预备应答，第二个是确定完成，第三个是拒绝完成，
最后是中间应答。这些应答是构成状态图的基础，状态图会在下节中给出:

建立连接
120
220
220
421

登录

USER
230
530
500, 501, 421
331, 332

PASS
230
202
530
500, 501, 503, 421
332

ACCT
230
202
530
500, 501, 503, 421

CWD
250
500, 501, 502, 421, 530, 550

CDUP
200
500, 501, 502, 421, 530, 550

SMNT
202, 250
500, 501, 502, 421, 530, 550

退出登录

REIN
120
220
220
421
500, 502

QUIT
221
500

传输参数

PORT
200
500, 501, 421, 530

PASV
227
500, 501, 502, 421, 530

MODE
200
500, 501, 504, 421, 530

TYPE
200
500, 501, 504, 421, 530

STRU
200
500, 501, 504, 421, 530

文件操作命令
ALLO
200
202
500, 501, 504, 421, 530

REST
500, 501, 502, 421, 530
350

STOR
125, 150
(110)
226, 250
425, 426, 451, 551, 552
532, 450, 452, 553
500, 501, 421, 530

STOU
125, 150
(110)
226, 250
425, 426, 451, 551, 552
532, 450, 452, 553
500, 501, 421, 530

RETR
125, 150
(110)
226, 250
425, 426, 451
450, 550
500, 501, 421, 530

LIST
125, 150
226, 250
425, 426, 451
450
500, 501, 502, 421, 530

NLST
125, 150
226, 250
425, 426, 451
450
500, 501, 502, 421, 530

APPE
125, 150
(110)
226, 250
425, 426, 451, 551, 552
532, 450, 550, 452, 553
500, 501, 502, 421, 530

RNFR
450, 550
500, 501, 502, 421, 530
350

RNTO
250
532, 553
500, 501, 502, 503, 421, 530

DELE
250
450, 550
500, 501, 502, 421, 530

RMD
250
500, 501, 502, 421, 530, 550

MKD
257
500, 501, 502, 421, 530, 550

PWD
257
500, 501, 502, 421, 550

ABOR
225, 226
500, 501, 502, 421

获得信息命令

SYST
215
500, 501, 502, 421

STAT
211, 212, 213
450
500, 501, 502, 421, 530

HELP
211, 214
500, 501, 502, 421

其它命令

SITE
200
202
500, 501, 530
NOOP
200
500 421
    
*/

class FTP{
    
    public static $socket      = null; // 命令通道
    public static $data_socket = null; // 数据通道
    public static $user        = '';   // FTP帐号
    public static $password    = '';   // FTP密码


    public function __construct ($host='211.151.160.104', $port=21, $user='ftp', $password='vhuatu_2013', $timeout=60){
        // init socket .
        self::$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect( self::$socket, $host, $port );
        self::$user = $user;
        self::$password = $password;
    }

    /*
    public function connect ($host='211.151.160.104', $port=21, $user='ftp', $password='vhuatu_2013', $timeout=60){

    }
    */

    // SOCKET 操作
    public function _send ($buff, $socket=''){
        // send data to socket.
        if ( $socket == '' ) $socket = self::$socket;
        if ( gettype($socket) != 'resource' ) return false;

        if ( $buff{strlen($buff)-2} . $buff{strlen($buff)-1}  == "\r\n" ) $buff .= "\r\n";
        return socket_write($socket, $buff, strlen($buff) );
    }

    public function _read($socket=''){
        // read data from socket.
        if ( $socket == '' ) $socket = self::$socket;
        if ( gettype($socket) != 'resource' ) return false;
        $buff = "";
        $run = true;
        while ( $run == true ) {
            $tmp = socket_read($socket, 9216);
            $buff .= $tmp;
            if ( strlen($buff) > 3 ) {
                $last_words = $buff{strlen($buff)-2} .   $buff{strlen($buff)-1};
                if ( $last_words == "\r\n" ) $run = False;

            }
        }
        print_r($buff);
        return $buff;
    }


    // FTP Message 解析
    public function _parse ($message){
        $response = array('code'=>0, 'message'=>'');
        //if ( preg_match('/^[0-9]{3} /', $line) ) break;  // end flag.

    }

    // FTP 命令操作
    public function login (){
        // ftp login with passwd.
        $user = self::$user;
        $password = self::$password;
        $this->_send("USER {$user}\r\n");
        $this->_read();
        // if code 230 in response , mean is success.
        // DO NOT CHECK!
        $this->_send("PASS {$password}\r\n");
        $this->_read();
        
        $this->_send("PASV\r\n");
        $response = $this->_read();
        // 227 Entering Passive Mode (127,0,0,1,54,255)
        try{
            if ( !preg_match('/\((?P<host>[0-9,]+),(?P<port1>[0-9]+),(?P<port2>[0-9]+)\)/', $response, $matches) ){
                return false;
            }
            $data_host = strtr($matches['host'], ',', '.');
            $data_port = ( $matches['port1'] * 256 ) + $matches['port2']; // low bit * 256 + high bit
            //echo ":: Data SOCKET: {$data_host}:{$data_port} ...\n";
            self::$data_socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP);
            socket_connect( self::$data_socket, $data_host, $data_port );
            return true;
        }catch(Exception $e) {
            return false;
        }

    }

    public function _put ($file_name, $file_data){
        $this->_send("STOR {$file_name}\r\n");
        $this->_read();  // 150 Ok to send data.\r\n
        $this->_send($file_data, self::$data_socket);

        return $this->_read();  // 226 File receive OK.\r\n
    }


    public function _get (){

    }

    public function _pwd (){
        $this->_send("PWD\r\n");
        $this->_read();
        return $this->_read(self::$data_socket);

    }

    public function _lpwd (){

    }

    public function _chdir($path){
        $this->_send("CWD {$path}\r\n");
        return $this->_read();
    }

    public function _lchdir(){

    }

    public function _ls ($path=''){
        if ( $path == '' ) $CMD ="LIST\r\n";
        else $CMD = "LIST {$path}\r\n";
        $this->_send( $CMD );
        $this->_read();
        $this->_read();
        return $this->_read(self::$data_socket);
    }

    public function _lls (){

    }


    public function __destruct (){
        try{
            socket_close(self::$socket);
            socket_close(self::$data_socket);
        }catch ( Exception $e ){
            // do nothing...
        }
    }
}



function test_ftp(){
    // ftp test
    $ftp = new FTP();
    echo $ftp->login();
    #echo $ftp->_ls();
    //echo $ftp->_pwd();
    //echo $ftp->_ls();
    echo $ftp->_chdir("/var/www/cdn");
    echo $ftp->_put('php_test.name', 'hahahaha');  // file name  && file data( String )

    /////////
    echo $this->_send("LIST /var/\r\n");
    echo $this->_read();
    echo $this->_read($ftp::$data_socket);

    //var_dump($res);

    //echo $ftp->_read();
}


//test_ftp();


?>