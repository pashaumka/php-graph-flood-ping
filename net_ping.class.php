<?php
// http://docshare04.docshare.tips/files/16769/167696814.pdf
// https://www.slideshare.net/auroraeosrose/socket-programming-with-php
class net_ping {
	private static $icmp_socket;
	private static $request;
	private static $request_len;
	private static $ping_count;
	private static $stats_array;
	private static $reply;
	private static $errstr;
	private static $time;
	private static $timer_start_time;
	private static $packet_data;
	private static $ping_result=array();
	private static $ttl = -1;
	private static $srcv4;
	private static $srcv6;
	private static $src_addr;
	private static $config;

	private static $unique_packet_id;
	private static $sqn;
    
	public function set_ipv4_addr($ip) {

	    if (!$ip)
		return array("status"=>"error", "msg"=>"Не указан IPv4 адрес");
	
	    if (ereg('/',$ip))   //if cidr type mask
		return(array("status"=>"error", "msg"=>"Только адрес устройства, а не подсети :)"));
	
	    if( self::is_ipv4($ip) === false )
		return(array("status"=>"error", "msg"=>"Это не IPv4 адрес"));
	
	    self::$srcv4 = $ip;
	}

	public function set_ipv6_addr($ip) {
	
	    if (!$ip)
		return array("status"=>"error", "msg"=>"Не указан IPv6 адрес");
	
	    if (ereg('/',$ip))   //if cidr type mask
		return(array("status"=>"error", "msg"=>"Только адрес устройства, а не подсети :)"));
	
	    if( self::is_ipv6($ip) === false )
		return(array("status"=>"error", "msg"=>"Это не IPv6 адрес"));
	
	    self::$srcv6 = $ip;
	}


