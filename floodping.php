<?php
#  umka@localka.net
#
#  floodping.class.php version 0.1 beta
#  script working with ipv4 and with ipv6 addresses
#
#  this script required root permissions, Run php5-fpm with root rights
#  user=root, group=root and potions with start  --allow-to-run-as-root
#  or more graceful
#
#  if u found bugs or mistakes - send me on umka@localka.net
#
#  echo "<img src='http://our.host.addr/ping.class.php?host=".$framedip."'>";
#  if need &pktlen=1472
#
#  todo
#  * check ttl 
#
/*
    my config nginx  /путь/к/скрипту/billing_tools/

    in nginx:

        #working php with root access
        location /billing_tools {
                root /path/to/script/;
                index index.php index.html index.htm;
                location ~ ^/billing_tools/(.+\.php)$ {
                        #try_files      $uri =404;
                        root            /путь/к/скрипту/;
                        #fastcgi_pass   php-fpm;
                        fastcgi_pass    127.0.0.1:9099;
                        fastcgi_index   index.php;
                        fastcgi_param   SCRIPT_FILENAME $request_filename;
                        include         /etc/nginx/fastcgi_params;
                }
                location ~* ^/billing_tools/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
                        root /path/to/script/with/images/;
                }

            access_log  /path/to/logs/access.log main;
            error_log   /path/to/logs/error.log warn;
        }


    in php-fpm in pool create pool with root access
        user=root
        group=root
        listen=127.0.0.1:9099;   // for example
*/

//include("/stat/web/Global_settings.php");

