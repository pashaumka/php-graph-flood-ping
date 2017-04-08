<?php

// based io ideas  https://github.com/djamps/php-ipv6-calculator/blob/master/ipcalc.php

class ip_blocks {

	public $url="/system/ip_blocks";

	private $mysqli;
	
	const	ipv4 = "v4";
	const	ipv6 = "v6";

	private static $_instance; //The single instance

	public static function getInstance() {
	    if(!self::$_instance) { // If no instance then make one
	        self::$_instance = new self();
	        }
	        return self::$_instance;
	}

	function __construct() {
	    //$this->mysqli = database::getInstance();
	}


	function parse_block_addr($charHost="",$charMask="",$block_vers="") {
	    if( ($charHost=="") OR ($charMask=="") OR ($block_vers=="") ) { return; }
	    // Single host mask used for hostmin and hostmax bitwise operations
	    $charHostMask = substr(self::_cdr2Char(127),-strlen($charHost));

	    $charWC = ~$charMask; // Supernet wildcard mask
	    $charNet = $charHost & $charMask; // Supernet network address
	    $charBcst = $charNet | ~$charMask; // Supernet broadcast
	    $charHostMin = $charNet | ~$charHostMask; // Minimum host
	    $charHostMax = $charBcst & $charHostMask; // Maximum host

	    $ip=array();
	    if($block_vers=="v4") {
		$host_total=pow(2, (32-self::_char2Cdr($charMask))); 
		$ip["num_ip"]=$host_total;
	    
	    }

	    if($block_vers=="v6") {
		$host_total= pow(2, (64-self::_char2Cdr($charMask)));
		$ip["prefixes_64"]=$host_total;
	    }

	    $ip["block_vers"]=$block_vers;
	    $ip["network"]=inet_ntop($charNet);
	    $ip["cidr_mask"]=self::_char2Cdr($charMask);
	    $ip["subnet_mask"]=inet_ntop($charMask);
	    $ip["wildcard"]=inet_ntop($charWC);
	    if($block_vers=="v6") {
		$ip["block_base"]=self::_hex2ipv6(bin2hex($charNet));
		$ip["block_max"]=self::_hex2ipv6(bin2hex($charBcst));
		$ip["host_min"]=self::_hex2ipv6(bin2hex($charHostMin));
		$ip["host_max"]=self::_hex2ipv6(bin2hex($charHostMax));
	    } else {
		$ip["block_base"]=inet_ntop($charNet);
		$ip["block_max"]=inet_ntop($charBcst);
		$ip["host_min"]=inet_ntop($charHostMin);
		$ip["host_max"]=inet_ntop($charHostMax);
	    }
	    $ip["char_net"]=$charNet;
	    $ip["char_mask"]=$charMask;
	    $ip["char_bcst"]=$charBcst;
	    return($ip);
	}

	/*
	 проверка диапазонов на пересечение. сеть/маска к массиву сетей ( прямое и обратное )
	 $f_block_base - char формат
	 $f_block_mask - char формат
	 $block_vers
	*/
	/*function check_block_ranges( $f_block_base , $f_block_mask, $block_vers ) {   // human_readable ?
	    $err_arr=array();
	    $err_arr["status"] = "ok";

	    $leased_subnets=array();
	    $sql="SELECT `block_base`, `block_mask` from `ip_blocks` WHERE `block_vers`='".$block_vers."'";
	    $pack_name_req=$this->mysqli->db_query("ip_manager",$sql);
	    while ($p_rowvar=$pack_name_req->fetch_array()) {
		$block_base=$p_rowvar["block_base"];   // 192.168.0.0
		$block_mask=$p_rowvar["block_mask"];   // 24 - cidr format
		$charNet = inet_pton($block_base);
		$charMask = self::_cdr2Char($block_mask);
		$sub = $block_base."/".$block_mask;
		$charNet = $charNet & $charMask; // Supernet network address
		$charBcst = $charNet | ~$charMask; // Supernet broadcast
		//echo $sub."<br>\n";
		$leased_subnets[]=array($charNet,$charBcst,$sub);
	    }
	    $pack_name_req->free_result();
	    $res=self::check_overlaps($f_block_base,$f_block_mask,$leased_subnets);
	    return($res);
	}*/


