<?php
#
#  umka@localka.net  skype: pashaumka
#
#  floodping.class.php version 0.3 beta @  18-03-2018
#  скрипт работает как с ipv4, так и с ipv6 адресами
#
#  для запуска скрипта необходимо запустить php5-fpm с правами root
#  user=root, group=root и опцией при запуске --allow-to-run-as-root
#  ну или более красиво
#
#  за баги и ляпы не пинать, а высылать исправления на umka@localka.net
#
#  echo "<img src='http://our.host.addr/ping.class.php?host=".$framedip."'>";
#  кому надо &pktlen=1472
#
#  надо допилить
#   * графику
#
/*
    скрипт  кладем в /путь/к/скрипту/billing_tools/

    в nginx:

        #Работа с php с правами рута
        location /billing_tools {
                root /путь/к/скрипту/;
                index index.php index.html index.htm;
		allow a.x.c.v/32;
		deny all;
                        
                location ~ ^/billing_tools/(.+\.php)$ {
                        #try_files      $uri =404;
                        root            /путь/к/скрипту/;
                        #fastcgi_pass   php-fpm;
                        fastcgi_pass    127.0.0.1:3031;
                        fastcgi_index   index.php;
                        fastcgi_param   SCRIPT_FILENAME $request_filename;
                        include         /etc/nginx/fastcgi_params;
                }
                location ~* ^/billing_tools/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
                        root /путь/к/картинкам/images/;
                }

            access_log  /путь/к/логам/webtools/logs/access.log main;
            error_log   /путь/к/логам/webtools/logs/error.log warn;
        }

    в php-fpm в пуле создаем пул с правами рута
        user=root
        group=root
        listen=127.0.0.1:3031;   // к примеру
*/


/* Проверка на валидность наших IP адресов */
include("check_ip_range.php");
include("net_ping.class.php");

$ip_range=new check_ip_range;

$script_start=$script_end="0";
$script_start=microtime(true);

$pkt_len="1472";

if(isset($_GET["pktlen"]) && ($_GET["pktlen"])) {
    $pkt_len=$_GET["pktlen"];
}
if( $pkt_len>"1472") $pkt_len="1472";
if( $pkt_len<"2") $pkt_len="2";