class Net_Ping {
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
	var $timeout = 1;
	var $srcv4="10.22.0.1";
	var $srcv6="fc80::1/64";
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
	function ping($count_pings="1000",$dst_addr,$timeout="1000",$percision="7",$packet_size="1472") {

	    $strings=new strings;
	    $ip_ttl_code = 7;
	    
	    $false_timeout="20";
	    if($count_pings<="3") {
		$false_timeout=$count_pings;
	    }

	    if (ereg('/',$dst_addr)){  //if cidr type mask
		return(array("status"=>"error",
		    "msg"=>"Only ip host/device. Not subnet :)"));
	    }

	    if($strings->is_ipv4($dst_addr)) {
		$ip_protocol_code = getprotobyname("icmp");
	        $ip_protocol="ipv4";
		if( ($this->icmp_socket = @socket_create(AF_INET, SOCK_RAW, $ip_protocol_code))===false ) {
			return(array("status"=>"error",
				    "msg"=>"can't create AF_INET RAW socket.\n Root permissions requred."));
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
				    "msg"=>"Can't create AF_INET6 RAW socket.\n Root permissions requred."));
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

//	$socket_ttl = socket_get_option($this->socket,$ip_protocol_code,$ip_ttl_code);
	
	//for ($a=0; $a<64; $a++)
	//	echo $a." - ".@socket_get_option($socket,$ip_protocol_code,$a)."\n";

/*	if ($this->ttl > 0) {
	    socket_set_option($this->icmp_socket,$ip_protocol_code,$ip_ttl_code,128);
	    $socket_ttl = socket_get_option($this->icmp_socket,$ip_protocol_code,$ip_ttl_code);
	    //socket_set_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL,128);
	    //$socket_ttl = socket_get_option($socket,Socket::IPPROTO_IP,Socket::IP_TTL);
	}
	else $socket_ttl = 64; // standard TTL
*/
	    $this->time="-1";
	    // lets catch dumb people
	    if ((int)$timeout <= 0) $timeout=5;
	    if ((int)$percision <= 0) $percision=3;
	    $ping_result=array();
	    $recv_count=$timeout_count=$num_retries=0;

	    /* set socket receive timeout to 1 second */
	    $sec = intval($timeout/1000);
	    $usec = $timeout%1000;
	    // set the timeout
	    socket_set_option($this->icmp_socket,
		SOL_SOCKET,  // socket level
		SO_RCVTIMEO, // timeout option
		array(
		    "sec"=>$sec, // Timeout in seconds
		    "usec"=>$usec  // I assume timeout in microseconds
		)
	    );

	    if( (socket_connect($this->icmp_socket, $dst_addr, null)) === false ){
		return array("status"=>"error",
		    "msg"=>"Can't connect to $dst_addr: ".socket_strerror(socket_last_error())) ;
	    }

	    if($packet_size) {
		$this->packet_data=$strings->generateStrongPassword($packet_size, false,'luds');
	    } else {
		$this->packet_data="abcdefghijklmnopqrstuvwabcdefghi";
	    }

	    $times_timeout=$ping_no="0";

	    $script_startTime = microtime(true); $script_timeout=true;
	    while( ( $ping_no<$count_pings ) && $script_timeout ) {

		$this->Build_Packet($ping_no,$ip_protocol);
		$this->start_time();
		$startTime = microtime(true); // need this for the looping section
		socket_send($this->icmp_socket, $this->request, $this->request_len,0); // @
		// Read Data
		$keepon=true;
		while( (false===($echo_reply=@socket_read($this->icmp_socket, 255))) && $keepon) { // @socket_read
		    if ( ( microtime(true) - $startTime ) > $this->timeout )  {
			$keepon=false;
			$times_timeout++;
			if($times_timeout >= $false_timeout) {
			    if($num_retries=="0") {
				return array("status"=>"error",
				    "msg"=>"The host did not respond 20 times in a row. Check host is accessible and firewall for icmp packets"
				    );
			    } else {
				$ping_no++;
				return array("status"=>"warn",
					     "msg"=>"test not complete with errors",
					     "sent"=>$ping_no,
					     "recv"=>$recv_count,
					     "ip_proto"=>$ip_protocol,
					     "src_addr"=>$this->src_addr,
					     "timeout_count"=>$timeout_count,
					     "states"=>$ping_result,
					     "packet_len"=>strlen($this->packet_data),
				    );
			    }
			}
		    }
		}
		
/*		if(!socket_last_error($this->icmp_socket)) {
		    return array("status"=>"error",
		    "msg"=>"Cannot read from  $dst_addr. Reason:".socket_strerror(socket_last_error($this->icmp_socket))."");
		}*/
		
		if($keepon) {
			if($times_timeout!=0) {
			    $times_timeout=0;
			    $num_retries++;
			    // перенесем время старта пакета
			    $startTime = microtime(true); // need this for the looping section
			}
		    $ping_result[$ping_no]=$this->get_time($percision) * 1000;
		    $recv_count++;
		} else {
		    $ping_result[$ping_no]="-1";
		    $timeout_count++;
		}
//		usleep(1000);
		$ping_no++;
		if( (microtime(true) - $script_startTime) > 60 ) { $script_timeout=false; }
	    }  // while

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



	    return array(	"status"=>"done",
				"msg"=>"test complete",
				"sent"=>$count_pings,
				"recv"=>$recv_count,
				"ip_proto"=>$ip_protocol,
				"src_addr"=>$this->src_addr,
				"timeout_count"=>$timeout_count,
				"states"=>$ping_result,
				"min_delay"=>sprintf("%.3F",$indicator_min),
				"max_delay"=>sprintf("%.3F",$indicator_max),
				"avg_delay"=>sprintf("%.3F",$average),
				"packet_len"=>strlen($this->packet_data),
		);
	}
	/* like min(), but casts to int and ignores 0 */
	function min_not_null(Array $values) {
	    return min(array_diff(array_map("intval",$values), array(0)));
	}

	function FloodPing($dst_addr, $count_pings="1000",$packet_size="1472") {
	    $result = $this->ping($count_pings,trim($dst_addr),100,6,$packet_size);
	    return($result);
	}

} // end class Net_Ping


// start here

include("check_ip_range.php");
$ip_range=new check_ip_range;

$script_start=$script_end="0";
$script_start=microtime(true);

$pkt_len="1472";

if(isset($_GET["pktlen"]) && ($_GET["pktlen"])) {
    $pkt_len=$_GET["pktlen"];
}
if( $pkt_len>"1472") $pkt_len="1472";
if( $pkt_len<"2") $pkt_len="2";



if(isset($_GET["host"])&& ($_GET["host"])) {
    $ip_addr=$_GET["host"];

    /* Check ip */
    $ip_result=$ip_range->check_in_range($ip_addr);
    if($ip_result["status"]=="free") {
	$result=array("status"=>"error","msg"=>"Making FLOOD PING out of ours ranges STRONGLY PROHIBITED!!!");
    } else {
	//  ip_result == overlap
	$ping = new Net_Ping;
	$result=$ping->FloodPing($ip_addr,"1000",$pkt_len);
    }
} else {
    $result=array("status"=>"error","msg"=>"host argument missing");
}

