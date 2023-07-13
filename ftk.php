<?php
include('ftk0.php');
//-------------------------------
function md5_hex_to_dec($hex_str)
{
    $arr = str_split($hex_str, 4);
    foreach ($arr as $grp) {
        $dec[] = str_pad(hexdec($grp), 5, '0', STR_PAD_LEFT);
    }
    return implode('', $dec);
}
//-------------------------------
function convurl($t){
$s=mb_convert_encoding($t,'windows-1251','utf-8');
if (strpos($s,"??")===false) return $s;
return $t;
}

//-------------------------------
function _header($s,$tp=-1) {	file_put_contents("loc.sys",$tp.":".$s." #(".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].")#\r\n",FILE_APPEND);	header($s);
	exit;}
//-------------------------------
function TimeWWW($sdt) {
$edt=$sdt;
if (strpos($sdt,":")==false) $edt.=":00";

return $edt;
}
//-------------------------------
function TimeWWW2($sdt) {
if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);
$edt=date("H:i", $sdt);

return $edt;
}

//-------------------------------
function ZerroHide($s){if ($s==0) $s="";
	else $s=number_format($s, 2, ',', ' ');
return $s;
}
//-------------------------------
function DateWWWLit2($sdt) {

if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);

$edt=date("d", $sdt);
if (substr ($edt, 0,1)=="0") $edt=substr ($edt, 1,1);

$edt=date("d.m.Y", $sdt);


//$edt.=" ".date("Y", $sdt)." г.";

return $edt;
}
//-------------------------------
function DateWWWLit($sdt) {

if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);

$edt=date("d", $sdt);
if (substr ($edt, 0,1)=="0") $edt=substr ($edt, 1,1);

$edt=date("d.m.y", $sdt);


//$edt.=" ".date("Y", $sdt)." г.";

return $edt;
}
//-------------------------------
function DateWWW($sdt) {

if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);

$edt=date("d", $sdt);
if (substr ($edt, 0,1)=="0") $edt=substr ($edt, 1,1);

$dt=date("m", $sdt);
if ($dt==1) $edt.=" января ";
if ($dt==2) $edt.=" февраля ";
if ($dt==3) $edt.=" марта ";
if ($dt==4) $edt.=" апреля ";
if ($dt==5) $edt.=" мая ";
if ($dt==6) $edt.=" июня ";
if ($dt==7) $edt.=" июля ";
if ($dt==8) $edt.=" августа ";
if ($dt==9) $edt.=" сентября ";
if ($dt==10) $edt.=" октября ";
if ($dt==11) $edt.=" ноября ";
if ($dt==12) $edt.=" декабря ";

//$edt.=" ".date("Y", $sdt)." г.";

return $edt;
}
//-------------------------------
function DateWWWlitmes($sdt) {

if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);

$dt=date("m", $sdt);
if ($dt==1) $edt.="Янв";
if ($dt==2) $edt.="Фев";
if ($dt==3) $edt.="Мар";
if ($dt==4) $edt.="Апр";
if ($dt==5) $edt.="Май";
if ($dt==6) $edt.="Июн";
if ($dt==7) $edt.="Июл";
if ($dt==8) $edt.="Авг";
if ($dt==9) $edt.="Сен";
if ($dt==10) $edt.="Окт";
if ($dt==11) $edt.="Ноя";
if ($dt==12) $edt.="Дек";

//$edt.=" ".date("Y", $sdt)." г.";

return $edt;
}
//-------------------------------
function DateWWWall($sdt) {

if ($sdt=="2001-01-01") return " - - - ";
$sdt=strtotime($sdt);

$edt=date("d", $sdt);
if (substr ($edt, 0,1)=="0") $edt=substr ($edt, 1,1);

$dt=date("m", $sdt);
if ($dt==1) $edt.=" января ";
if ($dt==2) $edt.=" февраля ";
if ($dt==3) $edt.=" марта ";
if ($dt==4) $edt.=" апреля ";
if ($dt==5) $edt.=" мая ";
if ($dt==6) $edt.=" июня ";
if ($dt==7) $edt.=" июля ";
if ($dt==8) $edt.=" августа ";
if ($dt==9) $edt.=" сентября ";
if ($dt==10) $edt.=" октября ";
if ($dt==11) $edt.=" ноября ";
if ($dt==12) $edt.=" декабря ";

$edt.=" ".date("Y", $sdt)." г.";
if (date("Y", $sdt)<2000) $edt="";

return $edt;
}
//-------------------------------
function DateNedSmallWWW($sdt) {
$sdt=strtotime($sdt);
$dt=date("w", $sdt);
if ($dt==0) return "ВС";
if ($dt==1) return "ПН";
if ($dt==2) return "ВТ";
if ($dt==3) return "СР";
if ($dt==4) return "ЧТ";
if ($dt==5) return "ПТ";
if ($dt==6) return "СБ";
return "2134";
}
//-------------------------------
function GetHtttp(){//global
return "";
}
//-------------------------------
function strtolower_ru ($text) {
	$al = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	$au = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
   return str_replace($au, $al, strtolower($text));
}
//-------------------------------
function strtoupper_ru ($text) {
	$al = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	$au = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
   return str_replace($al, $au, strtoupper($text));
}

