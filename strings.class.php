<?php
class strings {


    public $month;
    public $year;
    public $day;
    public $day_of_week;

    public $formatted_num;


    private static $_instance;
    /*
	Get an instance of the class
	@return Instance
    */
    public static function getInstance() {
        if(!self::$_instance) { // If no instance then make one
	    self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {

	$this->month_name_skl_rod=array("01"=>"января",
                                "02"=>"февраля",
                                "03"=>"марта",
                                "04"=>"апреля",
                                "05"=>"мая",
                                "06"=>"июня",
                                "07"=>"июля",
                                "08"=>"августа",
                                "09"=>"сентября",
                                "10"=>"октября",
                                "11"=>"ноября",
                                "12"=>"декабря",
                                );

	$this->month_name_skl_predl=array("01"=>"январе",
                                "02"=>"феврале",
                                "03"=>"марте",
                                "04"=>"апреле",
                                "05"=>"мае",
                                "06"=>"июне",
                                "07"=>"июле",
                                "08"=>"августе",
                                "09"=>"сентябре",
                                "10"=>"октябре",
                                "11"=>"ноябре",
                                "12"=>"декабре",
                                );
    }


function write_lit($sum,$lit_index,$lang="RU") {
//    echo "summ:".$sum."\t";
    // 0 - копеек    ------  ------  -----
    // 1 - копейка   гривна  тысяча  миллион
    // 2-4 копейки   гривны  тысячи
    // 5-  копеек    гривен  тысяч
    if($lang=="RU") {
    $index[0]=array("","копейка","копейки","копеек");
    $index[1]=array("","гривна","гривны","гривен");

    $index[2]=array("","тысяча","тысячи","тысяч");
    $index[3]=array("","миллион","миллиона","миллионов");
    $index[4]=array("","миллиард","миллиарда","миллиардов");
    $index[5]=array("","триллион","триллиона","триллионов");
    $index[6]=array("","квадриллион","квадриллиона","квадриллионов");
    $index[7]=array("","квинтиллион","квинтиллиона","квинтиллионов");
    $index[8]=array("","секстиллион","секстиллиона","секстиллионов");
    $index[9]=array("","септиллион","септиллиона","септиллионов");
    $index[10]=array("","октиллион","октиллиона","октиллионов");
    $index[11]=array("","нониллион","нониллиона","нониллионов");
    $index[12]=array("","дециллион","дециллиона","дециллионов");

    $hu[9]="девятьсот";
    $hu[8]="восемьсот";
    $hu[7]="семьсот";
    $hu[6]="шестьсот";
    $hu[5]="пятьсот";
    $hu[4]="четыреста";
    $hu[3]="триста";
    $hu[2]="двести";
    $hu[1]="сто";
    $hu[0]="";

    $de["9"]="девяносто";
    $de["8"]="восемьдесят";
    $de["7"]="семьдесят";
    $de["6"]="шестьдесят";
    $de["5"]="пятьдесят";
    $de["4"]="сорок";
    $de["3"]="тридцать";
    $de["2"]="двадцать";
    $de["1"]="";
    $de["0"]="";


    $ed["19"]="девятнадцать";
    $ed["18"]="восемнадцать";
    $ed["17"]="семнадцать";
    $ed["16"]="шестнадцать";
    $ed["15"]="пятнадцать";
    $ed["14"]="четырнадцать";
    $ed["13"]="тринадцать";
    $ed["12"]="двенадцать";
    $ed["11"]="одиннадцать";
    $ed["10"]="десять";
    $ed["09"]="девять";
    $ed["08"]="восемь";
    $ed["07"]="семь";
    $ed["06"]="шесть";
    $ed["05"]="пять";
    $ed["04"]="четыре";
    $ed["03"]="три";
    $ed["02"]="две";
    $ed["01"]="одна";
    $ed["00"]="ноль";

    $ed_mil["01"]="один";
    $ed_mil["02"]="два";
    }

    if($lang=="UA") {
    $index[0]=array("","коп╕йка","коп╕йки","коп╕йок");
    $index[1]=array("","гривня","гривн╕","гривень");

    $index[2]=array("","тисяча","тисяч╕","тисяч");
    $index[3]=array("","м╕льйон","м╕льйона","м╕льйон╕в");
    $index[4]=array("","м╕л╕ард","м╕л╕арда","м╕л╕ард╕в");
    $index[5]=array("","трилл╕он","трилл╕она","трилл╕он╕в");
    $index[6]=array("","квадр╕л╕он","квадр╕л╕она","квадр╕л╕он╕в");
    $index[7]=array("","квинт╕л╕он","квинт╕л╕на","квинт╕л╕он╕в");
    $index[8]=array("","секст╕лл╕он","секст╕лл╕она","секст╕лл╕он╕в");
    $index[9]=array("","септ╕лл╕он","септ╕лл╕она","септ╕лл╕он╕в");
    $index[10]=array("","окт╕лл╕он","окт╕лл╕она","окт╕лл╕он╕в");
    $index[11]=array("","нон╕лл╕он","нон╕лл╕она","нон╕лл╕он╕в");
    $index[12]=array("","дец╕лл╕он","дец╕лл╕она","дец╕лл╕он╕в");

    $hu[9]="дев'ятьсот";
    $hu[8]="в╕с╕мсот";
    $hu[7]="с╕мсот";
    $hu[6]="ш╕стьсот";
    $hu[5]="п'ятьсот";
    $hu[4]="чотириста";
    $hu[3]="триста";
    $hu[2]="дв╕сти";
    $hu[1]="сто";
    $hu[0]="";

    $de["9"]="дев'яносто";
    $de["8"]="в╕с╕мьдесят";
    $de["7"]="с╕мдесят";
    $de["6"]="ш╕стдесят";
    $de["5"]="п'ятьдесят";
    $de["4"]="сорок";
    $de["3"]="тридцать";
    $de["2"]="двадцать";
    $de["1"]="";
    $de["0"]="";


    $ed["19"]="дев'ятнадцать";
    $ed["18"]="в╕с╕мнадцать";
    $ed["17"]="семнадцать";
    $ed["16"]="ш╕стнадцать";
    $ed["15"]="п'ятнадцать";
    $ed["14"]="чотирнадцать";
    $ed["13"]="тринадцать";
    $ed["12"]="дванадцать";
    $ed["11"]="одинадцать";
    $ed["10"]="десять";
    $ed["09"]="дев'ять";
    $ed["08"]="в╕с╕м";
    $ed["07"]="с╕мь";
    $ed["06"]="ш╕сть";
    $ed["05"]="п'ять";
    $ed["04"]="чотири";
    $ed["03"]="три";
    $ed["02"]="дв╕";
    $ed["01"]="одна";
    $ed["00"]="ноль";

    $ed_mil["01"]="один";
    $ed_mil["02"]="два";
    }


    $num_ed=$num_de=$num_hu=$lit_val=$numm="";

    $str_len=strlen($sum);
    if($sum!="000") {
    switch($str_len) {
	case "3":
	    $num_ed=substr($sum,-1,1);
	    $num_de=substr($sum,-2,1);
	    $num_hu=substr($sum,-3,1);
	    if( $num_hu!="0" ) $lit_val=$hu[$num_hu]." ";
	break;
	case "2":
	    $num_ed=substr($sum,-1,1);
	    $num_de=substr($sum,-2,1);
	break;
	case "1":
	    $num_ed=substr($sum,-1,1);
	    $num_de=-1;
	break;
    }
    } else { $num_de="0"; $num_ed="0"; }

    if( $num_de > "1") {   // 2X+
#	    echo "..+20..";
	  if($num_ed != "0") {
	    $numm=sprintf("%02d",$num_ed);
	    $lit_val .= $de[$num_de]." ".$ed[$numm];
	  } else {
	    $numm=sprintf("%02d",$num_ed);
	    $lit_val .= $de[$num_de];
	  }

	} else {
#	    echo "de<2 ";
	  if(($num_de=="0") and ($num_ed=="0")) {
#	    echo "de=0 ed=0 ";
	    if($lit_index=="0") {
		$lit_val .= "00";
	    // echo "zero";
	    }
	  } else {
#		    echo "..<20..";
		  if($num_de > "-1") {
		      $summ = $num_de."".$num_ed;
		  } else {
			  $summ = $num_ed;
		  }
#		  echo "--$summ--";
		  $numm=sprintf("%02d",$summ);
		  if( ($lit_index>="3")  and (($num_ed=="1") or ($num_ed=="2"))){
		    $lit_val .= $ed_mil[$numm];
		  } else {
		    $lit_val .= $ed[$numm];
		  }
	  } 
	}

    $numm=sprintf("%2d",$numm);
    if( $numm >= "5") $l_index="3";
    if( ( $numm > "1" ) and ( $numm < "5" ) ) $l_index="2";
    if( $numm == "1" ) $l_index="1";
    if( $numm == "0" ) $l_index="3";
#    echo " index ".$l_index."\n";
    $literal_end=$index[$lit_index];

    if( $lit_val=="" ) {
	if($lit_index == "1") {
	  $lit_end=$literal_end[$l_index];
	  return $lit_end;
	} 
    } else {
	$lit_end=$literal_end[$l_index];
	return $lit_val." ".$lit_end;
    }
//    echo $lit_index."::[".$lit_val."-".$lit_end."]\n";

}

function num2str($allsumm,$lang="RU") {

    $allsumm=sprintf("%.02f" , $allsumm);
    $pos = strpos($allsumm,'.');

    $index  = "0";
    $lencop = strlen($allsumm) - $pos-1;
    $nlencop=$lencop*(-1);
    $copeck = substr($allsumm, $nlencop,$lencop);
    if(strlen($copeck)=="1") $copeck=$copeck*10;
    // начнем с копеек
    $literal_sum = $this->write_lit($copeck,$index,$lang);
    $formatted_out = ".".$copeck;
    $index++;
    while( $pos > "0") {

	if($pos >= "3" ) {
	    $length="3";
	    $sum=substr($allsumm,$pos-3,$length);
	} else {
	    $length=$pos;
	    $sum=substr($allsumm,0,$length);
	}

	// proceed literal
	$lit=$this->write_lit($sum,$index,$lang);
	if($lit!="") {
	    (string)$literal_sum = $lit." ".$literal_sum;
	    $formatted_out = " ".$sum."".$formatted_out;
	}
	// proceed
	$pos = $pos - "3";
	$index++;
    }
    $this->formatted_out=$formatted_out;
    return($literal_sum);
    }

function human_readable_date( $mydate, $lang="ru", $kavichka=" ",$print_year="Y") {
    $month_d=array("","31","29","31","30","31","30","31","31","30","31","30","31");
    switch($lang) {
	case "ua":
	case "UA":
	    $month_n=array("","с╕чень","лютий","березень","кв╕тень","травень","червень","липень","серпень","вересень","жовтень","листопад","грудень");
	    $month_e=array("","с╕чня","лютого","березня","кв╕тня","травня","червня","липня","серпня","вересня","жовтня","листопада","грудня");
	break;
	case "translate":
	    $month_n=array("","yanvar","fevral","mart","aprel","may","ijun","ijul","avgust","sentyabr","oktyabr","noyabr","dekabr");
	    $month_e=array("","yanvarya","fevralya","marta","aprelya","maya","ijunya","ijulya","avgusta","sentyabrya","oktyabrya","noyabrya","dekabrya");
	break;

	case "ru_short":
	    $month_n=array("","янв","фев","мар","апр","май","июн","июл","авг","сен","окт","ноя","дек");
	    $month_e=array("","янв","фев","мар","апр","мая","июн","июл","авг","сен","окт","ноя","дек");

	break;
	case "ru":
	case "RU":
	default:
	    $month_n=array("","январь","февраль","март","апрель","май","июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь");
	    $month_e=array("","января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
	break;
    }
    $this->year=substr($mydate,0,4);
    $this->month=sprintf("%d",substr($mydate,5,2));
    $this->day=sprintf("%d",substr($mydate,8,2));
    $timestamp=substr($mydate,10);
    $restxt = $kavichka."".$this->day."".$kavichka." ";
    if(isset($month_e[$this->month]))  { $restxt .= $month_e[$this->month]; };
    if( $print_year=="Y") { $restxt.=" ".$this->year; };
    if($timestamp) $restxt.=" ".$timestamp;
  return($restxt);
}

    function human_period($mydate) {
	$month_n=array("","январь","февраль","март","апрель","май","июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь");
	$month_e=array("","января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
	$month_d=array("","31","29","31","30","31","30","31","31","30","31","30","31");

	$year_np=substr($mydate,0,4);
	$month_np=substr($mydate,5,2);
	$month_np=sprintf("%d",$month_np);
	$day_np=substr($mydate,8,2);
	$timestamp=substr($mydate,10);
	$restxt=$day_np." ".$month_e[$month_np]." ".$year_np;
	return($restxt);
    }

    function human_month_year($mydate,$lang="ru",$pad="n") {
	$lang=strtolower($lang);
	$pad=strtolower($pad);

	$month_d=array("","31","29","31","30","31","30","31","31","30","31","30","31");

	if($lang=="ua") {
	    switch($pad) {
		case "n":
		    $month_n=array("","с╕чень","лютий","березень","кв╕тень","травень",
				"червень","липень","серпень","вересень","жовтень","листопад","грудень");
		break;
		case "micц":
		    $month_n=array("","сiчн╕","лютому","березн╕","кв╕тн╕","травн╕",
				"червн╕","липн╕","серпн╕","вересн╕","жовтн╕","листопад╕","грудн╕");
		break;
		case "кличн":
		    $month_n=array("","с╕ченю","лютому","березеню","кв╕тню","травеню",
				"червню","липню","серпню","вересню","жовтню","листопаде","грудн╕");
		break;
		default:
		    $month_n=array("","с╕чня","лютого","березня","кв╕тня","травня",
				"червня","липня","серпня","вересня","жовтня","листопада","грудня");
		break;
	    }
	}

	if($lang=="ru") {
	    switch($pad) {
		case "n":
		    $month_n=array("","январь","февраль","март","апрель","май",
				"июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь");
		break;
		case "e":
		    $month_n=array("","январе","феврале","марте","апреле","мае",
				"июне","июле","августе","сентябре","октябре","ноябре","декабре");
		break;
		default:
		    $month_n=array("","января","февраля","марта","апреля","мая",
				"июня","июля","августа","сентября","октября","ноября","декабря");
		break;
	    }
	}

	if($lang=="translate") {
	    switch($pad) {
		case "n":
		    $month_n=array("","yanvar","fevral","mart","aprel","may",
				"ijun","ijul","avgust","sentyabr","oktyabr","noyabr","dekabr");
		break;
		case "e":
		    $month_n=array("","yanvare","fevrale","marte","aprele","mae",
				"ijune","ijule","avguste","sentyabre","oktyabre","noyabre","dekabre");
		break;
		default:
		    $month_n=array("","yanvarya","fevralya","marta","aprelya","maya",
				"ijunya","ijulya","avgusta","sentyabrya","oktyabrya","noyabrya","dekabrya");
		break;
	    }
	}

	$year_np=substr($mydate,0,4);
	$month_np=substr($mydate,5,2);
	$month_np=sprintf("%d",$month_np);
	$day_np=substr($mydate,8,2);
	$timestamp=substr($mydate,10);
	$restxt=$month_n[$month_np]." ".$year_np;
	return($restxt);
    }

    function human_dayname($mydate) {
    $day_name=array("Воскресенье","Понедельник","Вторник",
		"Среда","Четверг","Пятница","Суббота");
    $day_no=date('w',strtotime($mydate));
    $restxt=$day_name[$day_no];
    return($restxt);
}


function array2xml2($attr="SOAP", $array, $name='array',
			$standalone=FALSE, $beginning=TRUE,
			$nested="0") {

  if(!isset($output)) $output="";
  $lf = '';
  $bol = " ";
  $eol = "\n";

  if($attr=="sign") {
    $bol="";
    $eol="";
  }


    if ($beginning) {
		if($attr!="sign") {
		    $output .= "<"."?"."xml version=\"1.0\" encoding=\"UTF-8\""."?".">".$eol;
		}
	    if ($standalone) header("content-type:text/xml;charset=utf-8");
		if($attr=="SOAP") {
		$output .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'.$eol;
		$output .= '<soap:Body>'.$eol;
		$output .= '<'.$this->methodNow.' xmlns="http://stat.localka.net/">'. $eol;
		}
		$output .= '<' . $name . '>'.$eol;
	        $nested = 0;
      }

  // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
    $ArrayNumberPrefix = 'ARRAY_NUMBER_';
  if(empty($array)) {
    return;
  }
  foreach ($array as $root => $child) {
      if (is_array($child)) {

	if(is_string($root))
	$output .= str_repeat($bol, (2 * $nested)) . "<" . (is_string($root) ? $root : $ArrayNumberPrefix . $root) .">".$eol;
	  $nested++;
	  $output .= $this->array2xml2($attr, $child, NULL, NULL, FALSE, $nested);
	  $nested--;
	if(is_string($root))
	  $output .= str_repeat($bol, (2 * $nested)) . '</' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . $eol;
      } else {
	 if(is_string($child)) {
	    if($child=="__EMPTY__") {
		$output .= str_repeat($bol, (2 * $nested)) . "<" . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . " />".$eol;
	    } else {
		//$child=iconv("K_O_I_8-U","U_T_F-8",$child);
		$output .= str_repeat($bol, (2 * $nested)) . "<" . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . ">".$child."</" . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . ">".$eol;
	    }
	 }

      }
  }
    if ($beginning) {
	$output .= '</' . $name . '>'.$eol;
	if($attr=="SOAP") {
	    $output .= '</'.$this->methodNow.'>'.$eol;
	    $output .= '</soap:Body>'.$eol;
	    $output .= '</soap:Envelope>'.$eol;
	}
    }
    return $output;
}

function gen_random_string($len = 8) {
	$symbols = 'ab1cdefghijkmnopqrstuvwzyx2345l6789'; // Строка допустимых символов
	$max = strlen($symbols)-1;
	$rez = '';
	for ($i=0;$i<$len;$i++) {
	    $rez .= $symbols[rand(0,$max)]; 
	}
    return $rez;
}

function isValidDateTime($dateTime) {
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }
    return false;
}

function isValidDate($date) {
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }
    return false;
}


// преобразовывает время между двумя датами ( y-m-d H:i:s )
// результат в виде массива
function timespan($check_date,$curr_date) {
    if(!is_int($check_date)) { $ch_date = strtotime($check_date);  } else { $ch_date = $check_date; }
    if(!is_int($curr_date)) { $cu_date = strtotime($curr_date);  } else { $cu_date = $curr_date; }

    $result = $this->get_timespan($ch_date,$cu_date);
    $str = $result["str_b"];
    $elem_arr=array("years", "month", "weeks", "days", "hours", "minutes", "seconds");
    while(list($v,$i) = each($elem_arr) ) {
	if(isset($result[$i])) $str .= $result[$i] . ' ';
    }
    $str .= $result["str_e"];
    $result["hum_str"] = $str;
    $result["hum_days"]=$result["tot_days"]." ".$this->get_date($result["tot_days"], "день", "дней", "дня");
    $result["hum_weeks"]=$result["tot_weeks"]." ".$this->get_date($result["tot_weeks"], "неделю", "недель", "недели");
    $result["hum_month"]=$result["tot_month"]." ".$this->get_date($result["tot_month"], "месяц", "месяцев", "месяца");
    $result["hum_years"]=$result["tot_years"]." ".$this->get_date($result["tot_years"], "год", "лет", "года");
    return($result);
}

function get_date($date,$first,$second,$third){
    if((($date % 10) > 4 && ($date % 10) < 10) || ($date > 10 && $date < 20)){
        return $second;
    }
    if(($date % 10) > 1 && ($date % 10) < 5){
        return $third;
    }
    if(($date%10) == 1) {
        return $first;
    } else {
        return $second;
    }
}

function get_timespan($seconds = 1, $time = '')
{
    $str = array();

    if ( ! is_numeric($seconds))
    {
    	$seconds = 1;
    }
    if ( ! is_numeric($time))
    {
    	$time = time();
    }

    if ($time == $seconds) {
	$str["str_b"]="сейчас";
	$str["str_e"]="";
	$str["action"]="NOW";
	$seconds=0;
    } else { 

	if ($time <= $seconds) {
	    $seconds = (int)$seconds-(int)$time;
	    $str["str_b"]="через "; $str["str_e"]="";
	    $str["action"]="FUTURE";
	} else {
	    $str["str_b"]=""; $str["str_e"]="назад";
	    $seconds = (int)$time - (int)$seconds;
	    $str["action"]="PAST";
	}
    }
    
    $years = floor($seconds / 31536000);
    $tot_hours=floor($seconds/3600);
    $tot_days=floor($seconds/3600/24);
    $tot_month=floor($seconds/3600/24/30);
    $tot_weeks=floor($seconds/3600/24/7);
    $str["tot_hours"]=$tot_hours;
    $str["tot_days"]=$tot_days;
    $str["tot_weeks"]=$tot_weeks;
    $str["tot_month"]=$tot_month;
    $str["tot_years"]=$years;
    if ($years > 0)
    {
    	$str["years"] = $years.' '.$this->get_date($years,'год','лет','года');
    }

    $seconds -= $years * 31536000;
    $months = floor($seconds / 2628000);

    if ($years > 0 OR $months > 0)
    {
	if ($months > 0)
	{
		$str["month"] = $months.' '.$this->get_date($months,'месяц','месяцев','месяца');
	}
              $seconds -= $months * 2628000;
    }

    $weeks = floor($seconds / 604800);
    if ($years > 0 OR $months > 0 OR $weeks > 0)
    {
	if ($weeks > 0) {
		$str["weeks"] = $weeks.' '.$this->get_date($weeks,'неделю','недель','недели');
	}
	    $seconds -= $weeks * 604800;
    }

    $days = floor($seconds / 86400);
    if ($months > 0 OR $weeks > 0 OR $days > 0) {
	if ($days > 0) {
		$str["days"] = $days.' '.$this->get_date($days,'день','дней','дня');
	}
	    $seconds -= $days * 86400;
    }

    $hours = floor($seconds / 3600);

    if ($days > 0 OR $hours > 0) {
	if ($hours > 0) {
		$str["hours"] = $hours.' '.$this->get_date($hours,'час','часов','часа');
	}
	    $seconds -= $hours * 3600;
    }

    $minutes = floor($seconds / 60);
    if ($days > 0 OR $hours > 0 OR $minutes > 0) {
	if ($minutes > 0) {
		$str["minutes"] = $minutes.' '.$this->get_date($minutes,'минута','минут','минуты');
	}
	    $seconds -= $minutes * 60;
    }

    if ($str == '') {
       $str["seconds"] = $seconds.' '.$this->get_date($seconds,'секунда','секунд','секунды');
    }

    return $str;
}


    function fill_template($text,$array_of_names=array()) {

	$human_date=$this->human_readable_date($array_of_names["human_date"]);
	$date_now=$this->human_readable_date($array_of_names["date_now"]);
	$text=str_replace("<#user_name#>",$array_of_names["user_name"] , $text);
	$text=str_replace("<#time_now#>",$array_of_names["time_now"], $text);
	$text=str_replace("<#amount_pay#>",$array_of_names["amount_pay"], $text);
	$text=str_replace("<#balance2pay#>",$array_of_names["balance2pay"], $text);
	$text=str_replace("<#balance2action#>",$array_of_names["balance2action"], $text);
	$text=str_replace("<#balance_now#>",$array_of_names["balance_now"], $text);
	$text=str_replace("<#service_fee#>",$array_of_names["service_fee"], $text);
	$text=str_replace("<#options#>",$array_of_names["options"], $text);
	$text=str_replace("<#text_insert1#>",$array_of_names["text_insert1"], $text);

      // поиск и замена блоков контента самим контентом
	reset($this->content);
        while ( ( $s = current($this->content) ) !== FALSE ) {
            $text = str_replace("<var:".key($this->content).">", $s, $text);
            next($this->content);
        }

      // поиск и замена блоков контента самим контентом
	reset($array_of_names);
	//print_r($array_of_names);
	while ( ($s = current($array_of_names) ) !== FALSE ) {
	    $text = str_replace("<var:".key($array_of_names).">", $s, $text);
	    $text = str_replace("{{var:".key($array_of_names)."}}", $s, $text);
	    next($array_of_names);
	}
	return($text);
    }

    function validateDate($date, $format = 'Y-m-d H:i:s') {
	$version = explode('.', phpversion());
	if (((int) $version[0] >= 5 && (int) $version[1] >= 2 && (int) $version[2] > 17)) {
	    try {  $d = DateTime::createFromFormat($format, $date); }
                catch (Exception $e) {
                    if ($debug) { echo "Invalid date: " . $e->getMessage() . "<br>\n"; }
                        return false;
                    }
	    } else {
                    try { $d = new DateTime(date($format, strtotime($date))); }
		        catch (Exception $e) {
			    if ($debug) { echo "Invalid date: " . $e->getMessage() . "<br>\n"; }
			    return false;
                        }
	    }
	
//	$d = DateTime::createFromFormat($format, $date);
//	return $d && $d->format($format) == $date;

    }


// сперто с интернета http://oridoki.com/2009/10/14/php-array-2-json/
    function array2json($arr) {
	if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality. 
	$parts = array(); 
	$is_list = false;

	//Find out if the given array is a numerical array
	$keys = array_keys($arr);
	$max_length = count($arr)-1;
	if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
	    $is_list = true; 
	    for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
		if($i != $keys[$i]) { //A key fails at position check.
		    $is_list = false; //It is an associative array.
		    break;
		}
	    }
	}

	foreach($arr as $key=>$value) {
	    if(is_array($value)) { //Custom handling for arrays
		if($is_list) $parts[] = array2json($value); /* :RECURSION: */
		    else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
		} else {
		    $str = '';
		    if(!$is_list) $str = '"' . $key . '":';
		      //Custom handling for multiple data types
		    if(is_numeric($value)) $str .= $value; //Numbers
		    elseif($value === false) $str .= 'false'; //The booleans
		    elseif($value === true) $str .= 'true';
		    else $str .= '"' . addslashes($value) . '"'; //All other things
		    // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
		    $parts[] = $str;
		}
	}
	$json = implode(',',$parts);
	if($is_list) return '[' . $json . ']';//Return numerical JSON
	return '{' . $json . '}';//Return associative JSON
    }