	private static function is_ipv4($ip) {
	    // The regular expression checks for any number between 0 and 255 beginning with a dot (repeated 3 times)
	    // followed by another number between 0 and 255 at the end. The equivalent to an IPv4 address.
	    //It does not allow leading zeros
	    return (bool) preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])'.
	            '\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])$/', $ip);
	}

	private static function is_ipv6($ip) {
	    return (bool) preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\da-fA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i',$ip);
	    //return (bool) preg_match("/^[0-9a-f]{1,4}:([0-9a-f]{0,4}:){1,6}[0-9a-f]{1,4}$/", $ip);
	}

	// Generates a strong password of N length containing at least one lower case letter,
	// one uppercase letter, one digit, and one special character. The remaining characters
	// in the password are chosen at random from those four sets.
	//
	// The available characters in each set are user friendly - there are no ambiguous
	// characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
	// makes it much easier for users to manually type or speak their passwords.
	//
	// Note: the $add_dashes option will increase the length of the password by
	// floor(sqrt(N)) characters.
	//  https://gist.github.com/tylerhall/521810
	private static function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds') {
	    srand();
	    $sets = array();
	    if(strpos($available_sets, 'l') !== false)
		$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	    if(strpos($available_sets, 'u') !== false)
	    	$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	    if(strpos($available_sets, 'd') !== false)
	    	$sets[] = '23456789';
	    if(strpos($available_sets, 's') !== false)
		$sets[] = '!@#$%&*?';

	    $all = '';
	    $password = '';
	    foreach($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	    }

	    $all = str_split($all);
	    for($i = 0; $i < $length - count($sets); $i++)
		$password .= $all[array_rand($all)];

	    $password = str_shuffle($password);

	    if(!$add_dashes)
		return $password;

	    $dash_len = floor(sqrt($length));
	    $dash_str = '';
	    while(strlen($password) > $dash_len) {
		$dash_str .= substr($password, 0, $dash_len) . '-';
		$password = substr($password, $dash_len);
	    }
	    $dash_str .= $password;
	    return $dash_str;
	}



	private static function ip_checksum($data) {
	    if (strlen($data)%2)
		$data .= "\x00";
	    $bit = unpack('n*', $data);
	    $sum = array_sum($bit);
	    while ($sum >> 16)
		$sum = ($sum >> 16) + ($sum & 0xffff);
	    return pack('n*', ~$sum);
	}

	private static function start_time() {
	    self::$timer_start_time = microtime();
	}

	private static function get_time($acc=4) {
	    $end_time = explode (" ", microtime());
	    // format start time
	    $start_time = explode (" ", self::$timer_start_time);
	    $start_time = $start_time[1] + $start_time[0];
	    // get and format end time
	    $end_time = $end_time[1] + $end_time[0];
	    return number_format ($end_time - $start_time, $acc);
	}

	private static function Build_Packet($seq_no, $ip_protocol="ipv4") {
	    $type = "\x08"; // 8 echo message; 0 echo reply message
	    if($ip_protocol=="ipv6") {
		$type = "\x80"; // 128 echo message for ipv6
	    }
	    $code = "\x00"; // always 0 for this program
	    $chksm = "\x00\x00"; // generate checksum for icmp request
	    $id = "\x00\x00"; // we will have to work with this later
	    self::$unique_packet_id  = chr(rand(0,255)) . chr(rand(0,255));
	    self::$sqn =  chr(floor($seq_no/256)%256) . chr($seq_no%256);
	    // now we need to change the checksum to the real checksum
	    $chksm = self::ip_checksum($type . $code . $chksm . self::$unique_packet_id . self::$sqn . self::$packet_data);
	    // now lets build the actual icmp packet
	    self::$request = $type . $code . $chksm . self::$unique_packet_id . self::$sqn . self::$packet_data;
	    $request_len = strlen(self::$request);
	    if($ip_protocol=="ipv4") {
		self::$request_len = $request_len + 20;
	    } else {
		self::$request_len = $request_len;
	    }
	}


	//http://php.net/manual/ru/function.socket-create.php
	//https://github.com/clue/php-socket-raw
	private static function ping($dst_addr, $count_pings="3", $packet_size="3", $packet_timeout="1000", $packets_interval="1000", $percision="7",$script_timeout="10000") {

	    if($count_pings=="1") { $packets_interval="0"; }

	    if($script_timeout=="10000") {
		$script_timeout = $count_pings * ( $packet_timeout + $packets_interval );    // very dUUUUUmmy link
	    }

	    //$hexdump=new hexdump;
	    $ip_ttl_code = 7;

	    $false_timeout="20";
	    if($count_pings<="3") {
		$false_timeout=$count_pings;
	    }


	    if (!$dst_addr) {
		return array("status"=>"error",
			"msg"=>"Не указан IPv4/IPv6 адрес опрашиваемого устройства!");
	    }

	    if (ereg('/',$dst_addr)){  //if cidr type mask
		return(array("status"=>"error",
		    "msg"=>"Только адрес устройства, а не подсети :)"));
	    }

	    if($packet_size < 1 ) {
		return(array("status"=>"error",
		    "msg"=>"Длина передаваемого пакета должна быть больше 0 и меньше 1472"));
	    }

	    if($packet_size > 1472 ) {
		return(array("status"=>"error",
		    "msg"=>"Длина передаваемого пакета должна быть больше 0 и меньше 1472"));
	    }

	    if(self::is_ipv4($dst_addr)) {
		$ip_protocol_code = getprotobyname("icmp");
	        $ip_protocol="ipv4";
		if( (self::$icmp_socket = @socket_create(AF_INET, SOCK_RAW, $ip_protocol_code))===false ) {
			return(array("status"=>"error",
				    "msg"=>"Не могу создать AF_INET RAW сокет.\nСкрипт следует запускать с правами ROOTа."));
		}
		if( self::$srcv4 ) {
		    if( (@socket_bind(self::$icmp_socket, self::$srcv4)) === false ){
			return(array("status"=>"error",
				    "msg"=>"Указываемый вами ipv4 адрес '".self::$srcv4."' как источник опроса \n не присутствует ни на одном интерфейсе данного сервера: ".socket_strerror(socket_last_error()))) ;
		    }
		    self::$src_addr = self::$srcv4;
		} else {
			return(array("status"=>"error", "msg"=>"Не задан IPv4 адрес источника."));
		}
		socket_set_nonblock(self::$icmp_socket);
		// Установим TTL для пакетов
		if( (@socket_set_option(self::$icmp_socket, $ip_protocol_code, $ip_ttl_code, 128)) === false ){
			return(array("status"=>"error",
				    "msg"=>"Не могу установить для сокета опции: '".socket_strerror(socket_last_error())."'")) ;
		}

	    } elseif(self::is_ipv6($dst_addr)) {
		/// http://php.net/manual/ru/function.socket-create.php#120174
		$ip_protocol_code = 58; //getprotobyname("ipv6-icmp");
		$ip_protocol="ipv6";
		if( (self::$icmp_socket = @socket_create(AF_INET6, SOCK_RAW, $ip_protocol_code)) === false ) {
			return(array("status"=>"error",
				    "msg"=>"Не могу создать AF_INET6 RAW сокет.\nСкрипт следует запускать с правами ROOTа."));
		}
		if( self::$srcv6 ) {
		    if( (@socket_bind(self::$icmp_socket, self::$srcv6)) === false ){
			return(array("status"=>"error",
				    "msg"=>"Указываемый вами ipv6 адрес '".self::$srcv6."' как источник опроса \n не присутствует ни на одном интерфейсе данного сервера: ".socket_strerror(socket_last_error()))) ;
		    }
		    self::$src_addr = self::$srcv6;
		} else {
			return(array("status"=>"error", "msg"=>"Не задан IPv6 адрес источника."));
		}
		/* check our ipv4 ranges here*/
		socket_set_nonblock(self::$icmp_socket);
		// Установим TTL для пакетов
		//if( (@socket_set_option(self::$icmp_socket, getprotobyname("ipv6-icmp"), $ip_ttl_code, 128)) === false ){
		//	return(array("status"=>"error",
		//		    "msg"=>"Не могу установить для сокета опции: '".socket_strerror(socket_last_error())."'")) ;
		//}

	    } else {
		return(array("status"=>"error",
		    "msg"=>"Строка '".$dst_addr."' не является ни ipv4 ни ipv6 адресом!"));
	    }

	    self::$time="-1";
	    // lets catch dumb people
	    //if ((int)$packet_timeout <= 0) $packet_timeout = 1000;
	    if ((int)$percision <= 0) $percision = 7;
	    $ping_result = array();
	    $recv_count = $timeout_count = $num_retries = 0;

	    /* set socket receive timeout to 1 second */
	    $sock_sec = intval($packet_timeout/1000);
	    $sock_usec = $packet_timeout%1000;
	    // Установим таймаут ожидания ответного пакета
	    socket_set_option(self::$icmp_socket,
		SOL_SOCKET,  // socket level
		SO_RCVTIMEO, // timeout option
		array(
		    "sec"  => $sock_sec, // Timeout in seconds
		    "usec" => $sock_usec  // I assume timeout in microseconds
		)
	    );

	    $status="warn"; $msg="test not complete with errors";

	    $times_timeout=$ping_no="0";

	    $script_time = true;
	    $script_startTime = microtime(true);
	    $usec_script_timeout = sprintf("%.4F",$script_timeout/1000);

	    while( ( $ping_no<$count_pings ) && $script_time ) {
		// Генерируем пакет с произвольными данными
		self::$packet_data = self::generateStrongPassword($packet_size, false, 'luds');
		self::Build_Packet($ping_no,$ip_protocol);

		self::start_time();

		if( (@socket_connect(self::$icmp_socket, $dst_addr, null)) === false ){
		    return array("status"=>"error",
				    "msg"=>"Не могу подключиться к опрашиваемому адресу '".$dst_addr."': ".socket_strerror(socket_last_error())) ;
		}

		if( ( $sent_byte = @socket_send(self::$icmp_socket, self::$request, self::$request_len, 0) )===false) {
		    // break with error;
		    return array("status"=>"error",
				    "msg"=>"Не могу отослать пакет '".$dst_addr."': ".socket_strerror(socket_last_error())) ;
		}
		
		if($sent_byte > 1) {  // есть что принимать
		    // ищем свободный сокет
		    $num = 0;
		    $timeout = 0;
		    while (($num <= 0) and ($timeout < 100))  {
			$set = array(self::$icmp_socket);
			$num = socket_select($set, $s_write = NULL, $s_accept = NULL, 0, 1000);
			if ($num === false) {
			}
			$timeout++;
		    }

		    if ($num > 0) {
		        if( ($aux = @socket_read( self::$icmp_socket, self::$request_len)) === false ) {
				$ping_result[$ping_no] = "-1"; // данные не получены
			} else {
			    $packet_time = self::get_time($percision) * 1000;
			    if($ip_protocol=="ipv4") {
				// можем проаерить контрольную сумму пакета
				// First clear off the IP protocol data
				$packet = substr($aux, 20);
				$data = unpack(
				    'Ctype/Ccode/nchecksum/nid/nsequence/C*message',
				    $packet );
				if( $data['type'] !== 0x00 ) {
				    $ping_result[$ping_no] = "-2"; //Type should be 0x00 (Echo Reply)
				} elseif ( $data['id'] != self::$unique_packet_id ) {
				    $ping_result[$ping_no] = "-3"; //Reply received was not for this request
				}
				$packet[2] = pack('C', 0x00);
				$packet[3] = pack('C', 0x00);
				$checksum  = unpack('nchecksum', self::ip_checksum($packet));
				if ($data['checksum'] !== $checksum['checksum']) {
				    $ping_result[$ping_no] = "-4"; //Reply received was not for this request
				} else {
				    $ping_result[$ping_no] = $packet_time;
				    $recv_count++;
				}
			    } elseif($ip_protocol=="ipv6") {
				// " The IPv4 Header Checksum was removed in IPv6. "
				// https://www.cisco.com/c/en/us/about/press/internet-protocol-journal/back-issues/table-contents-13/ipv6-internals.html
				$ping_result[$ping_no] = $packet_time;
				$recv_count++;
			    }

			}
		    } else {
			// timeout select socket
			$ping_result[$ping_no] = "-1";
			$timeout_count++;
		    }
		} // if sent bytes > 1

		// жждем 
		if($packets_interval!="0") {  usleep($packets_interval); }
		$ping_no++;
		if( (microtime(true) - $script_startTime) > $usec_script_timeout ) { $script_time=false; }
	    }  // while
	    $script_endTime = microtime(true);

	    // calculate min/max/avg rtt
	    $indicator_min = $indicator_max = $average = 0;
	    if($recv_count > 0 ) {
		$indicator_min = $ping_result[0];
		$indicator_max = $ping_result[0];
		foreach($ping_result as $value) {
		    // calc min in > 0
		    if($value>=0) {
			if($indicator_min>$value) $indicator_min=$value;
			//calc avg
			$average = $average+$value;
			// calc max
			if($indicator_max<$value) $indicator_max=$value;
		    }
		}
		$average = $average/$recv_count;
	    }

	    $status="done"; $msg="test complete";

	    return 	array(	"status"=>$status,
				"msg"=>$msg,
				"states"=>$ping_result,
				"sent"=>$count_pings,
				"recv"=>$recv_count,
				"ip_proto"=>$ip_protocol,
				"src_addr"=>self::$src_addr,
				"dst_addr"=>$dst_addr,
				"timeout_count"=>$timeout_count,
				"min_delay"=>sprintf("%.4F",$indicator_min),
				"max_delay"=>sprintf("%.4F",$indicator_max),
				"avg_delay"=>sprintf("%.4F",$average),
				"packet_len"=>strlen(self::$packet_data),
				"start_time"=>$script_startTime,
				"end_time"=>$script_endTime
				);
	}

	public function stdPing($dst_addr, $count_pings="5", $packet_size="10") {
	    $result = self::ping(trim($dst_addr), $count_pings, $packet_size, 1500, 1000);
	    return($result);
	}

	public function FloodPing($dst_addr, $count_pings="1000",$packet_size="1472") {
	    $result = self::ping(trim($dst_addr), $count_pings, $packet_size, 50, 2);
	    return($result);
	}

} // end class Net_Ping

?>