if(isset($_GET["host"]) && ($_GET["host"])) {
    $ip_addr=$_GET["host"];
    /* Проверка на валидность наших IP адресов */
    $ip_result=$ip_range->check_in_range($ip_addr);
    if($ip_result["status"]=="free") {
	$result=array("status"=>"error","msg"=>"Делать FLOOD PING вне разрешенных IP адресов ЗАПРЕЩЕНО!");
    } else {
	//  ip_result == overlap
	$ping = new net_ping;
	$ping -> set_ipv4_addr("192.168.0.1");			//  <--  поменять на свои
	$ping -> set_ipv6_addr("2001:1ffc:2ac4::2000:1");	//  <--  поменять на свои
	$result = $ping->stdPing( $ip_addr, "3", "100" );
	if($result["status"]=="done") {
	    if( ($result["sent"]!="0" ) && ( $result["recv"]=="0") ) {
		$result=array("status"=>"error","msg"=>"Опрашиваемый адрес ( ".$ip_addr." ) не отвечает. \nВозможно, включен фаервол или нет связи с устройством");
	    }
	    $result = $ping->FloodPing($ip_addr,"1000",$pkt_len);
	}

    }
} else {
    $result=array("status"=>"error","msg"=>"В скрипт не передан адрес опрашиваемого устройства");
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
//$bar_color=imagecolorallocate($img,0,64,128);
$bar_color=imagecolorallocate($img,70,130,180);
$vertical_values_color=imagecolorallocate($img,0,0,0);
$background_color=imagecolorallocate($img,208,208,208);
$result_color=imagecolorallocate($img,143,188,143);
//$background_color=imagecolorallocate($img,240,240,255);
//$border_color=imagecolorallocate($img,46,139,87);
$border_color=imagecolorallocate($img,173,216,230);
$line_color=imagecolorallocate($img,120,120,120);
$error_color=imagecolorallocate($img,255,0,0);
$error_color1=imagecolorallocate($img,240,0,0);
$error_color2=imagecolorallocate($img,255,69,0);
$error_color3=imagecolorallocate($img,255,0,0);
$error_color4=imagecolorallocate($img,139,0,0);
$error_color5=imagecolorallocate($img,255,0,0);
$percent_color=imagecolorallocate($img,220,0,0);
$normaltext_color=imagecolorallocate($img,0,0,139);
$point_color=imagecolorallocate($img,0,0,128);
$black_color=imagecolorallocate($img,0,0,64);

#$black_color=imagecolorallocate($img,255,69,0);
#$bar_color=imagecolorallocate($img,240,230,140);


#$background_color=imagecolorallocate($img,0,0,0);
#$black_color=imagecolorallocate($img,0,192,0);
#$bar_color=imagecolorallocate($img,0,128,0,50);



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
	    $v=sprintf("%.3F",$horizontal_gap * $i / $ratio);
	    //imagestring($img,5,20,$y-5,$v,$vertical_values_color);
	    $bbox = imagettfbbox(12, 0, $font, $v);
	    imagettftext($img, 12, 0, $margin_left-10-$bbox[4], $y+5+round($bbox[3]/2), $vertical_values_color, $font, $v);
	}
	//# ----------- Draw the bars here ------
	$prev_x=$prev_y="";

	reset($values);
	// сначала отрисуем все "позитивные пинги"
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
		// Жирные Точечки над графиком
		//imagefilledrectangle($img,$x1-1,$y1-1,$x1+2,$y1+2,$point_color);
		imageline($img,$x1,$y1,$prev_x,$prev_y,$black_color);
		//imagesetpixel($img,$x1,$y1,$point_color);
		$prev_x=$x1;
		$prev_y=$y1;
	    }

	}
	
	reset($values);

	// негативные отрисуем пожирнее.т.к. с мобилки их надо увидеть "не напрягаясь"
	for($i=0;$i < $total_bars; $i++){
	    //# ------ Extract key and value pair from the current pointer position
	    list($key,$value)=each($values);
	    $x1= $margin_left + $gap + $i * ($gap+$bar_width) ;
	    $x2= $x1 + $bar_width+1;
	    $y1= $margin_top+$graph_height- intval($max_value * $ratio)+1 ;
	    $y2= $img_height-$margin_bottom-1;
	    if($value == "-1") {
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$error_color1);
	    } elseif($value == "-2") {
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$error_color2);
	    } elseif($value == "-3") {
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$error_color3);
	    } elseif($value == "-4") {
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$error_color4);
	    } elseif($value == "-5") {
		imagefilledrectangle($img,$x1,$y1,$x2,$y2,$error_color5);
	    }
	}

	imagerectangle($img,$margin_left,$margin_top,$graph_width+$margin_left,$graph_height+$margin_top,$result_color);

	$lost=(($result["sent"]-$result["recv"])/$result["sent"])*100;
	$lost_txt=sprintf("%4.3F",$lost);


	$ipaddr_len=strlen($ip_addr);
	if($ipaddr_len<"16") $ipaddr_len="16";
	
	if($result["ip_proto"]=="ipv6") {
	    $text="IPv6 ADDR: ".$ip_addr."";
	} elseif($result["ip_proto"]=="ipv4") {
	    $text="IPv4 ADDR: ".$ip_addr."";
	}
	$box_offset=$ipaddr_len*10+80;
	$ypos=$margin_top+17;
	$info_y=20*5+4;
	$text_x=$img_width-$margin_right-$box_offset+10;


	
	$box_info_len=strlen($text);

	$imginfo=imagecreate($text_x,$info_y);
	//imagesavealpha($imginfo, true);

	imagefilledrectangle($imginfo,0,$text_x,
				0,$info_y,$result_color);
	imagerectangle($imginfo,0,$text_x,0,$info_y,$normaltext_color);
	imagerectangle($imginfo,1,$text_x-1,1,$info_y-1,$normaltext_color);
/*	imagettftext($imginfo,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packet size: ".sprintf("%6d",$result["packet_len"])." bytes";
	imagettftext($imginfo,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets SENT / RECV: ".sprintf("%d",$result["sent"])." / ".sprintf("%d",$result["recv"]);
	imagettftext($imginfo,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
//	$text="Packets RECV:".."";
//	imagettftext($imginfo,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets LOST:";
	imagettftext($imginfo,9,0,$text_x,$ypos,$normaltext_color,$font,$text);
	$text=$lost_txt;
	if($lost>"2.5") {
	    imagettftext($imginfo,9,0,$text_x+130,$ypos,$percent_color,$font,$text);
	} else {
	    imagettftext($imginfo,9,0,$text_x+130,$ypos,$normaltext_color,$font,$text);
	}
	$text=" %";
	imagettftext($imginfo,9,0,$text_x+160,$ypos,$normaltext_color,$font,$text); $ypos+=19;
	$text="min / max / avg: ".$result["min_delay"]. " / " .$result["max_delay"]. " / ".$result["avg_delay"];
	imagettftext($imginfo,9,0,$text_x , $ypos,$normaltext_color,$font,$text); $ypos+=19;
*/