    function is_mac_addr($mac_addr) {
	$string=strtolower($mac_addr);
	//echo "$string  ";
	$newmac="";
	$mac_addr=str_replace("-",":",$mac_addr);
	$linux_win_pattern="/^[a-f0-9]{1,2}[:][a-f0-9]{1,2}[:][a-f0-9]{1,2}[:][a-f0-9]{1,2}[:][a-f0-9]{1,2}[:][a-f0-9]{1,2}/i";
	$cisco_pattern="/^[a-f0-9]{4}[\.-:][a-f0-9]{4}[\.-:][a-f0-9]{4}/i";
	$mac=array();
	if(preg_match($linux_win_pattern,$mac_addr,$matches)) {
	    // linux or windows format
	    list($a1,$a2,$a3,$a4,$a5,$a6)=explode(':',$mac_addr);
	    $a1=hexdec($a1); $a2=hexdec($a2); $a3=hexdec($a3);$a4=hexdec($a4); $a5=hexdec($a5); $a6=hexdec($a6);
	    $newmac=sprintf("%02x:%02x:%02x:%02x:%02x:%02x",$a1,$a2,$a3,$a4,$a5,$a6);
	    //echo "linux:: ";
	    //echo "$newmac\n";
	    return($newmac);
	} elseif(preg_match($cisco_pattern,$mac_addr,$matches)) {
	    //cisco format
	    $newmac=$this->format_mac($mac_addr,"linux");
	    //echo "cisco::";
	    //echo "$newmac\n";
	    return($newmac);
	} else {
	    //echo "FALSE FORMAT\n";
	    return false;
	}
    }