	function is_ipv4($ip) {
	    // The regular expression checks for any number between 0 and 255 beginning with a dot (repeated 3 times)
	    // followed by another number between 0 and 255 at the end. The equivalent to an IPv4 address.
	    //It does not allow leading zeros
	    return (bool) preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])'.
	            '\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])$/', $ip);
	}

	function is_ipv6($ip) {
	    return (bool) preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\da-fA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})\z/i',$ip);
	    //return (bool) preg_match("/^[0-9a-f]{1,4}:([0-9a-f]{0,4}:){1,6}[0-9a-f]{1,4}$/", $ip);
	}

	// проверка пересечений адреса с массивом подсетей
	// $charSub,$charSubMask - невый и последний адрес диапазона, приседенный в бинарный вид
	// $leased_subnets - массив непересекающихся адресов в формате
	//      первый адрес сети	- binary packed    pton
	//      последний адрес сети	- binary packed
	//      подсеть/cidr
	function check_overlaps($charSub,$charSubMask,$leased_subnets) {
		
		$err_arr=array();
		$subNet = $charSub & $charSubMask; // Supernet network address
		$subBcst = $charSub | ~$charSubMask; // Supernet broadcast
		foreach($leased_subnets as $subnet_info) {
		    $first_in_range = $subnet_info[0];
		    $last_in_range = $subnet_info[1];
		    $sub = $subnet_info[2];
		    if ((strlen($charSub) == strlen($first_in_range)) &&
			($subNet >= $first_in_range && $subBcst <= $last_in_range)) {
			// In range
			$err_arr["status"]="overlap";
			$err_arr["block_base"]="main перекрывается блоком ".$sub."";
			return ($err_arr);
		    }
		} // foreach
		$text = "";
		// do reverse checking
		foreach($leased_subnets as $subnet_info) {
		    $first_in_range = $subnet_info[0];
		    $last_in_range = $subnet_info[1];
		    $sub = $subnet_info[2];
		    if ( ( strlen($first_in_range)==strlen($subNet) ) &&
			($first_in_range >= $subNet && $last_in_range <= $subBcst )) {
			// In range
			$text .= "rev перекрывается блоком ".$sub."";
		    }
		}
		if($text!="") {
			$err_arr["status"]="overlap";
			$err_arr["block_base"]=$text;
			return ($err_arr);
		}
	    $err_arr["status"]="free";
	    return($err_arr);
	}

    // Convert array of short unsigned integers to binary
    function _packBytes($array) {
	$chars ="";
	foreach ( $array as $byte ) {
	    $chars .= pack('C',$byte);
	}
	return $chars;
    }
    // Convert binary to array of short integers
    function _unpackBytes($string) {
	return unpack('C*',$string);
    }

// Add array of short unsigned integers
    function _addBytes($array1,$array2) {
	$result = array();
	$carry = 0;
	foreach ( array_reverse($array1,true) as $value1 ) {
	    $value2 = array_pop($array2);
	    if ( empty($result) ) { $value2++; }
	    $newValue = $value1 + $value2 + $carry;
	    if ( $newValue > 255 ) {
		$newValue = $newValue - 256;
		$carry = 1;
	    } else {
		$carry = 0;
	    }
	    array_unshift($result,$newValue);
	}
	return $result;
    }

    /* Useful Functions */

    function _cdr2Bin ($cdrin,$len=4){
	if ( $len > 4 || $cdrin > 32 ) { // Are we ipv6?
		return str_pad(str_pad("", $cdrin, "1"), 128, "0");
	} else {
	  return str_pad(str_pad("", $cdrin, "1"), 32, "0");
	}
	}

    function _bin2Cdr ($binin){
	return strlen(rtrim($binin,"0"));
    }

    function _cdr2Char ($cdrin,$len=4){
	$hex = self::_bin2Hex(self::_cdr2Bin($cdrin,$len));
	return self::_hex2Char($hex);
    }

    function _char2Cdr ($char){
	$bin = self::_hex2Bin(self::_char2Hex($char));
	return self::_bin2Cdr($bin);
    }

    function _hex2Char($hex){
	return pack('H*',$hex);
    }

    function _char2Hex($char){
	$hex = unpack('H*',$char);
	return array_pop($hex);
    }

    function _hex2Bin($hex){
        $bin='';
        for($i=0;$i<strlen($hex);$i++)
            $bin.=str_pad(decbin(hexdec($hex{$i})),4,'0',STR_PAD_LEFT);
        return $bin;
    }

    function _bin2Hex($bin){
        $hex='';
        for($i=strlen($bin)-4;$i>=0;$i-=4)
        $hex.=dechex(bindec(substr($bin,$i,4)));
        return strrev($hex);
    }

    function _hex2ipv6($str) {
	$hex = "";
	$i = 0; $j = 0; $k = 0;
	do {
	    $hex .= $str[$i];
	    $i++;
	    $j++;
	    if($j == 4 ) {
		if($k<7) {
		    $hex .= ":";
		    $k++;
		}
		$j = 0;
	    }
	} while ($i < strlen($str));
	return $hex;
    }


}