$script_end = microtime(true);
$total_time = sprintf("%0.4F",$script_end - $script_start);

# http://stackoverflow.com/questions/645582/how-to-draw-a-graph-in-php
# ------- The graph values in the form of associative array

$margin_top=27;
$margin_bottom=27;
$margin_left=70;
$margin_right=25;

$img_width=1002+$margin_left+$margin_right;
$img_height=230+$margin_top+$margin_bottom;

# ---- Find the size of graph by substracting the size of borders
$graph_width=$img_width - $margin_left - $margin_right;
$graph_height=$img_height - $margin_top - $margin_bottom;
$img=imagecreate($img_width,$img_height);




# -------  Define Colors ----------------
$bar_color=imagecolorallocate($img,70,130,180);
$vertical_values_color=imagecolorallocate($img,0,0,0);
$background_color=imagecolorallocate($img,200,200,200);
$result_color=imagecolorallocate($img,143,188,143);
$border_color=imagecolorallocate($img,173,216,230);
$line_color=imagecolorallocate($img,120,120,120);
$error_color=imagecolorallocate($img,255,0,0);
$percent_color=imagecolorallocate($img,220,0,0);
$normaltext_color=imagecolorallocate($img,0,0,139);
$point_color=imagecolorallocate($img,0,0,128);
# ------ Create the border around the graph ------
$font = './arial.ttf';

imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color);
imagefilledrectangle($img,$margin_left,$margin_top,$img_width-1-$margin_right,$img_height-1-$margin_bottom,$background_color);

if($result["status"]=="error") {

    $text=$result["msg"];
    imagerectangle($img,$margin_left,$margin_top,$graph_width+$margin_left,$graph_height+$margin_top,$error_color);
    imagerectangle($img,$margin_left+1,$margin_top+1,$graph_width+$margin_left-1,$graph_height+$margin_top-1,$error_color);
    // imagestring($img,5,$margin_left+20,$margin_top+20,$text,$error_color);
    imagettftext($img, 14, 0, $margin_left+20, $margin_top+30, $error_color, $font, $text);

} else {

	$values = $result["states"];

	$bar_width=1;
	$total_bars=count($values);
	$bar_width=intval(1002/$total_bars)-1;
	if($bar_width<"0") $bar_width="0";
	$gap=($graph_width - $total_bars * $bar_width ) / ($total_bars +1);

	//# ------- Max value is required to adjust the scale -------
	$max_value=max($values);
	$ratio= $graph_height/$max_value;


	//# -------- Create scale and draw horizontal lines  --------
	$horizontal_lines=10;
	$horizontal_gap=$graph_height/$horizontal_lines;

	for($i=1;$i<=$horizontal_lines;$i++){
	    $y=$img_height - $margin_bottom - $horizontal_gap * $i ;
	    imageline($img,$margin_left,$y,$img_width-$margin_right,$y,$line_color);
	    $v=sprintf("%03.3F",$horizontal_gap * $i / $ratio);
	    imagestring($img,5,20,$y-5,$v,$vertical_values_color);
	}
	//# ----------- Draw the bars here ------
	$prev_x=$prev_y="";

	reset($values);
	// FIRST DRAW ALL "POSITIVES PINGS"
	for($i=0;$i< $total_bars; $i++){
	    //# ------ Extract key and value pair from the current pointer position
	    list($key,$value)=each($values);
	    if($value=="-1") {
	    } else {
		$x1= $margin_left + $gap + $i * ($gap+$bar_width) ;
		$x2= $x1 + $bar_width;
		$y1=$margin_top + $graph_height- intval($value * $ratio) ;
		$y2=$img_height - $margin_bottom;
		if($prev_x=="") {$prev_x=$x1+1;}
		if($prev_y=="") {$prev_y=$y1+1;}
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$bar_color);
		// FAT POINTS on graph 
		//imagefilledrectangle($img,$x1-1,$y1-1,$x1+2,$y1+2,$point_color);
		//imageline($img,$x1,$y1,$prev_x,$prev_y,$bar_color);
		//imagesetpixel($img,$x1,$y1,$point_color);
		//$prev_x=$x1;
		//$prev_y=$y1;
	    }

	}
	
	reset($values);

	// draw timeout  pings as red lines 
	for($i=0;$i < $total_bars; $i++){
	    //# ------ Extract key and value pair from the current pointer position
	    list($key,$value)=each($values);
	    if($value=="-1") {
		$x1= $margin_left + $gap + $i * ($gap+$bar_width) ;
		$x2= $x1 + $bar_width;
		$y1= $margin_top+$graph_height- intval($max_value * $ratio) ;
		$y2= $img_height-$margin_bottom;
		imagefilledrectangle($img,$x1,$y1,$x2+1,$y2,$error_color);
	    } else {
	    }
	}

	imagerectangle($img,$margin_left,$margin_top,$graph_width+$margin_left,$graph_height+$margin_top,$result_color);

	$lost=(($result["sent"]-$result["recv"])/$result["sent"])*100;
	$lost_txt=sprintf("%4.3F",$lost);


