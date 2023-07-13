<?
if ($_GET['k']!="asdflh234bk12msdf") exit;
set_time_limit (0);
ini_set('max_execution_time', 0);
ignore_user_abort(true);
ini_set('memory_limit', '2200M');

include ('../conf.php');
mysql_connect($sServer, $sUser, $sPass);
mysql_select_db("nordcom");
mysql_query("set names cp1251");
include ('../ftk.php');

?>
<style type="text/css">
	body {
		font-family: Verdana, Arial;
		font-size: 12px;
		margin: 0px;
		padding: 4px;
	}

	table {
		font-size: 12px;
		border-spacing: 0;
		border-bottom: 1px #ccc solid;
		border-right: 1px #ccc solid;
		
	}

	td {

		border-top: 1px #ccc solid;
		border-left: 1px #ccc solid;
		padding: 4px;
	}

	tr:nth-child(2n) {
		border-top: 1px #000 solid;
		border-left: 1px #000 solid;
		background: #f9f9f9;
	
		padding: 4px;
	}
	tr:first-child{

		background: #eee linear-gradient(#efefef,#ddd);
	}
	.title{
		
	margin-top:20px;	
	margin-bottom:10px;	

	}
</style>
<?
$city1=$_GET['city1'];
if ($city1!=112) {echo "Пока не работает.";exit;}
$city2=$_GET['city2'];
$adr1=$_GET['adr1'];
$adr2=$_GET['adr2'];
$ves1=$_GET['ves1'];
$ves2=$_GET['ves2'];
$ves=round($ves1);
if ($ves2>$ves1)$ves=$ves2;
$vol1=$_GET['vol'];
$vol2=$_GET['vol2'];
$vol=$vol1;
if ($vol2>$vol1)$vol=$vol2;
if ($vol==0)$vol="0.1";

$mest1=$_GET['mest'];
$mest2=$_GET['mest2'];
$mest=$mest1;
if ($mest2>$mest1)$mest=$mest2;
if ($mest==0) $mest=1;

$adr1t=$adr1;
$adr2t=$adr2;
$adr1=mb_convert_encoding($adr1,'windows-1251','utf-8');
$adr2=mb_convert_encoding($adr2,'windows-1251','utf-8');

$sql="select row_id, city from city where row_id=".intval($city1);
$sql=mysql_query($sql);
$sqlg=mysql_fetch_row($sql);
$city1name=$sqlg[1];

$sql="select row_id, city from city where row_id=".intval($city2);
$sql=mysql_query($sql);
$sqlg=mysql_fetch_row($sql);
$city2name=$sqlg[1];

?>

<div style='margin:0 0 8px 0;'><b><?= $city1name ?>: <?= $adr1t ?> - ><br><?= $city2name ?>: <?= $adr2t ?></b></div>
<?

?>
<div class="title">С доставкой</div>
<table style="width:560px;" cellpadding="0" cellspacing="0">

	<tr>
		<td>Перевозчик</td>
		<td>Вес</td>
		<td>Объем</td>
		<td>Мест</td>
		<td>Дней</td>
		<td>Сумма</td>
	</tr>
	<?
line(1,"DPD",$ves,$vol,$mest,$adr2);
line(2,"ЖелДор",$ves,$vol,$mest,$adr2);
line(3,"ДЛ",$ves,$vol,$mest,$adr2);
newline(24,"Байкал-Сервис",$ves,$vol,$mest,$adr2);
//line(4,"ПЭК",$ves,$vol,$mest,$adr2);
?>
</table>
<div class="title">Без доставки</div>
<table style="width:560px;" cellpadding="0" cellspacing="0">

	<tr>
		<td>Перевозчик</td>
		<td>Вес</td>
		<td>Объем</td>
		<td>Мест</td>
		<td>Дней</td>
		<td>Сумма</td>
	</tr>

	<?
line(1,"DPD",$ves,$vol,$mest);
line(2,"ЖелДор",$ves,$vol,$mest);
line(3,"ДЛ",$ves,$vol,$mest);
newline(24,"Байкал-Сервис",$ves,$vol,$mest);
//line(4,"ПЭК",$ves,$vol,$mest);

?>
</table>
<?

