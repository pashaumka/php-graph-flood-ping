<?php


class net_ping {
	var $icmp_socket;
	var $request;
	var $request_len;
	var $ping_count;
	var $stats_array;
	var $reply;
	var $errstr;
	var $time;
	var $timer_start_time;
	var $packet_data;
	var $ping_result=array();
	var $ttl = -1;
	var $srcv4="X.r.g.b";
	var $srcv6="X:Y;C:v:B::k";
	var $src_addr;
	var $ip_calc;
	var $config;

	function Net_Ping() {
	}

	function ip_checksum($data) {
	    if (strlen($data)%2)
		$data .= "\x00";
	    $bit = unpack('n*', $data);
	    $sum = array_sum($bit);
	    while ($sum >> 16)
		$sum = ($sum >> 16) + ($sum & 0xffff);
	    return pack('n*', ~$sum);
	}

	function start_time() {
	    $this->timer_start_time = microtime();
	}

	function get_time($acc=2) {
	    $end_time = explode (" ", microtime());
	    // format start time
	    $start_time = explode (" ", $this->timer_start_time);
	    $start_time = $start_time[1] + $start_time[0];
	    // get and format end time
	    $end_time = $end_time[1] + $end_time[0];
	    return number_format ($end_time - $start_time, $acc);
	}

	function Build_Packet($seq_no,$ip_protocol="ipv4") {
	    $type = "\x08"; // 8 echo message; 0 echo reply message
	    if($ip_protocol=="ipv6") {
		$type = "\x80"; // 128 echo message for ipv6
	    }
	    $code = "\x00"; // always 0 for this program
	    $chksm = "\x00\x00"; // generate checksum for icmp request
	    $id = "\x00\x00"; // we will have to work with this later
	    $id  = chr(rand(0,255)) . chr(rand(0,255));
	    $sqn =  chr(floor($seq_no/256)%256) . chr($seq_no%256);
	    // now we need to change the checksum to the real checksum
	    $chksm = $this->ip_checksum($type.$code.$chksm.$id.$sqn.$this->packet_data);
	    // now lets build the actual icmp packet
	    $this->request = $type.$code.$chksm.$id.$sqn.$this->packet_data;
	    $this->request_len = strlen($this->request);
	}


	//http://php.net/manual/ru/function.socket-create.php
	//https://github.com/clue/php-socket-raw
	function ping($dst_addr, $count_pings="3", $packet_size="3", $packet_timeout="1000", $packets_interval="1000", $percision="7",$script_timeout="10000") {

	    if($count_pings=="1") { $packets_interval="0"; }

	    if($script_timeout=="10000") {
		$script_timeout=$count_pings*($packet_timeout+$packets_interval);    // very dUUUUUmmy link
	    }

	    $strings=new strings;
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
		    "msg"=>"Длина передаваемого пакета должна біть больше 0 и меньше 1472"));
	    }

	    if($packet_size > 1472 ) {
		return(array("status"=>"error",
		    "msg"=>"Длина передаваемого пакета должна біть больше 0 и меньше 1472"));
	    }

	    if($strings->is_ipv4($dst_addr)) {
		$ip_protocol_code = getprotobyname("icmp");
	        $ip_protocol="ipv4";
		if( ($this->icmp_socket = @socket_create(AF_INET, SOCK_RAW, $ip_protocol_code))===false ) {
			return(array("status"=>"error",
				    "msg"=>"Не могу создать AF_INET RAW сокет.\nСкрипт следует запускать с правами ROOTа."));
		}
		if($this->srcv4 != "") {
		    socket_bind($this->icmp_socket,$this->srcv4);
		    $this->src_addr=$this->srcv4;
		}
		socket_set_nonblock($this->icmp_socket);
	    } elseif($strings->is_ipv6($dst_addr)) {
		/// http://php.net/manual/ru/function.socket-create.php#120174
		$ip_protocol_code = 58; //getprotobyname("ipv6-icmp");
		$ip_protocol="ipv6";
		if( ($this->icmp_socket = @socket_create(AF_INET6, SOCK_RAW, $ip_protocol_code)) === false ) {
			return(array("status"=>"error",
				    "msg"=>"Не могу создать AF_INET6 RAW сокет.\nСкрипт следует запускать с правами ROOTа."));
		}
		if($this->srcv6 != "") {
		    socket_bind($this->icmp_socket,$this->srcv6);
		    $this->src_addr=$this->srcv6;
		}
		/* check our ipv4 ranges here*/
		socket_set_nonblock($this->icmp_socket);
	    } else {
		return(array("status"=>"error",
		    "msg"=>"Строка ".$dst_addr." не содержит ни ipv4 ни ipv6 адрес!"));
	    }

	    socket_set_option($this->icmp_socket,$ip_protocol_code, $ip_ttl_code, 128);

