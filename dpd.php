<?
//login: 326355ru
//QOUWKH

$flGoroda=1; //---���������� ������ 1-��
$flTerminal=1; //---���������� ������ ����������
$flcalc=1; //---���������� ���������

ini_set("display_errors",1);
error_reporting(E_ERROR);

set_time_limit (0);
ini_set('max_execution_time', 0);
ignore_user_abort(true);
ini_set('memory_limit', '2200M');


include ('conf.php');
$connection=mysqli_connect($sServer, $sUser, $sPass,"nordcom");

mysqli_query($connection,"set names cp1251");
include ('tk.php');

include('dpd_service.class.php');
$dpd = new DPD_service();

echo "����� ���������<br>";

//------------------------
if ($flGoroda==1) {
echo "���������� ������ �������:<br>";
$ret=$dpd->getCityList();
//print_r($ret);
//exit;
// [cityId] => 48951627 [countryCode] => RU [countryName] => ������ [regionCode] => 42 [regionName] => ����������� [cityCode] => 42000009000 [cityName] => �������� [abbreviation] => � [indexMin] => 650000 [indexMax] => 650993
foreach ($ret as $value) {
if (trim($value['abbreviation'])=="�"){
$sql="select row_id from city where dpdcode='".$value['cityCode']."' and del=0";
$sql=mysqli_query($connection,$sql);
if (mysqli_num_rows($connection,$sql)==0){
$err="";
$sql="select row_id, name from cityregion where code='".$value['regionCode']."' ";
//echo $sql;
$sql=mysqli_query($connection,$sql);
$cityregion_id=0;
$cityregionname="";
if (mysqli_num_rows($connection,$sql)>0){
	$sqlg=mysqli_fetch_row($sql);
	$cityregion_id=$sqlg[0];
	$cityregionname=$sqlg[1];
	}
	else {
		$err.="�� ������ ������: ".$value['regionCode']."! ";
	}

$sql="select row_id from citytype where name='".$value['abbreviation']."'";
//echo $sql;
$sql=mysql_query($sql);
$citytype_id=0;
if (mysql_num_rows($sql)>0){
	$sqlg=mysql_fetch_row($sql);
	$citytype_id=$sqlg[0];
	}
	else {
		$sql="insert into citytype set name='".$value['abbreviation']."', del=0";
//		echo $sql;
		$sql=mysql_query($sql);
	    $citytype_id=mysqli_insert_id($connection);
	}

$cn=$value['cityName'];
$cn=str_replace("�","�",$cn);
$sql="select row_id, citytype_id from city where cityregion_id=".$cityregion_id." and city='".$cn."'";
//echo $sql;
$sql=mysqli_query($connection,$sql);
$city_id=0;
if (mysqli_num_rows($connection,$sql)==0){
		//$sql="insert into city set city='".$value['cityName']."', del=0, country_id=1, biggest_city=0, cityregion_id=".intval($cityregion_id).", citytype_id=".intval($citytype_id).", dpdcode='".$value['cityCode']."', dpdcityid='".$value['cityId']."'";
		//echo $sql;
		//$sql=mysql_query($sql);
		?><span style='color:#f00;'>����� �� ������: <?=$value['cityName']?> (<?=$cityregionname?>)</span><?
	}
	else {
		$sqlg=mysqli_fetch_row($sql);
		$id=$sqlg[0];
		if ($sqlg[1]==1) {
				if ($citytype_id!=1) $id=-1;
//					else echo "<span style='color:#f00;'>����� ������ ������ � �������!</span>";
				}
		$sql="update city set citytype_id=".intval($citytype_id).", dpdcode='".$value['cityCode']."', dpdcityid='".$value['cityId']."' where row_id=".$id;
		//	echo $sql;
		$sql=mysqli_query($connection,$sql);
			//$err.="�� ������ �����: ".$value['regionCode']." - ".$value['cityName']."! ";
	}

    echo "|";
    echo $citytype_id;
    echo "|";
    echo $value['regionCode'];
    echo "|";
    echo $value['cityCode'];
    echo "|";
    echo $value['cityId'];
    echo "|";
    echo $value['cityName'];
    echo "|";
    echo $value['abbreviation'];
    echo "|";
    echo $err;
    echo "<br />";
}
//else { echo $value['cityName']." - ����� ���������!<br>";}
}
}

}
//------------------------