//---------------------------------------
function inputclean($input) {
	$input = htmlspecialchars($input, ENT_QUOTES, 'cp1251');
	$input = mysql_real_escape_string(stripslashes($input));
    $input = strip_tags($input);
    return $input;
}
//-------------------------------------
function SumZak($zak, $fldisk=true){
global $stUserTypePrice,$stDomainID;
global $stUserTypePrice;
global $stPromocode_id;

$sqlsz="select zakData.price*zakData.kol
, zak.zakPromo_id
, ''
, 0
, zak.weight
, zak.postprice
, zakData.base_id
,(select baseprice.pricenac from baseprice where baseprice.del=0 and baseprice.base_id=zakData.base_id and baseprice.pricetype_id=33 and baseprice.price>0 limit 1)
,(select basepricelink.price from basepricelink where basepricelink.act=1 and basepricelink.base_id=zakData.base_id and basepricelink.user_id=".intval($stUser_ID)." order by basepricelink.data desc limit 1)
from zak, zakData where zak.row_id=zakData.zak_id and zakData.del=0
and zakData.zak_id=".intval($zak);
//	echo $sqlsz;
$sqlsz=qSQL($sqlsz);
$summ=0;
$summn=0;
$zcomm="";
while ($sqlszg = mysql_fetch_row($sqlsz)){
$summpost=intval($sqlszg[5]);
$promo_id=$sqlszg[1];
//-------- id=759621 Организация доставки
if ($sqlszg[8]>0 || $sqlszg[7]>0 || $sqlszg[6]==759621) {$summn+=$sqlszg[0];$zcomm="<div style='color:#aaa;font-size:10px;'>* Скидка не распространяется на товар по распродаже и доставку!</div>";}
	else $summ+=$sqlszg[0];
}

//echo $summ;

//echo $summ."!";
//echo $summn."!";
//echo $summpost."!";

$summd=0;
$discount=0;
if ($stPromocode_id==0){
	if ($promo_id==0) $sqlszd="select price, percent, row_id from zakDiscount where del=0 and price<='".($summ+$summn)."' and pricetype_id=".intval($stUserTypePrice)." and domain_id in (0,".intval($stDomainID).") order by price DESC";
		else $sqlszd="select zakPromoDiscount.price, zakPromoDiscount.percent, zakPromoDiscount.row_id from zakPromo, zakPromoDiscount where zakPromoDiscount.price<='".($summ+$summn)."' and zakPromo.row_id=".$promo_id." and zakPromoDiscount.del=0 and zakPromo.del=0 and zakPromoDiscount.zakPromo_id=zakPromo.Row_id and zakPromoDiscount.pricetype_id=".intval($stUserTypePrice)." and zakPromo.datastart<=CURDATE() and zakPromo.dataend>=CURDATE() order by zakPromoDiscount.price DESC";
	//echo $sqlsz;
	$sqlszd=qSQL($sqlszd);
	$sqlszdg=mysql_fetch_row($sqlszd);
	$discount=intval($sqlszdg[1]);
	if ($fldisk==false)$discount=0;
}
else {
	$sqlszd="select discount from promocode where row_id=".intval($stPromocode_id);
	$sqlszd=qSQL($sqlszd);
	$sqlszdg=mysql_fetch_row($sqlszd);
	$discount=intval($sqlszdg[0]);
}


if ($discount>0){
	mysql_data_seek($sqlsz, 0);
	while ($sqlszg = mysql_fetch_row($sqlsz)){
	//-------- id=759621 I?aaiecaoey ainoaaee
	if ($sqlszg[8]>0 || $sqlszg[7]>0 || $sqlszg[6]==759621) {
		}
		else {
			$tsm=round($sqlszg[0]-$sqlszg[0]*$discount/100,2);
		$summd+=$tsm;
				}
	}
}



//echo $summd."!";
//echo $summn."!";
//echo $summpost."!";

if ($discount!=0) {
		if ($promo_id==0) $summ=Cena($summd+$summn+$summpost,false);
			else  $summ=Cena($summd+$summn+$summpost,false);
		}
		else {
		$summ=Cena($summ+$summn, false)+$summpost;
		}


return $summ;
}

//----------------------------------------------------------------------------------
function SumZak2($zak){
//---Не забыть поменять в 1с в модуле ИнтернетЗаказПечать.ert
global $stUserTypePrice,$stUser_ID,$stDomainID;
global $stPromocode_id;

$sqlsz="select zakData.price*zakData.kol
, zak.zakPromo_id
, p.text
, case when zakData.ed=p.edosn and p.edosn<>'' then p.weight*p.edosnkf*zakData.kol else p.weight*zakData.kol end
, zak.weight
, zak.postprice
, zakData.base_id
,(select baseprice.pricenac from baseprice where baseprice.del=0 and baseprice.base_id=zakData.base_id and baseprice.pricetype_id=33 and baseprice.price>0 limit 1)
,(select basepricelink.price from basepricelink where basepricelink.act=1 and basepricelink.base_id=zakData.base_id and basepricelink.user_id=".intval($stUser_ID)." order by basepricelink.data desc limit 1)
from zak, zakData, base p where zak.row_id=zakData.zak_id and zakData.base_id=p.row_id and zakData.del=0 and zakData.zak_id=".intval($zak);
//	echo $sqlsz;
$sqlsz=qSQL($sqlsz);
$summ=0;
$summn=0;
$iweight=0;
$iweightpref="";
$zcomm="";
while ($sqlszg = mysql_fetch_row($sqlsz)){if ($sqlszg[3]>0) {$iweight+=$sqlszg[3];$iweightpref="";}
$summpost=intval($sqlszg[5]);

$promo_id=$sqlszg[1];
//-------- id=759621 Организация доставкиif ($sqlszg[8]>0 || $sqlszg[7]>0 || $sqlszg[6]==759621) {	$summn+=$sqlszg[0];
	$zcomm="<div style='color:#aaa;font-size:10px;'>* Скидка не распространяется на товар по распродаже и доставку!</div>";
	if ($sqlszg[8]>0) $zcomm="<div style='color:#aaa;font-size:10px;'>* Скидка не распространяется на товар по персональной скидке, распродаже или доставке!</div>";
	}	else $summ+=$sqlszg[0];
}