/*	if ($this->ttl > 0) {
	    $socket_ttl = socket_get_option($this->icmp_socket,$ip_protocol_code,$ip_ttl_code);
	    //socket_set_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL,128);
	    //$socket_ttl = socket_get_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL);
	}
	else $socket_ttl = 64; // standard TTL
*/

	    $this->time="-1";
	    // lets catch dumb people
	    //if ((int)$packet_timeout <= 0) $packet_timeout = 1000;
	    if ((int)$percision <= 0) $percision = 7;
	    $ping_result = array();
	    $recv_count = $timeout_count = $num_retries = 0;

	    /* set socket receive timeout to 1 second */
	    $sock_sec = intval($packet_timeout/1000);
	    $sock_usec = $packet_timeout%1000;
	    // set the timeout
	    socket_set_option($this->icmp_socket,
		SOL_SOCKET,  // socket level
		SO_RCVTIMEO, // timeout option
		array(
		    "sec"=>$sock_sec, // Timeout in seconds
		    "usec"=>$sock_usec  // I assume timeout in microseconds
		)
	    );

	    // Генерируем пакет
	    $this->packet_data = $strings->generateStrongPassword($packet_size, false, 'luds');

	    $status="warn"; $msg="test not complete with errors";

	    $times_timeout=$ping_no="0";

	    $script_time=true;
	    $script_startTime = microtime(true);
	    $usec_script_timeout=sprintf("%.4F",$script_timeout/1000);

	    while( ( $ping_no<$count_pings ) && $script_time ) {
		$this->Build_Packet($ping_no,$ip_protocol);
		$this->start_time();

		//echo "pkt $ping_no send..";
		if( (socket_connect($this->icmp_socket, $dst_addr, null)) === false ){
		    return array("status"=>"error",
				    "msg"=>"Не могу подключиться к $dst_addr: ".socket_strerror(socket_last_error())) ;
		}

		$sent_byte = socket_send($this->icmp_socket, $this->request, $this->request_len, 0); // @

		if($sent_byte===false) {
		    // break with error;
		} else {
		    //callback
		}
		
		if($sent_byte > 1) {  // есть что принимать
		    
		    // ищем свободный сокет
		    $num = 0;
		    $timeout = 0;
		    while (($num <= 0) and ($timeout < 100))  {
			$set = array($this->icmp_socket);
			$num = socket_select($set, $s_write = NULL, $s_accept = NULL, 0, 1000);
			if ($num === false) {
			    //$this->callback("waiting on socket",socket_strerror(socket_last_error()));
			}
			$timeout++;
		    }

		    if ($num > 0) {
		        $aux = @socket_read( $this->icmp_socket, 100);
			$len= strlen($aux)-20;
			//echo "recv...";
			$ping_result[$ping_no] = $this->get_time($percision) * 1000;
			$recv_count++;
		    } else {
			// timeout select socket
			//echo "timeout...";
			$ping_result[$ping_no] = "-1";
			$timeout_count++;
		    }
		} // if sent bytes > 1

		// жждем 
		if($packets_interval!="0") {  usleep($packets_interval); }
		$ping_no++;
		if( (microtime(true) - $script_startTime) > $usec_script_timeout ) { $script_time=false; }
		//echo "\n";
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
				"src_addr"=>$this->src_addr,
				"dst_addr"=>$dst_addr,
				"timeout_count"=>$timeout_count,
				"min_delay"=>sprintf("%.3F",$indicator_min),
				"max_delay"=>sprintf("%.3F",$indicator_max),
				"avg_delay"=>sprintf("%.3F",$average),
				"packet_len"=>strlen($this->packet_data),
				"start_time"=>$script_startTime,
				"end_time"=>$script_endTime
				);
	}

	function stdPing($dst_addr, $count_pings="5", $packet_size="10") {
	    $result = $this->ping(trim($dst_addr), $count_pings, $packet_size, 1500, 1000);
	    return($result);
	}

	function FloodPing($dst_addr, $count_pings="1000",$packet_size="1472") {
	    $result = $this->ping(trim($dst_addr), $count_pings, $packet_size, 50, 2);
	    return($result);
	}

} // end class Net_Ping

?>