if ($flTerminal==1) {

$sql=mysqli_query($connection,"update post set flupd=0 where posttype_id=1 and post.flkurier<>2");
$sql=mysqli_query($connection,"update post, postwork set postwork.flupd=0 where post.posttype_id=1 and postwork.post_id=post.row_id and post.flkurier<>2");

//$ret=$dpd->getCityList();
//print_r($ret);
//exit;

$ret=$dpd->getTerminalList();
//print_r($ret);
//exit;
// Array ( [0] => Array ( [terminalCode] => M11 [terminalName] => ������ - M11 ���������� [address] => Array ( [countryCode] => RU [regionCode] => 77 [regionName] => ������
//[cityCode] => 77000000000 [cityName] => ������ [index] => 127081 [street] => ���������� [streetAbbr] => ����� [houseNo] => 3 )
//[geoCoordinates] => Array ( [latitude] => 55.884671 [longitude] => 37.628909 )
//[schedule] => Array ( [0] => Array ( [operation] => SelfPickup [timetable] => Array ( [0] => Array ( [weekDays] => ��,��,��,��,�� [workTime] => 08:00 - 22:00 ) [1] => Array ( [weekDays] => ��,�� [workTime] => 10:00 - 18:00 ) ) )
$kol=0;
foreach ($ret['terminal'] as $value) {
$kol++;
$err="";

$sql="select row_id from city where dpdcode='".$value['address']['cityCode']."'";
//echo $sql;
$sql=mysqli_query($connection,$sql);
$city_id=0;
if (mysqli_num_rows($sql)>0){
	$sqlg=mysqli_fetch_row($sql);
	$city_id=$sqlg[0];
	}
	else {
		$err.="�� ������ �����: ".$value['address']['cityCode']."! ";
	}

$id=0;
//--------------------------------------------
if ($city_id>0){

$sql="select row_id from streettype where name='".$value['address']['streetAbbr']."'";
//echo $sql;
$sql=mysqli_query($connection,$sql);
$streettype_id=0;
if (mysqli_num_rows($sql)>0){
	$sqlg=mysqli_fetch_row($sql);
	$streettype_id=$sqlg[0];
	}
	else {
		$sql="insert into streettype set name='".$value['address']['streetAbbr']."'";
		$sql=mysqli_query($connection,$sql);
		$streettype_id=mysql_insert_id($connection);
	}




$sql="select row_id from post where city_id=".intval($city_id)." and posttype_id=1 and code='".$value['terminalCode']."'";
//echo $sql;
$sql=mysqli_query($connection,$sql);
if (mysqli_num_rows($sql)==0){
		$sql="insert into post
		set flupd=1, flkurier=1, streettype_id=".$streettype_id.", store1_id=1, city_id=".intval($city_id).", posttype_id=1, code='".$value['terminalCode']."', name='".$value['terminalName']."',street='".$value['address']['street']."', house='".$value['address']['houseNo']."', x='".$value['geoCoordinates']['latitude']."', y='".$value['geoCoordinates']['longitude']."'
		";
//		echo $sql;
		$sql=mysqli_query($connection,$sql);
		$id=mysqli_insert_id($connection);
	}
	else {
		$sqlg=mysqli_fetch_row($sql);
		$id=$sqlg[0];
		$sql="update post
		set flupd=1, flkurier=1, del=0, streettype_id=".$streettype_id.", city_id=".intval($city_id).", posttype_id=1, code='".$value['terminalCode']."', name='".inputclean($value['terminalName'])."',street='".inputclean($value['address']['street'])."', house='".inputclean($value['address']['houseNo'])."', x='".$value['geoCoordinates']['latitude']."', y='".$value['geoCoordinates']['longitude']."'
		where row_id=".$id;

//		echo $sql;
		$sql=mysqli_query($connection,$sql);
	}
}
//--------------------------------------------
    echo $id;
    echo "|";
    echo $value['terminalCode'];
    echo "|";
    echo $value['terminalName'];
    echo "|";
    echo $value['address']['countryCode'];
    echo "|";
    echo $value['address']['regionCode'];
    echo "|";
    echo $value['address']['regionName'];
    echo "|";
    echo $value['address']['cityCode'];
    echo "|";
    echo $value['address']['cityName'];
    echo "|";
    echo $value['address']['index'];
    echo "|";
    echo $value['address']['street'];
    echo "|";
    echo $value['address']['streetAbbr'];
    echo "|";
    echo $value['address']['houseNo'];
    echo "|";
    echo $value['geoCoordinates']['latitude'];
    echo "|";
    echo $value['geoCoordinates']['longitude'];
    echo "|";
    echo "<br />";

	if ($id>0){
	foreach ($value['schedule'] as $val2) {

//	print_r($val2);
	if (array_key_exists("timetable",$val2)){

	if (!is_array($val2['timetable'][0])) {
		$t=$val2['timetable'];
		unset($val2['timetable']);
		$val2['timetable'][0]=$t;
		}

    foreach ($val2['timetable'] as $val3) {


	$sql="select row_id from postoperation where name='".$val2['operation']."'";
	//echo $sql;
	$sql=mysqli_query($connection,$sql);
	$postoperation_id=0;
	if (mysqli_num_rows($sql)>0){
		$sqlg=mysqli_fetch_row($sql);
		$postoperation_id=$sqlg[0];
	}
	else {
		$sql="insert into postoperation set name='".$val2['operation']."'";
		$sql=mysqli_query($connection,$sql);
		$postoperation_id=mysqli_insert_id($connection);
	}

	$sql="select post_id from postwork where post_id=".intval($id)." and day='".$val3['weekDays']."' and postoperation_id=".$postoperation_id;
	//echo $sql;
	$sql=mysqli_query($connection,$sql);
	if (mysqli_num_rows($sql)==0){
		$sql="insert into postwork set post_id=".intval($id).", day='".$val3['weekDays']."', `time`='".$val3['workTime']."', postoperation_id=".$postoperation_id.", flupd=1";
		//echo $sql;
		$sql=mysqli_query($connection,$sql);
	}
	else {
		$sql="update postwork set del=0, flupd=1, `time`='".$val3['workTime']."' where post_id=".intval($id)." and day='".$val3['weekDays']."' and postoperation_id=".$postoperation_id;
		//echo $sql;
		$sql=mysqli_query($connection,$sql);
	}

    echo $val2['operation'];
    echo "|";
   	echo $val3['weekDays'];
    echo "|";
   	echo $val3['workTime'];
    echo "|";
    echo "<br />";
    }
	}
	}
	}

    echo print_r($value['schedule']);
    echo "|";
    echo "<span style='color:#f00;'>".$err."</span>";
    echo "<br />";
    echo "<br />";
}

if ($kol>100){
	$sql=mysqli_query($connection,"update post set del=1 where flupd=0 and posttype_id=1 and post.flkurier<>2");
	$sql=mysqli_query($connection,"update post, postwork set postwork.del=1 where postwork.flupd=0 and post.posttype_id=1 and postwork.post_id=post.row_id and post.flkurier<>2");
}

}