//echo "!!!".$summ;

//echo $stDomainID;

$summd=0;
$discount=0;
if ($stPromocode_id==0){
	if ($promo_id==0) $sqlszd="select price, percent, row_id from zakDiscount where del=0 and price<='".($summ+$summn)."' and pricetype_id=".intval($stUserTypePrice)." and domain_id in (0,".intval($stDomainID).") order by price DESC";
		else $sqlszd="select zakPromoDiscount.price, zakPromoDiscount.percent, zakPromoDiscount.row_id from zakPromo, zakPromoDiscount where zakPromoDiscount.price<='".($summ+$summn)."' and zakPromo.row_id=".$promo_id." and zakPromoDiscount.del=0 and zakPromo.del=0 and zakPromoDiscount.zakPromo_id=zakPromo.Row_id and zakPromoDiscount.pricetype_id=".intval($stUserTypePrice)." and zakPromo.datastart<=CURDATE() and zakPromo.dataend>=CURDATE() order by zakPromoDiscount.price DESC";
	//echo $sqlsz;
	$sqlszd=qSQL($sqlszd);
	$sqlszdg=mysql_fetch_row($sqlszd);
	$discount=intval($sqlszdg[1]);
    }
else {	$sqlszd="select discount from promocode where row_id=".intval($stPromocode_id);
	$sqlszd=qSQL($sqlszd);
	$sqlszdg=mysql_fetch_row($sqlszd);
	$discount=intval($sqlszdg[0]);
}

$summd=0;
if ($discount>0){
	mysql_data_seek($sqlsz, 0);
	while ($sqlszg = mysql_fetch_row($sqlsz)){
	//-------- id=759621 Организация доставки
	if ($sqlszg[8]>0 || $sqlszg[7]>0 || $sqlszg[6]==759621) {
		}
		else {			$tsm=round($sqlszg[0]-$sqlszg[0]*$discount/100,2);			$summd+=$tsm;
			}
	}
}



//$summd=$summ-$summ*$discount/100;


if ($discount!=0) {		if ($promo_id==0) $summ="Итого: <div class='tcol6 pdstb'>без скидки: ".($summ+$summn+$summpost)." руб.</div>со скидкой ".$discount."%: <div class='fbbb tcol pdtb'>".(Cena($summd+$summn+$summpost,false))." руб.</div>";
			else  $summ="Итого: <div class='tcol6'>без скидки: ".($summ+$summn+$summpost)." руб.</div>со скидкой ".$discount."% по промокоду: <div class='fbbb tcol pdtb'>".Cena($summd+$summn+$summpost,false)." руб.</div>";
		$summ.=$zcomm;
		}
		else {		$summ="Итого: <div class='fbbb tcol pdtb'>".(Cena($summ+$summn, false)+$summpost)." руб.</div>";
		}

if ($summpost>0)  $summ.="<br>в т.ч. доставка: ".$summpost." руб.";
$summ.="<div id=p3a>Вес: &asymp; ".Cena($iweight,false).$iweightpref." кг</div>";

return $summ;
}

//-------------------------------------
function Cena($s, $flscr=true,$dig=0){global $stDeviceType;//if ((strlen($s)-strrpos($s,"."))>2)
//echo "!".$s;
$s=round_up ($s, 2);
//echo "=!".$s;
if (strpos ($s,".")===false) $s.=".";
$k=strlen($s)-strrpos($s,".");
while ($k<3) {$s.="0";$k++;}
$s=str_replace(".00","",$s);


if ($dig>0){    if (strpos($s,".")===false) $s.=".0";
    while (strlen($s)-strpos($s,".")<=$dig){    	$s.="0";    	}	}
//if ($flscr==true) $s.="&nbsp;<span class=rublr>P</span>";
if ($flscr==true) {	if ($stDeviceType==0) $s.="&nbsp;руб.";
		else $s.="&nbsp;р.";
	}
return $s;
}
//-------------------------------------
function round_up ($value, $places=0) {
  if ($places < 0) { $places = 0; }
  $mult = pow(10, $places);
  $v=$value * $mult;
  if (strpos($v,".")===false) return $v/$mult;
//  echo "#".$v."#";
  return ceil($value * $mult) / $mult;
 }