    function format_mac($mac, $format="linux"){
	$mac = preg_replace("/[^a-fA-F0-9]/",'',$mac);
        $mac = (str_split($mac,2));
	if(!(count($mac) == 6))
		return false;
	if($format == 'linux' || $format == ':') {
	    return $mac[0]. ":" . $mac[1] . ":" . $mac[2]. ":" . $mac[3] . ":" . $mac[4]. ":" . $mac[5];
	} elseif($format == 'windows' || $format == '-') {
	    return $mac[0]. "-" . $mac[1] . "-" . $mac[2]. "-" . $mac[3] . "-" . $mac[4]. "-" . $mac[5];
	}elseif($format == 'cisco'){
	    return $mac[0] . $mac[1] . ":" . $mac[2] . $mac[3] . ":" . $mac[4] . $mac[5];
	}elseif($format == 'snmp'){
	    return hexdec($mac[0]). "." . hexdec($mac[1]) . "." . hexdec($mac[2]). "." . hexdec($mac[3]) . "." . hexdec($mac[4]). "." . hexdec($mac[5]);
	}else{
	    return $mac[0]. "$format" . $mac[1] . "$format" . $mac[2]. "$format" . $mac[3] . "$format" . $mac[4]. "$format" . $mac[5];
	}
    }

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



function cp1251_to_utf8($s)
  {
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "WINDOWS-1251")
    {
    $c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
    for($i=0; $i<strlen($s); $i++)
      {
      $c=ord($s[$i]);
      if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
      elseif ($c>239)  $t.=$c209.chr($c-112);
      elseif ($c==184) $t.=$c209.$c209;
      elseif ($c==168) $t.=$c208.$c129;
      else $t.=$s[$i];
      }
    return $t;
    }
  else
    {
    return $s;
    }
   }