//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//k=asdflh234bk12msdf&city1=112&city2=1&ves1=63.68&vol=0&mest=2&ves2=&vol2=&mest2=&adr1=%D0%9B%D0%B5%D1%81%D0%BD%D0%BE%D0%B9%20%D0%BF%D1%80.,%D0%B4%D0%BE%D0%BC%2051,%20%D0%BA%D0%B2/%D0%BE%D1%84%20217&adr2=2-%D0%BE%D0%B9%20%D0%93%D1%80

//echo "OK";

//-----------------------------
function newline($companyid,$nm,$weight,$volume,$mest,$adr2=""){
global $city1,$city2;


	
		$url="http://725522.ru/post2/post_api.php?k=KLJASLDFBHjwerjhgazsfJGAJSGDJAGAJSF&action=1001&companyid=".$companyid."&city=".intval($city2)."&weight=".$weight."&volume=".$volume."&places=$mest&adr2=".$adr2;

		$r=file_get_contents($url);

		$ret=json_decode($r,true);


	


		?>
<tr>
	<td><?= $nm ?></td>
	<td><?= $ret['weight'] ?></td>
	<td><?= $ret['volume'] ?></td>
	<td><?= $ret['places'] ?></td>
	<td><?= $ret['days'] ?></td>
	<td><?= $ret['price'] ?></td>
</tr>
<?

}



function line($tk,$nm,$ves,$vol,$mest,$adr2=""){
	global $city1,$city2;
	
		//$volsql=str_replace(".",",",$vol);
	//	$vessql=str_replace(".",",",$ves);
	
		$sql="select row_id, now()-data, days, price from postcache where post_id=".intval($tk)." and weight='".$ves."' and vol='".$vol."' and mest=".intval($mest)." and adr='".inputclean($adr2)."' and city1=".intval($city1)." and city2=".intval($city2);
	//	echo $sql;
		//exit;
	
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$rid=$sqlg[0];
		$time=$sqlg[1];
		$days=$sqlg[2];
		$sm=$sqlg[3];
		$fl=true;
		if ($adr2!="") $nm.=" (адрес)";
			else  $nm.=" (пункт)";
	
		if ($rid==0 || $time>60*60*8){
			$fl=false;
			$url="http://725522.ru/post/post_api.php?k=KLJASLDFBHjwerjhgazsfJGAJSGDJAGAJSF&a=1&t=".$tk."&c2=".intval($city2)."&m=".$mest."&w=".$ves."&v=".$vol."&adr2=".$adr2;
			//echo $url."<br>";
			$ret=file_get_contents($url);
		//	echo "----------------------<br>";
		//	echo $ret;
			$ret=explode("\r",$ret);
			$mins=99999999999999999999;
			$maxs=0;
			$mind=99999999999999999999;
			$maxd=0;
			foreach ($ret as $zn){
			$r=explode("|",$zn);
			$s=$r[2];
			$d=$r[3];
			if ($s>0){
				$fl=true;
				if ($s>$maxs) $maxs=$s;
				if ($s<$mins) $mins=$s;
				if ($d>$maxd) $maxd=$d;
				if ($d<$mind) $mind=$d;
				}
			}
			$sm=round($mins)."-".round($maxs);
			if ($mins==$maxs) $sm=$maxs;
			$days=round($mind)."-".round($maxd);
			if ($mind==$maxd) $days=$maxd;
	
			if ($fl==true){
				$sql=" data=now(), post_id=".intval($tk).", weight='".$ves."', vol='".$vol."', mest=".intval($mest).", adr='".inputclean($adr2)."', days='".inputclean($days)."', price='".inputclean($sm)."', city1=".intval($city1).", city2=".intval($city2);
				if ($rid==0) $sql="insert into postcache set ".$sql;
					else  $sql="update postcache set ".$sql." where row_id=".intval($rid);
		//		echo $sql;
				$sql=mysql_query($sql);
			}
		}
	
	
		if ($fl==true){
			?>
<tr>
	<td><?= $nm ?></td>
	<td><?= $ves ?></td>
	<td><?= $vol ?></td>
	<td><?= $mest ?></td>
	<td><?= $days ?></td>
	<td><?= $sm ?></td>
</tr>
<?
		}
	}


?>