//-------------------------------------
function FilterText($s){$ret="";$t=explode("\r",$s);
foreach($t as $k=>$v){
if (strpos($v,"ГОСТ=")===false && strpos($v,"СИНОНИМ=")===false && strpos($v,"СИНОНИМ_КОРОТКО=")===false){
	$ret.=$v."\r";
	}
}
return $ret;
}
//-------------------------------------
function TovarName($s, $fltit=false){
$s=preg_replace("|(\d)/\s\s(\d)|isU","\\1/\\2",$s);
$s=preg_replace("|(\d)/\s(\d)|isU","\\1/\\2",$s);

$s=preg_replace("|(\d)\sх(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)\sx(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)\s\sх(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)\s\sx(\d)|isU","\\1х\\2",$s);

$s=preg_replace("|(\d)х\s(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)x\s(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)х\s\s(\d)|isU","\\1х\\2",$s);
$s=preg_replace("|(\d)x\s\s(\d)|isU","\\1х\\2",$s);

$s=preg_replace("|\((\d),\s(\d)x|isU","(\\1,\\2х",$s);


//$s = preg_replace('/\,(?!\,)/', ', ', $s);

$s=preg_replace("|(\d)\, (\d)т|isU","\\1,\\2т",$s);

if ($fltit==true){
	$s=str_replace("кл. пр.","класс прочности",$s);
	$s=str_replace("кл.пр.","класс прочности",$s);
}

$s=str_replace("  "," ",$s);
return $s;
}
//-------------------------------------
function TextToScr($tx, $fl=true){global $stDeviceType;
if ($fl==true) $tx="<p>".$tx."</p>";


if ($fl==true) $tx=str_replace ("\r\n","</p><p>",$tx);
if ($fl==true) $tx=str_replace ("<p>-","<p>· ",$tx);
if ($fl==true) {	$tx=preg_replace("!<p>·(.*?): (.*?)</p>!si","<p>·\\1: <b>\\2</b></p>",$tx);
	}

$tx=str_replace ("·","<span id=imgli>&nbsp;&nbsp;</span>",$tx);

$tx=htmlspecialchars_decode($tx);

$tx=str_replace ("<table>","<table id=tarticle>",$tx);

$tx=str_replace ("<a?","<a href=".GetHtttp()."?",$tx);
//$tx=str_replace ("","",$tx);

$tx=str_replace ("src='a","src='/a",$tx);
$tx=str_replace ("src='i","src='/i",$tx);
$tx=str_replace ("src=\"a","src=\"/a",$tx);
$tx=str_replace ("src=\"i","src=\"/i",$tx);


$k=0;
while (strpos($tx,"[@")!==false){	$st=substr($tx,0,strpos($tx,"[@"));
	$val=substr($tx,strpos($tx,"[@")+2);
	$val=substr($val,0,strpos($val,"]"));
	$valm=substr($val,strpos($val,"-")+1);
	$val=substr($val,0,strpos($val,"-"));
	if ($stDeviceType!=0) $val=$valm;
	$tx=$st.$val.substr($tx,strpos($tx,"]")+1);
	$k++;
	if ($k==100) {		$tx.="#err=12367#";		break;
		}	}

return $tx;
}
//-------------------------------
function SaleAvailability($zak){
$sqlsz="select zak1doc.flprov from zak1doc where zak1doc.zak_id=".$zak;
$sqlsz=qSQL($sqlsz);
$sqlszg = mysql_fetch_row($sqlsz);
if ($sqlszg[0]==1) return "";

$sqlsz="select sum(case when zakData.ed=p.edosn and p.edosn<>'' then zakData.kol*p.edosnkf else zakData.kol end) as k
, (select sum(o.count) from basecount o, pricestype where o.pricestype_id=pricestype.row_id and o.base_id=p.row_id and o.del=0)
, (select sum(o.count) from basecount o, pricestype where o.pricestype_id=pricestype.row_id and o.base_id=p.root_id and o.del=0)
, p.kpereshet
, p.name
, zakData.ed
, p.edosn
, p.edosnkf
 from zak, zakData, base p where p.row_id<>759621 and zak.row_id=zakData.zak_id and zakData.base_id=p.row_id and zakData.del=0 and zakData.zak_id=".intval($zak)." group by zakData.base_id order by zakData.row_id";
//	echo $sqlsz;
$sqlsz=qSQL($sqlsz);
$ret="";
while ($sqlszg = mysql_fetch_row($sqlsz)){//echo $sqlszg[0]."-".GetOst2($sqlszg[1],$sqlszg[2],$sqlszg[3]);if ($sqlszg[0]>GetOst2($sqlszg[1],$sqlszg[2],$sqlszg[3])) $ret=$sqlszg[4]." нет в наличии. Есть ".$sqlszg[1].", а нужно ".$sqlszg[0]."<br>";
}
//echo $ret;
return $ret;
}
//-------------------------------------
function ArticleToScr($tx){//$tx=TextToScr($tx,true);
//$tx=preg_replace("|#(.)\)|isU","<sup title='\\1'>\\1</sup> ",$tx);
$tx=preg_replace_callback("|#(.)\)|isU", 'ArticleToScr1', $tx);


$tx=preg_replace("|#(.)\=|isU","<br>\\1) ",$tx);
//$tx=preg_replace("!#(.*?)\=!si","<br>\\1) ",$tx);

$tx = str_replace('>
', '>', $tx);

$bimg=false;
if (strpos($tx,"-sm.jpg'>")!==false) $bimg=true;

$tx = str_replace("-sm.jpg'>","-sm.jpg' class='bimg' onclick='ShowImg(this);'>", $tx);

$tx=str_replace ("<h5>","<h6>",$tx);
$tx=str_replace ("<h4>","<h5>",$tx);
$tx=str_replace ("<h3>","<h4>",$tx);
$tx=str_replace ("<h2>","<h3>",$tx);
$tx=str_replace ("<h1>","<h2>",$tx);

//$tx=nl2br($tx);


$tx=str_replace ("
-","
<img src='/art/3.gif'>",$tx);

$tx=str_replace ("\r\n","</p><p>",$tx);
$tx=str_replace ("><p></p><","><br><",$tx);
$tx="<p>".$tx."</p>";

$tx.="<div id=clear></div>";

if ($bimg==true) {	$tx.="<div id=bghide onclick='CloseImg();'></div><div id=bimg><div id=close onclick='CloseImg();'></div><div id=bimg2><img id=bimgsrc src='' width=600px></div></div>";
	}

return $tx;
}
//-------------------------------------
function ArticleToScr1($m) {
	global $tx;
//	echo "!!!".$tx;//	preg_match_all('#\#'.$m[1].'\=(.+?)$#is', $tx, $arr);
//	print_r($arr[1]);
//	echo "!!!!!<br>";
//	echo "<br>";
//	echo $arr[1][0];
	$tg="#".$m[1]."=";
   	$p=substr($tx,strpos($tx,$tg)+strlen($tg), 655);
   	if (strpos($p,"\r")) $p=substr($p,0,strpos($p,"\r"));
   	$p=strip_tags($p);
//   	$p=str_replace ("<br>"," ",$p);
//   	$p=substr($p,0,strpos($p,"\r"));
//   	echo $p;
	return "<sup title='".$p."'>".$m[1]."</sup> ";
//	return "###";
}
//-------------------------------------
function GetRazdelName($nm1,$nm2,$fllit=false){
global $stGOST,$stPriceSinonim;
$tx=$nm2;

if (strpos($tx,"СИНОНИМ=")!==false){	$tmp=substr($tx,strpos($tx,"СИНОНИМ=")+8,6555);
	if (strpos($tmp,"\r")!==false) $tmp=substr($tmp,0,strpos($tmp,"\r"));
	$tmp=str_replace(",",", ",$tmp);
	$tmp=str_replace("  "," ",$tmp);
	$stPriceSinonim=$tmp;
}

if (strpos($tx,"ГОСТ=")!==false){
	$tmp=substr($tx,strpos($tx,"ГОСТ=")+5,6555);
	if (strpos($tmp,"\r")!==false) $tmp=substr($tmp,0,strpos($tmp,"\r"));
	$stGOST[]=$tmp;
}

if (strpos($tx,"СИНОНИМ_КОРОТКО=")!==false){
	$tmp=substr($tx,strpos($tx,"СИНОНИМ_КОРОТКО=")+16,6555);
	if (strpos($tmp,"\r")!==false) $tmp=substr($tmp,0,strpos($tmp,"\r"));
	$tmp=str_replace(",",", ",$tmp);
	$tmp=str_replace("  "," ",$tmp);
	if ($fllit==true) $tx=$tmp;
	}

if ($tx=="") $tx=$nm1;
if (strpos($tx,"\r")!==false) $tx=substr($tx,0,strpos($tx,"\r"));
return $tx;
}
//-------------------------------------
function num2str($num) {
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('рубль'   ,'рубля'   ,'рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}
//-------------------------------------
function dtsale($dt) {$kday=5;
$d=strtotime($dt);
while ($kday>0){$d=strtotime("+1 day", $d);if (date("w",$d)==1 || date("w",$d)==2) $kday++;
$kday--;
}
return date('d.m.Y',$d);
}//-------------------------------------
function ImgWhiteSpaceY($source){
$img = imagecreatefromjpeg($source);

//find the size of the borders
$b_top = 0;
$b_btm = 0;
$b_lft = 0;
$b_rt = 0;

//bottom
for(; $b_btm < imagesy($img); ++$b_btm) {
  for($x = 0; $x < imagesx($img); ++$x) {
    if(imagecolorat($img, $x, imagesy($img) - $b_btm-1) != 0xFFFFFF) {
       break 2; //out of the 'bottom' loop
    }
  }
}

return $b_btm;
}
//-------------------------------------
function ImgWhiteSpace($source){
$dest=$source;
$dest=substr($dest,0,strrpos($dest,"."))."_ws.jpg";
if (file_exists($dest)) return $dest;


//echo $img;
$img = imagecreatefromjpeg($source);

//find the size of the borders
$b_top = 0;
$b_btm = 0;
$b_lft = 0;
$b_rt = 0;

//top
for(; $b_top < imagesy($img); ++$b_top) {
  for($x = 0; $x < imagesx($img); ++$x) {//  	echo "!".imagecolorat($img, $x, $b_top)."!";
    if(imagecolorat($img, $x, $b_top) != 0xFFFFFF) {
       break 2; //out of the 'top' loop
    }
  }
}

//bottom
for(; $b_btm < imagesy($img); ++$b_btm) {
  for($x = 0; $x < imagesx($img); ++$x) {
    if(imagecolorat($img, $x, imagesy($img) - $b_btm-1) != 0xFFFFFF) {
       break 2; //out of the 'bottom' loop
    }
  }
}

//left
for(; $b_lft < imagesx($img); ++$b_lft) {
  for($y = 0; $y < imagesy($img); ++$y) {
    if(imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {
       break 2; //out of the 'left' loop
    }
  }
}

//right
for(; $b_rt < imagesx($img); ++$b_rt) {
  for($y = 0; $y < imagesy($img); ++$y) {
    if(imagecolorat($img, imagesx($img) - $b_rt-1, $y) != 0xFFFFFF) {
       break 2; //out of the 'right' loop
    }
  }
}

//echo "!!!!".$b_top."!";
//echo "!!!!".$b_btm."!";

//copy the contents, excluding the border
$newimg = imagecreatetruecolor(
    imagesx($img)-($b_lft+$b_rt), imagesy($img)-($b_top+$b_btm));

//echo "!!!!".imagesy($newimg)."!";
//echo "!!!!".imagesy($newimg)."!";

imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));
imagejpeg($newimg, $dest);
return $dest;
}
//-------------------------------------
function SendMail($zak_id,$tema,$tx,$att=""){global $stUser_ID,$stDomainName2;

if ($att!="") {	$txold=$tx;
	$pdf="create";
	$id=$zak_id;
 	include("printsale.php");
 	$tx=$txold;
    }

require_once $_SERVER['DOCUMENT_ROOT']."/mail/PHPMailerAutoload.php";
$sql="select zak.row_id, sum(zakData.price), zak.fio, zak.mail, zak.tel,zak.Adr,user.mail,''
, (select domain.punycode from city t, domain where domain.cityregion_id=t.cityregion_id and t.row_id=user.city_id limit 1)
, (select domain.domain from city t, domain where domain.cityregion_id=t.cityregion_id and t.row_id=user.city_id limit 1)
, (select cfg.Tel from city t, domain, cfg where cfg.domain_id=domain.row_id and domain.cityregion_id=t.cityregion_id and t.row_id=user.city_id limit 1)
 from zak, zakData, base, user where user.row_id=zak.user_id and zak.user_id=".intval($stUser_ID)." and base.row_id=zakData.base_id and zakData.del=0 and zakData.zak_ID=zak.Row_ID and zak.del=0 and zak.row_id=".intval($zak_id);
//echo $sql;
$sql=qSQL($sql);
if (mysql_num_rows($sql)>0){
$sqlg = mysql_fetch_row($sql);
$stDomainName=$sqlg[8];
$stDomainName2=$sqlg[9];
$site_tel=$sqlg[10];
$email=trim($sqlg[3]);
if ($email=="") $email=trim($sqlg[6]);

ob_start();
$col1="#DBF1FC";
$col2="#0568A5";
$col3="#FDF9BB";
$col4="#ffc112";
$font="Verdana";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title>Нордком</title>

      <style type="text/css">
         /* Client-specific Styles */
         #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
         body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
         /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
         .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
         .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  */
         #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
         img {outline:none; text-decoration:none;border:none; -ms-interpolation-mode: bicubic;}
         a img {border:none;}
         .image_fix {display:block;}
         p {margin: 0px 0px !important;}
         table td {border-collapse: collapse;}
         table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
         a {color: #f00;text-decoration: none;text-decoration:none!important;}
         /*STYLES*/
         table[class=full] { width: 100%; clear: both; }
         /*IPAD STYLES*/
         @media only screen and (max-width: 640px) {
         a[href^="tel"], a[href^="sms"] {
         text-decoration: none;
         color: #f00; /* or whatever your want */
         pointer-events: none;
         cursor: default;
         }
         .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
         text-decoration: default;
         color: #f00 !important;
         pointer-events: auto;
         cursor: default;
         }
         table[class=devicewidth] {width: 440px!important;text-align:center!important;}
         table[class=devicewidthinner] {width: 420px!important;text-align:center!important;}
         img[class=banner] {width: 440px!important;height:420px!important;}
         img[class=colimg2] {width: 440px!important;height:420px!important;}


         }
         /*IPHONE STYLES*/
         @media only screen and (max-width: 480px) {
         a[href^="tel"], a[href^="sms"] {
         text-decoration: none;
         color: #ffffff; /* or whatever your want */
         pointer-events: none;
         cursor: default;
         }
         .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
         text-decoration: default;
         color: #ffffff !important;
         pointer-events: auto;
         cursor: default;
         }
         table[class=devicewidth] {width: 280px!important;text-align:center!important;}
         table[class=devicewidthinner] {width: 260px!important;text-align:center!important;}
         img[class=banner] {width: 280px!important;height:220px!important;}
         img[class=colimg2] {width: 280px!important;height:220px!important;}
         td[class="padding-top15"]{padding-top:15px!important;}


         }
      </style>
   </head>
   <body>
<!-- Start of header -->
<table width="100%" bgcolor="<?=$col3?>" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
               <tbody>
                  <tr>
                     <td width="100%">
                        <table bgcolor="<?=$col3?>" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                           <tbody>
                              <!-- Spacing -->
                              <tr>
                                 <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td>
                                    <!-- logo -->
                                    <table width="140" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                       <tbody>
                                          <tr>
                                             <td width="140" height="60" align="center">
                                                <div class="imgpop">
                                                   <a target="_blank" href="http://<?=$stDomainName?>">
                                                   <img src="http://725522.ru/art/logo.png" alt="" border="0" width="316" height="24" style="display:block; border:none; outline:none; text-decoration:none;">
                                                   </a>
                                                </div>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!-- end of logo -->
                                    <!-- start of menu -->
                                    <table width="250" border="0" align="right" valign="middle" cellpadding="0" cellspacing="0" border="0" class="devicewidth">
                                       <tbody>
                                          <tr>
                                             <td align="center" style="font-family: <?=$font?>; font-size: 14px;color: <?=$col2?>" st-content="phone"  height="60">
                                                <?=TextToScr($site_tel,false)?>
                                             </td>
                                          </tr>
                                       </tbody>
                                    </table>
                                    <!-- end of menu -->
                                 </td>
                              </tr>
                              <!-- Spacing -->
                              <tr>
                                 <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                              </tr>
                              <!-- Spacing -->
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </tbody>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
   <tbody>
      <tr>
         <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
               <tbody>
                  <tr>
                     <td width="100%">
                     <br>
                     <br>
					 <?
					 $tx=str_replace("\n","<br>",$tx);
					 echo $tx; ?>
					 </td>
				  </tr>
			</tbody>
		</table>
					 </td>
				  </tr>
	</tbody>
</table>

</body>
</html>
<?

$tx=ob_get_contents();
ob_end_clean();

$tx=str_replace("[username]",trim($sqlg[2]),$tx);
$tx=str_replace("[domain]","<a href='http://".$stDomainName."'>".$stDomainName2."</a>",$tx);

$tema=str_replace("[username]",trim($sqlg[2]),$tema);
$tema=str_replace("[domain]",$stDomainName2,$tema);

if (strpos($tx,"[zakdata]")!==false){	$pos=1;
	$sql="select zak.row_id, base.name, zakData.kol, zakData.price, base.row_id, base.del from zak, zakStatus, zakData, base where base.row_id=zakData.base_id and zakData.del=0 and zakData.zak_ID=zak.Row_ID  and zak.del=0 and zak.zakStatus_id=zakStatus.row_id and zak.user_id=".intval($stUser_ID)." and zak.Row_ID=".intval($zak_id)." order by zakData.row_id";
	//echo $sql;
	$sql=qSQL($sql);
	$z="";
	while ($sqlg = mysql_fetch_row($sql)){	if ($sqlg[5]==0) {			$z.=$pos.". <a href='http://".$stDomainName."/?id=".$sqlg[4]."'>".$sqlg[1]."</a>: ".$sqlg[2]."x".$sqlg[3]."=".($sqlg[3]*$sqlg[2])."\n";
			}
			else {			$z.=$pos.". ".$sqlg[1].": ".$sqlg[2]."x".$sqlg[3]."=".($sqlg[3]*$sqlg[2])."\n";
			}
	$pos++;
	}
	$z=str_replace("\n","<br>",$z);
	$tx=str_replace("[zakdata]",$z,$tx);
}

//echo $email;
//echo $tema;
//echo $tx;

$mail = new PHPMailer;
$mail->setFrom('info@725522.ru', 'Нордком');
$mail->CharSet = "Windows-1251";
//$mail->addAddress('vladrmg10@yandex.ru', '');
$mail->addAddress($email, '');
$mail->Subject = $tema;
$mail->msgHTML($tx);

//$mail->AltBody = 'This is a plain-text message body';
if ($att!="") {// 	echo "pdf/".$zak_id.".pdf";
	$mail->addAttachment("pdf/chet_".$zak_id.".pdf");
//	$mail->addAttachment("1595.pdf");

	}



if (!$mail->send()) {
//    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
//    echo "Message sent!";
}




}

}

//------------------------------------------------
function GetTovarOpis($opis){
$opis=TextToScr($opis);
$oldp="";
if (strtoupper_ru(substr($opis,0,5))=="АКЦИЯ"){
$tx=substr($opis,6,66666);
$tx=substr($tx,1,strpos($tx,"))")-1);
$nm=substr($tx,0,strpos($tx,"("));
$oldp=substr($tx,strpos($tx,"(")+1,6555);
$opis=TextToScr(substr($opis,strpos($opis,"))")+2,66666));
}


if (strpos($opis,"[РОССТРОЙ")!==false){
$opis=substr($opis,0,strpos($opis,"[РОССТРОЙ]")).substr($opis,strpos($opis,"[РОССТРОЙ]")+10,65555);
$rosstroi=true;
}

if (strpos($opis,"[ETA")!==false){
$eta=substr($opis,strpos($opis,"[ETA")+1,strpos($opis,"]",strpos($opis,"[ETA"))-strpos($opis,"[ETA")-1);
$opis=substr($opis,0,strpos($opis,"[ETA")).substr($opis,strpos($opis,"]",strpos($opis,"[ETA"))+1,65555);
$rosstroi=true;
}

return $opis;
}
//------------------------------------------------
function qSQL($sql){global $start_time;
global $stLog;
global $stUser_ID;
//echo $sql;

if ($stUser_ID!=5062) return mysql_query($sql);

$t1=microtime();
$t1a=explode(" ",$t1);
$t1=$t1a[1] + $t1a[0];

ob_start();
?><div style="padding:4px; margin:8px; border:1px solid;background:#fff;"><?
echo $sql;
$ret=mysql_query($sql);

$t2=microtime();
$t2a= explode(" ",$t2);
$t2=$t2a[1] + $t2a[0];
$time =$t2-$t1;
?><?

if ($time>=0.1) { ?><span style='background:#f00; color:#fff;'><? }
else if ($time>=0.05) { ?><span style='background:#0f0; color:#fff;'><? }
printf(": %f секунд<br>",$time);
if ($time>=0.05) { ?></span><? }

$end_time = microtime();
$end_array = explode(" ",$end_time);
$end_time = $end_array[1] + $end_array[0];
$time = $end_time - $start_time;
echo "Время:".$time;
?></div><?
$stLog.=ob_get_contents();
ob_end_clean();
return $ret;
}
//------------------------------------------------
function unsetr($l){if (strpos($l,"?")===false)  return $l;
$st=substr($l,0,strpos($l,"?"));
$l=substr($l,strpos($l,"?")+1);




$parts = explode('&',$l);
$ret="";
$z="";
foreach ($parts as $t){
	$k=substr($t,0,strpos($t,"="));
	$t=substr($t,strpos($t,"=")+1);
	if ($k!="r"){
		$ret.=$z.$k."=".$t; $z="&";
		}
	}
if ($ret!="" && $st!="") $st.="?";
return $st.$ret;
}
//------------------------------------------------
function GetHrefAll(){global $stRazdelLink,$prop,$findtx,$menu_id,$cab;
$ret.="?";
if ($menu_id>0) $ret.="&m=".$menu_id;
if ($cab!="###") $ret.="&cab=".$cab;
$ret.=$stRazdelLink;



if ($findtx!="") $ret.="&s=".$findtx;

foreach ($prop as $k => $v) {
	if ($v['type']==9){		if ($v['start']>0)$ret.="&f_".$k."s=".$v['start'];		if ($v['end']>0) $ret.="&f_".$k."e=".$v['end'];
	}
	else {	if (count($v['values'])>0){
		foreach ($v['values'] as $z){
			$ret.="&f_".$k."_".$z."=1";
		}
	}
	}
}


return $ret;
}
//------------------------------------------------

if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}
//-------------------------------------------------------------------------
function intcodelike($zn){
$p=array(7,4,13,10,2,15,3,11,6,9,14,1,5,12,8,16);
$ret="";
foreach ($p as $z){$t=trim(substr($zn,$z-1,1));
if ($t!="") $ret.="%".$t;
}
return $ret;
}
//-------------------------------------------------------------------------
function intdecode($h){
$p=array(7,4,13,10,2,15,3,11,6,9,14,1,5,12,8,16);
$ret=array();
$i=1;
foreach ($p as $z){
$ret[$z]=$h[$i];
$i+=2;
}

//$ret=asort($ret);
//print_r($ret);

$i=0;
$ret2="";
while ($i<16){
$z=$ret[$i];
if ($z>='0' && $z<='9') $ret2.=$z;
$i++;
}

if ($h[0]=='o') {	if ($ret2!=0) $ret2=0-$ret2;
		else $ret2="-".$ret2;
	}
return $ret2;
}
//------------------------------------------------
function is_valid_inn( $inn )
{	$inn=GetCifr($inn);
    if ( preg_match('/\D/', $inn) ) return false;

    $inn = (string) $inn;
    $len = strlen($inn);


    if ( $len === 10 )
    {
        return $inn[9] === (string) (((
            2*$inn[0] + 4*$inn[1] + 10*$inn[2] +
            3*$inn[3] + 5*$inn[4] +  9*$inn[5] +
            4*$inn[6] + 6*$inn[7] +  8*$inn[8]
        ) % 11) % 10);
    }
    elseif ( $len === 12 )
    {

        $num10 = (string) (((
             7*$inn[0] + 2*$inn[1] + 4*$inn[2] +
            10*$inn[3] + 3*$inn[4] + 5*$inn[5] +
             9*$inn[6] + 4*$inn[7] + 6*$inn[8] +
             8*$inn[9]
        ) % 11) % 10);

        $num11 = (string) (((
            3*$inn[0] +  7*$inn[1] + 2*$inn[2] +
            4*$inn[3] + 10*$inn[4] + 3*$inn[5] +
            5*$inn[6] +  9*$inn[7] + 4*$inn[8] +
            6*$inn[9] +  8*$inn[10]
        ) % 11) % 10);

        return $inn[11] === $num11 && $inn[10] === $num10;
    }

    return false;
}
//------------------------------------------------
function GetCifr($s){$s = preg_replace("/[^0-9]/", '', $s);
return $s;
}
//----------------------------
function phone_number($sPhone){	$sPhone=trim($sPhone);	$sPhone=str_replace(" ","",$sPhone);	$sPhone=str_replace("+7","",$sPhone);
	if (substr($sPhone,0,2)=="8(") $sPhone=substr($sPhone,1);
	if (substr($sPhone,0,2)=="7(") $sPhone=substr($sPhone,1);
	if (substr($sPhone,0,2)=="89") $sPhone=substr($sPhone,1);
	if (substr($sPhone,0,2)=="79") $sPhone=substr($sPhone,1);
	if (substr($sPhone,0,2)=="8-") $sPhone=substr($sPhone,1);
	if (substr($sPhone,0,2)=="7-") $sPhone=substr($sPhone,1);

    $sPhone = preg_replace("/[^0-9]/", '', $sPhone);
    if(strlen($sPhone) != 10) return(false);
    $sArea = substr($sPhone, 0,3);
    $sPrefix = substr($sPhone,3,3);
    $sNumber = substr($sPhone,6,4);
    $sPhone = "".$sArea."".$sPrefix."".$sNumber;
    return($sPhone);
}
//------------------------------------------------
function CityList(){
$ret="<div id=clear></div>";
$sql="select city.row_id, city.city
 from city, post
 where city.del=0 and city.row_id=post.city_id and post.del=0 group by city.row_id order by city.city";
$sql=qSQL($sql);
while ($sqlg = mysql_fetch_row($sql)){
$ret.='<div class="left mgbo tcol lif15" style="width:180px;">'.$sqlg[1].'</div>';
}
return $ret;
}
//------------------------------------------------
function array_msort2($array, $cols)
{
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;

}
//-------------------------------------
function script_start(){ob_start();
}
//-------------------------------------
function UserActionAdd($uatype,$uaval=0,$uases=0){
global $stUser_ID,$stSessionID,$stVisorKey;
	if ($stUser_ID!=0){
		if ($stVisorKey==""){
			if ($uases==0) $uases=$stSessionID;
			$sqlua="insert into useraction set useractiontype_id=".intval($uatype).",val=".intval($uaval).",user_id=".intval($stUser_ID).",data=NOW(), useractionses_id=".intval($uases);
			$sqlua=qSQL($sqlua);
		}
	}
}
//-------------------------------------
function isSotr(){
	$ip=$_SERVER['HTTP_X_REAL_IP'];
	if ($ip=="") $ip=getenv('REMOTE_ADDR');
	$ip=trim($ip);
	//
	if ($ip=="127.0.0.1") return true;
	//ПТЗ
	if ($ip=="212.109.16.203") return true;
	//СПб
	if ($ip=="176.221.14.11") return true;
	//МСК
	if ($ip=="91.107.32.96") return true;
	if ($ip=="176.99.130.19") return true;
	return false;
}
//-------------------------------------
function script_end(){global $stScript;
$stScript.=ob_get_contents();
ob_end_clean();
}
?>