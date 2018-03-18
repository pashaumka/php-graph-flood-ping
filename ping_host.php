<?php

//    $_GET["ip"]="10.2.68.238";

    if(isset($_GET["ip"]) && $_GET["ip"] ) {
	$ip_addr=$_GET["ip"];
    } else {
	$result=array("status"=>"error","msg"=>"В скрипт не передан адрес опрашиваемого устройства");
	echo json_encode($result);
	return;
    }

    $pkt_len="1472";

    if(isset($_GET["pktlen"]) && ($_GET["pktlen"])) {
	$pkt_len=$_GET["pktlen"];
	if( $pkt_len>"1472") $pkt_len="1472";
	if( $pkt_len<"2") $pkt_len="2";
    }

    /* Проверка на валидность наших IP адресов */
    include("check_ip_range.class.php");
    include("net_ping.class.php");

    $ip_range=new check_ip_range;
    /* Проверка на валидность наших IP адресов */
    $ip_result=$ip_range->check_in_range($ip_addr);
    if($ip_result["status"]=="free") {
	$result=array("status"=>"error","msg"=>"Делать FLOOD PING вне разрешенных IP адресов ЗАПРЕЩЕНО!");
    } else {
	//  ip_result == overlap
	$ping = new net_ping;
	$ping -> set_ipv4_addr("10.2.68.1");			// <-  поменять на свои
	$ping -> set_ipv6_addr("fc80:6f7c:1f0::68:1");		// <-  поменять на свои
	$result = $ping->stdPing( $ip_addr, "3", $pkt_len,"6000");
	if( ($result["sent"]!="0" ) && ( $result["recv"]=="0") ) {
	     $result=array(	"status"=>"error",
			    "packet_len"=>$result["packet_len"],
			    "src_addr"=>$result["src_addr"],
			    "dst_addr"=>$result["dst_addr"],
			    "sent"=>$result["sent"],
			    "recv"=>$result["recv"],
			    "ip_proto"=>$result["ip_proto"],
			    "timeout_count"=>$result["timeout_count"],
			    "start_time"=>$result["start_time"],
			    "end_time"=>$result["end_time"],
			    "msg"=>"Опрашиваемый адрес не отвечает. \nВозможно, включен фаервол или нет связи с устройством");
	}
    }

    //print_r($result);
    echo json_encode($result);
?>