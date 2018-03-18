<?php
include("ip_blocks.class.php");

class check_ip_range {

	protected static $leased_subnets=array();

	function __construct() {
	    $ip_blocks=ip_blocks::getInstance();
	    // our networks ranges.... ipv4 and ipv6 in mix ;)
	    $my_range = "10.56.0.0/16, fc80:0000:0001::/48";
	    $my_range.= ", 10.28.0.0/24, 10.2.0.0/24, 172.16.0.0/20";
	    $my_range=str_replace(" ","",$my_range);
	    $my_range=str_replace(",",";",$my_range);
	    $a=explode(";",$my_range);
	    // prepare blocks
	    $leased_subnets=array();
	    foreach($a as $subnet) {
		$checked_subnet = self::check_ip($subnet);
		if($checked_subnet["status"]=="ok") {
		    $block_base=$checked_subnet["detail"]["network"];
		    $block_mask=$checked_subnet["detail"]["cidr_mask"];
		    $charSub  = inet_pton($block_base);
		    $charMask = $ip_blocks->_cdr2Char($block_mask);
		    $sub = $block_base."/".$block_mask;
		    $charNet = $charSub & $charMask; // Supernet network address
		    $charBcst = $charSub | ~$charMask; // Supernet broadcast
		    $leased_subnets[]=array($charSub, $charBcst , $sub);   // в таблицу складываем первый и последний адрес подсети
		} //if
	    } // foreach
	    self::$leased_subnets = $leased_subnets;
	}

	private function check_ip($subnet) {

	    $ip_blocks=ip_blocks::getInstance();
	    $block_vers="";
	    // Calculate supernet mask and cdr
	    if (ereg('/',$subnet,$mask)){  //if cidr type mask
	    } else {
		if($ip_blocks->is_ipv4($subnet)===true ) {
		    $subnet .="/32";
		    $block_vers="v4";
		} elseif($ip_blocks->is_ipv6($subnet)===true ) {
		    $subnet .="/128";
		    $block_vers="v6";
		} else {
		    return( array("status"=>'error',"msg"=>"String '".$subnet."' neither IPv4 nor IPv6 address."));
		}
	    }
	    $block_vers="";
	    // parse ip/mask here
	    $charHost = inet_pton(strtok($subnet, '/'));
	    $charMask = $ip_blocks->_cdr2Char(strtok('/'),strlen($charHost));
	    $CDRmask  = $ip_blocks->_char2Cdr($charMask);

	    $chr_address=inet_ntop($charHost)."";

	    if($ip_blocks->is_ipv4($chr_address)===true ) {
		$block_vers="v4";
	    } elseif($ip_blocks->is_ipv6($chr_address)===true ) {
		$block_vers="v6";
	    } else {
		return( array("status"=>'error',"msg"=>"String '".$chr_address."' neither IPv4 nor IPv6 address.") );
	    }
	    $report=$ip_blocks->parse_block_addr($charHost,$charMask,$block_vers);
	    return( array("status"=>'ok',"subnet"=>$subnet,"detail"=>$report) );
	}

	function check_in_range($test_ip) {
	    $ip_blocks=ip_blocks::getInstance();
	    $checked_subnet=self::check_ip($test_ip);
	    if($checked_subnet["status"]=="ok") {
		$block_base=$checked_subnet["detail"]["network"];
		$block_mask=$checked_subnet["detail"]["cidr_mask"];
		$sub = $block_base."/".$block_mask;
		$charHost = inet_pton($block_base);
		$charMask = $ip_blocks->_cdr2Char($block_mask);
		$res = $ip_blocks->check_overlaps($charHost, $charMask, self::$leased_subnets);
		return($res);
	    } else {
		return($checked_subnet);
	    }
	}
} //class
?>