//------------------------���� �������� �� ������ ������ ���----------------------
if ($flcalc==1){
$sql=mysqli_query($connection,"update post set flupd=0 where posttype_id=1 and post.flkurier=2");

$weight=array(5,10,25,50,100,150,200,250,300,400,500,600,700,800,900,1000,1500,2000);
foreach ($weight as $val){
$sql="select post.row_id, city.row_id, city.dpdcityid, post.name, city.city
 ,(select min(postcalc.dataupd) from postcalc where postcalc.post_id=post.row_id) as ord
 from post, city
 where post.del=0 and post.city_id=city.row_id and post.posttype_id=1 and post.flkurier<>2
 order by ord";
//echo $sql;
//exit;
$sql=mysqli_query($connection,$sql);
$kol=0;
$ctr=0;
while ($sqlg=mysqli_fetch_row($sql)){
	?><?=$sqlg[3]?> (<?=$val?>)<?
	$ctr++;
	if ($ctr>10) exit;
	$arData=array();
	$arData['delivery']['cityId']=$sqlg[2];
	$arData['weight'] = $val;
	$arData['serviceCode'] = "ECN";
	$ret=$dpd->getServiceCost($arData);
	$terr="";
	if (trim($dpd->arMSG['str'])!="") {
		$terr=mb_convert_encoding($dpd->arMSG['str'],'windows-1251','utf-8');
		echo "ERROR: ".$terr."<br>";
		}
	if (strpos($terr,'��������� ������')!==false){
		echo "!!!!!";
		}
//	echo "!!!!";
//	print_r($dpd->arMSG);
//	echo "!!!!";
//	print_r($ret);
//	echo "<br>";
//	exit;

	if (intval($ret['cost'])>0){
		$kol++;
		$sql2="select row_id from postcalc where post_id=".intval($sqlg[0])." and weight='".$val."' and flkurier=0 and postservice_id=2";
		//echo $sql2;
		$sql2=mysqli_query($connection,$sql2);
		if (mysqli_num_rows($sql2)==0){
			$sql2="insert into postcalc set post_id=".intval($sqlg[0]).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."',flkurier=0, postservice_id=2, dataupd=now()";
			//echo $sql2;
			$sql2=mysqli_query($connection,$sql2);
		}
		else {
			$sql2g=mysqli_fetch_row($connection,$sql2);
			$id2=$sql2g[0];
			$sql2="update postcalc set post_id=".intval($sqlg[0]).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."', postservice_id=2, dataupd=now() where row_id=".$id2;
			//echo $sql2;
			$sql2=mysqli_query($connection,$sql2);
		}
	}

	echo "�������� ��������";
	$arData=array();
	$arData['delivery']['cityId']=$sqlg[2];
	$arData['weight'] = $val;
	$arData['selfDelivery'] = false;
	$arData['serviceCode'] = "ECN";
	$ret=$dpd->getServiceCost($arData);
	$terr="";
	if (trim($dpd->arMSG['str'])!="") {
		$terr=mb_convert_encoding($dpd->arMSG['str'],'windows-1251','utf-8');
		echo "ERROR: ".$terr."<br>";
		}
//	exit;
	//print_r($ret);
	//print_r($dpd->arMSG);
	echo "<br>";
	//exit;

	if (intval($ret['cost'])>0){
		$kol++;
		$sql2="select row_id from postcalc where post_id=".intval($sqlg[0])." and weight='".$val."' and flkurier=1";
		//echo $sql2;
		$sql2=mysqli_query($connection,$sql2);
		if (mysqli_num_rows($sql2)==0){
			$sql2="insert into postcalc set post_id=".intval($sqlg[0]).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."',flkurier=1, postservice_id=2, dataupd=now()";
			//echo $sql2;
			$sql2=mysqli_query($connection,$sql2);
		}
		else {
			$sql2g=mysqli_fetch_row($sql2);
			$id2=$sql2g[0];
			$sql2="update postcalc set post_id=".intval($sqlg[0]).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."', postservice_id=2, dataupd=now() where row_id=".$id2;
			//echo $sql2;
			$sql2=mysqli_query($connection,$sql2);
		}
	}
}

//----------------������� ������ ���������, ��� ��� ��� � ����
$sql="delete from postcalc where postcalc.price=0 and postcalc.days=0 and exists (select * from post where post.row_id=postcalc.post_id and post.posttype_id=1)";
$sql=mysqli_query($connection,$sql);

$kol=0;
//------------�������� ��������� �������� (��� ������ � ������)
$sql="select (select post.row_id from post where post.city_id=city.row_id and post.posttype_id=1 and post.flkurier=2 limit 1)
, city.row_id, city.dpdcityid, city.city, city.city
, (select post.dataupd from post where post.city_id=city.row_id and post.posttype_id=1 and post.flkurier=2 limit 1) as ord from city where city.del=0 and city.dpdcode<>'' and not exists (select * from post where post.del=0 and post.city_id=city.row_id and post.posttype_id=1 and flkurier<>2) order by ord limit 2000";
//echo $sql;
$sql=mysqli_query($connection,$sql);
while ($sqlg=mysqli_fetch_row($connection,$sql)){

echo "�������� �������� � �����, ��� ��� ������ ������";
$arData=array();
$arData['delivery']['cityId']=$sqlg[2];
$arData['weight'] = $val;
$arData['selfDelivery'] = false;
$arData['serviceCode'] = "PCL";
$ret=$dpd->getServiceCost($arData);
//print_r($ret);
//print_r($dpd->arMSG);
echo "<br>";
//exit;
if (intval($ret['cost'])>0){
	$pid=$sqlg[0];
	if ($pid==0){
		$sql2="insert into post set flupd=1, flkurier=2, store1_id=1, city_id=".intval($sqlg[1]).", posttype_id=1, name='�������� �������� � �.".$sqlg[4]."', dataupd=NOW()";
		$sql2=mysqli_query($connection,$sql2);
		$pid=mysqli_insert_id($connection);
	}
	else {
		$sql2="update post set flupd=1, del=0, dataupd=NOW() where row_id=".$pid;
		$sql2=mysqli_query($connection,$sql2);
	}


	$kol++;
	$sql2="select row_id from postcalc where post_id=".intval($pid)." and weight='".$val."' and flkurier=1";
	//echo $sql2;
	$sql2=mysqli_query($connection,$sql2);
	if (mysqli_num_rows($sql2)==0){
		$sql2="insert into postcalc set post_id=".intval($pid).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."',flkurier=1, postservice_id=1, dataupd=now()";
		//echo $sql2;
		$sql2=mysqli_query($connection,$sql2);
	}
	else {
		$sql2g=mysqli_fetch_row($connection,$sql2);
		$id2=$sql2g[0];
		$sql2="update postcalc set post_id=".intval($pid).", weight='".$val."', price='".$ret['cost']."', days='".$ret['days']."', postservice_id=1, dataupd=now() where row_id=".$id2;
		//echo $sql2;
		$sql2=mysqli_query($connection,$sql2);
	}
}

}
//------



}

if ($kol>100){
//	$sql=mysql_query("update post set del=1 where flupd=0 and posttype_id=1 and post.flkurier=2");
	$sql="update const set `data`=NOW() where row_id=3";
	//echo $sql;
	$sql=mysqli_query($connection,$sql);
}


}
//----------------
//print_r($ret);
?>