/*	// at your flavours

	$ipaddr_len=strlen($ip_addr);
	if($ipaddr_len<"16") $ipaddr_len="16";
	
	if($result["ip_proto"]=="ipv6") {
	    $text="IPv6 ADDR: ".$ip_addr."";
	} elseif($result["ip_proto"]=="ipv4") {
	    $text="IPv4 ADDR: ".$ip_addr."";
	}
	$box_offset=$ipaddr_len*10+80;
	$ypos=$margin_top+17;
	$info_y=20*6+4;
	$text_x=$img_width-$margin_right-$box_offset+10;

	imagefilledrectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right-2,$margin_top+$info_y+4,$result_color);

	imagerectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right,$margin_top+$info_y+5,$normaltext_color);
	imagerectangle($img,$img_width-$margin_right-$box_offset-9,$margin_top+1,
				$img_width-$margin_right-1,$margin_top+$info_y+4,$normaltext_color);
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packet size: ".sprintf("%6d",$result["packet_len"])." bytes";
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets SENT:".sprintf("%6d",$result["sent"])."";
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets RECV:".sprintf("%6d",$result["recv"])."";
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets LOST:";
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);
	$text=$lost_txt;
	if($lost>"2.5") {
	    imagettftext($img,9,0,$text_x+130,$ypos,$percent_color,$font,$text);
	} else {
	    imagettftext($img,9,0,$text_x+130,$ypos,$normaltext_color,$font,$text);
	}
	$text=" %";
	imagettftext($img,9,0,$text_x+160,$ypos,$normaltext_color,$font,$text); $ypos+=19;
	$text="min / max / avg: ".$result["min_delay"]. " / " .$result["max_delay"]. " / ".$result["avg_delay"];
	imagettftext($img,9,0,$text_x , $ypos,$normaltext_color,$font,$text); $ypos+=19;


	$text  = "LOST: ".$lost_txt."%    ";
	$text .= "SEND ".$result["sent"].", ";
	$text .= "RECV ".$result["recv"]." ";
	$text .= "packets lenght ".$result["packet_len"]." bytes ";
	$text .= "min / max / avg delay, ms: ".$result["min_delay"]. " / " .$result["max_delay"]. " / ".$result["avg_delay"];

	$bbox = imagettfbbox(10, 0, $font, $text);
	imagettftext($img, 10, 0, $margin_left, $img_height - $margin_bottom - $bbox[5]+6,$normaltext_color, $font, $text);
*/

	// title
	$text="Test flood pings ";
	if($result["src_addr"]!="") {
	    $text.="ftom ".$result["src_addr"]." to ";
	} else {
	    $text.="";
	}
	$text.=$ip_addr."";
	// First we create our bounding box for the text
	$bbox = imagettfbbox(13, 0, $font, $text);
	// This is our cordinates for X and Y
	$x = $bbox[0] + (imagesx($img) / 2) - ($bbox[4] / 2) - 25;
	$y = $bbox[1] + (imagesy($img) / 2) - ($bbox[5] / 2) - 5;
	imagettftext($img, 13, 0, $x, 20,$normaltext_color, $font, $text);
	
	$tot_time="script working ".$total_time." sec";
	$bbox = imagettfbbox(10, 0, $font, $tot_time);
	imagettftext($img, 10,0, $img_width-$margin_right-$bbox[4], $img_height - $margin_bottom - $bbox[5]+8, $normaltext_color, $font, $tot_time);

	//$copyright="(C)umka@localka.net";
	//$bbox = imagettfbbox(10, 0, $font, $copyright);
	//imagettftext($img, 10,90, $img_width-$margin_right/2+5, $img_height-$bbox[5]-100, $normaltext_color, $font, $copyright);
    }
header("Content-type:image/png");
imagepng($img);
?>