function utf8_to_cp1251($s) {
    $out="";
    $out_i="";
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "UTF-8") {
  $byte2="";
    for ($c=0;$c<strlen($s);$c++) {
      $i=ord($s[$c]);
      if ($i<=127) $out.=$s[$c];
      if ($byte2) {
        $new_c2=($c1&3)*64+($i&63);
        $new_c1=($c1>>2)&5;
        $new_i=$new_c1*256+$new_c2;
        if ($new_i==1025)
          {
          $out_i=168;
          } else {
          if ($new_i==1105)
            {
            $out_i=184;
            } else {
            $out_i=$new_i-848;
            }
          }
        $out.=chr($out_i);
        $byte2=false;
        }
        if (($i>>5)==6)
          {
          $c1=$i;
          $byte2=true;
          }
      }
    return $out;
    }
  else
    {
    return $s;
    }
  }
	
	//http://stackoverflow.com/questions/3212266/detecting-russian-characters-on-a-form-in-php
	function ru2lat($str)    {
		$tr = array(
		"А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g", "Д"=>"d",
		"Е"=>"e", "Ё"=>"yo", "Ж"=>"zh", "З"=>"z", "И"=>"i", 
		"Й"=>"j", "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", 
		"О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s", "Т"=>"t", 
		"У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"ts", "Ч"=>"ch", 
		"Ш"=>"sh", "Щ"=>"sch", "Ъ"=>"", "Ы"=>"y", "Ь"=>"", 
		"Э"=>"e", "Ю"=>"yu", "Я"=>"ya",
		"а"=>"a", "б"=>"b", 
		"в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ё"=>"yo", 
		"ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"j", "к"=>"k", 
		"л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", 
		"р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", 
		"х"=>"kh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch", 
		"ъ"=>"", "ы"=>"y", "ь"=>"", "э"=>"e", "ю"=>"yu", 
		"я"=>"ya", 
		" "=>"-", "."=>"", ","=>"", "/"=>"-",  
		":"=>"", ";"=>"","—"=>"", "–"=>"-"
		);
		return strtr($str,$tr);
	}

	function detect_cyr_utf8($content) {
		return preg_match('/&#10[78]\d/', mb_encode_numericentity($content, array(0x0, 0x2FFFF, 0, 0xFFFF), 'UTF-8'));
	}

    function parse_rad_string($config_string="",$level="0") {

	if($level > "1") {
	    return(array("status"=>"error","msg"=>"Вложенности"));
	}

	if( isset($config_string) ) {
	    if( (strlen($config_string))=="0") {
		return;
	    }
	    $op_array=array(":=","==","+=","!=",">=","<=","=~","!~","=*","!*","=",">","<");
	    $config_string=str_replace("\r", "", $config_string);
	    $result=false;
	    $offset="0";

	    foreach($op_array as $op_string) {
		//echo " -- ".$op_string."  \n ";
		$offset = strpos($config_string,$op_string);
		if($offset) {
		    $op_len=strlen($op_string);
		    break;
		}
	    }
	    if(!$offset) {
		return(array("status"=>"error","msg"=>"Строка не содержит строки ключ-оператор-значение!!!"));
	    }

	    $str_key=substr($config_string,0,$offset);
	    $str_val=substr($config_string,$offset+$op_len);
	    $str_key = str_replace(" ", "", $str_key);
	    $str_key = str_replace("'", "", $str_key);
	    $str_key = str_replace("\"", "", $str_key);

	    $str_val = trim($str_val, " ");
	    $str_val = str_replace("'", "", $str_val);
	    $str_val = str_replace("\"", "", $str_val);

	    $result = self::parse_rad_string($str_val,++$level);
	    if($result["status"]=="ok") {
		return(array("status"=>"error","key"=>$str_key,"op"=>$op_string,"value"=>$str_val,"msg"=>"Значение не уникально"));
	    }
	    return(array("status"=>"ok","key"=>$str_key,"op"=>$op_string,"value"=>$str_val));
	} else {
	    return(array("status"=>"error","msg"=>"какой-либо строки не наблюдается!!!"));
	}
    } // function parse_rad_string

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
    function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
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
	foreach($sets as $set)
	{
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
	while(strlen($password) > $dash_len)
	{
		$dash_str .= substr($password, 0, $dash_len) . '-';
		$password = substr($password, $dash_len);
	}
	$dash_str .= $password;
	return $dash_str;
    }


} //class

?>