/*	imagefilledrectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right-2,$margin_top+$info_y+4,$result_color);

	imagerectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right,$margin_top+$info_y+5,$normaltext_color);
	imagerectangle($img,$img_width-$margin_right-$box_offset-9,$margin_top+1,
				$img_width-$margin_right-1,$margin_top+$info_y+4,$normaltext_color);
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packet size: ".sprintf("%6d",$result["packet_len"])." bytes";
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
	$text="Packets SENT / RECV: ".sprintf("%d",$result["sent"])." / ".sprintf("%d",$result["recv"]);
	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
//	$text="Packets RECV:".."";
//	imagettftext($img,9,0,$text_x,$ypos,$normaltext_color,$font,$text);$ypos+=19;
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
*/

	$text  = "Потери: ".$lost_txt."%    ";
	$text .= "Отправлено ".$result["sent"].", ";
	$text .= "принято ".$result["recv"]." ";
	$text .= "пакетов длиной по ".$result["packet_len"]." байт(а) ";
	$text .= "min / max / avg delay, ms: ".$result["min_delay"]. " / " .$result["max_delay"]. " / ".$result["avg_delay"];


/*	$ipaddr_len=strlen($ip_addr);
	if($ipaddr_len<"16") $ipaddr_len="16";
	
	if($result["ip_proto"]=="ipv6") {
	    $text="IPv6 ADDR: ".$ip_addr."";
	} elseif($result["ip_proto"]=="ipv4") {
	    $text="IPv4 ADDR: ".$ip_addr."";
	}
	$box_offset=$ipaddr_len*10+80;
	$ypos=$margin_top+7;
	$info_y=20*5+4;
	$text_x=$img_width-$margin_right-$box_offset+5;

	imagefilledrectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right-2,$margin_top+$info_y+4,$result_color);

	imagerectangle($img,$img_width-$margin_right-$box_offset-10,$margin_top,
				$img_width-$margin_right,$margin_top+$info_y+5,$normaltext_color);
	imagerectangle($img,$img_width-$margin_right-$box_offset-9,$margin_top+1,
				$img_width-$margin_right-1,$margin_top+$info_y+4,$normaltext_color);
	imagestring($img,5,$text_x,$ypos,$text,$normaltext_color);$ypos+=20;
	$text="Packet size: ".sprintf("%6d",$result["packet_len"])." bytes";
	imagestring($img,5,$text_x,$ypos,$text,$normaltext_color);$ypos+=20;
	$text="Packets SENT:".sprintf("%6d",$result["sent"])."";
	imagestring($img,5,$text_x,$ypos,$text,$normaltext_color);$ypos+=20;
	$text="Packets RECV:".sprintf("%6d",$result["recv"])."";
	imagestring($img,5,$text_x,$ypos,$text,$normaltext_color);$ypos+=20;
	$text="Packets LOST:";
	imagestring($img,5,$text_x,$ypos,$text,$normaltext_color);
	if($lost>"2.5") {
	    imagestring($img,5,$text_x+130,$ypos,$text,$percent_color);
	} else {
	    imagestring($img,5,$text_x+130,$ypos,$text,$normaltext_color);
	}
	$text=" %";
	imagestring($img,5,$text_x+180,$ypos,$text,$normaltext_color);
	
	$ypos+=20;*/




/*	$text="Потери: ".$lost_txt."%    ";
	$text.="Отправлено ".$result["sent"].", ";
	$text.="принято ".$result["recv"]." ";
	$text.="пакетов длиной по ".$result["packet_len"]." байт(а) ";
*/	



/*	$_end_time = explode (" ", $script_end);
	// format start time
	$_start_time = explode (" ", $scpirt_start);
	$_start_time = $_start_time[1] + $_start_time[0];
	// get and format end time
	$_end_time = $_end_time[1] + $_end_time[0];
	$_exec_time= number_format ($_end_time - $_start_time, 8);
	$exec_time=sprintf("%04.4f", $_exec_time*1000);
*/

	//$text.=" script exec: $exec_time msec";

	$bbox = imagettfbbox(10, 0, $font, $text);
	// Пишем текст
	imagettftext($img, 10, 0, $margin_left, $img_height - $margin_bottom - $bbox[5]+6,$normaltext_color, $font, $text);
	// Для стандартного английского шрифта
	// imagestring($img,5,$margin_left+10,$img_height - $margin_bottom+4,$text,$normaltext_color);



	// Шапка
	$text="Тестируем флудным пингом ";
	if($result["src_addr"]!="") {
	    $text.="с ".$result["src_addr"]." <=> ";
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
	//imagettftext($img, $graph_width-15,$graph_height-10, 15, 10, $normaltext_color, $font, $total_time);
	
	$tot_time="скрипт работал ".$total_time." сек";
	$bbox = imagettfbbox(10, 0, $font, $tot_time);
	imagettftext($img, 10,0, $img_width-$margin_right-$bbox[4], $img_height - $margin_bottom - $bbox[5]+8, $normaltext_color, $font, $tot_time);

	//imagecopy($img,$imginfo,5, 5, 0, 0, imagesx($imginfo), imagesy($imginfo));

	//$copyright="(C) umka@localka.net";
	//$bbox = imagettfbbox(10, 0, $font, $copyright);
	//imagettftext($img, 10,90, $img_width-$margin_right/2+5, $img_height-$bbox[5]-100, $normaltext_color, $font, $copyright);
    }
header("Content-type:image/png");
imagepng($img);
?>
