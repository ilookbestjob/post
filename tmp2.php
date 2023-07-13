<?
header("Content-Type: text/html; charset=windows-1251");

$kk="";
if (isset($_GET['k'])) $kk=$_GET['k'];
if ($kk!="KLJASLDFBHjwerjhgazsfJGAJSGDJAGAJSF") exit;

//http://nord.ru/post/post_api.php?k=KLJASLDFBHjwerjhgazsfJGAJSGDJAGAJSF&a=10&t=1&id=&c1=112&c2=87&tn= 4105&o=25145&p=1140&dp=359&vid=1&pp=64&po=0&fio=Кутыркин Виктор Валентинович&ph=+7 (909) 562-08-04&fio2=Сулима Д.В.&ph2=8-902-770-65-82&contr=ООО "Нордком"&contr2=Кутыркин Виктор Валентинович&adr=64: Мурманск - терминал (Инженерная, 22)&adr2=г. Петрозаводск, пр. Лесной, д. 51, оф. 217&adr_ul=Мурманская ул&adr2_ul=Лесной пр-кт&adr_d=&adr2_d=51&adr_k=&adr2_k=&adr_kv=&adr2_kv=217&adr_c=Инженерная, 22&adr2_c=&dz=03.04.19&m=2.6|10|7|60|RN|

//phpinfo();

//ini_set("display_errors",1);
//error_reporting(E_ALL);

set_time_limit (0);
ini_set('max_execution_time', 0);
ignore_user_abort(true);
ini_set('memory_limit', '2200M');

include ('../conf.php');
mysql_connect($sServer, $sUser, $sPass);
mysql_select_db("nordcom");
mysql_query("set names cp1251");
require_once('ft.php');

require_once('dl/dlclient.php');
require_once('dpd/dpd_service.class.php');
require_once('pec/pecom_kabinet.php');

$jdeuser="2252130865107604";
$jdetoken="936854124454170776";

$Boxtoken="35503.rnpqabfa";
require_once('boxberry/boxberry.php');

include('address.parser.php');

$adrr=array();
$adrr2=array();

if (isset($_GET['a'])) $action=$_GET['a']; else $action=0;
if (isset($_GET['t'])) $tk=$_GET['t']; else $tk=0;
if (isset($_GET['id'])) $id=trim($_GET['id']); else $id=0;
if (isset($_GET['o'])) $order=trim($_GET['o']); else $order="";
if (isset($_GET['tn'])) $trnakl=$_GET['tn']; else $trnakl="";
if (isset($_GET['nz'])) $nz=$_GET['nz']; else $nz="";
if (isset($_GET['p'])) $price=$_GET['p']; else $price="0";
if (isset($_GET['vid'])) $vid=$_GET['vid']; else $vid="0";
if (isset($_GET['dp'])) $deliveryprice=$_GET['dp']; else $deliveryprice="0";
if (isset($_GET['pp'])) $ppol=$_GET['pp']; else $ppol="0";
if (isset($_GET['po'])) $potpr=$_GET['po']; else $potpr="0";
if (isset($_GET['fio'])) $fio=$_GET['fio']; else $fio="";
if (isset($_GET['fio2'])) $fio2=$_GET['fio2']; else $fio2="";
if (isset($_GET['m'])) $mest=$_GET['m']; else $mest="";
if (isset($_GET['contr'])) $contr=$_GET['contr']; else $contr="";
if (isset($_GET['contr2'])) $contr2=$_GET['contr2']; else $contr2="";
if (isset($_GET['dz'])) $datepickup=$_GET['dz']; else $datepickup="";
if (isset($_POST['n'])) $nom=$_POST['n']; else $nom="";

if (isset($_GET['adr'])) $adr=$_GET['adr']; else $adr="";
if (isset($_GET['adr2'])) $adr2=$_GET['adr2']; else $adr2="";
if (isset($_GET['adr_ul'])) $adrr['street']=$_GET['adr_ul'];
if (isset($_GET['adr2_ul'])) $adrr2['street']=$_GET['adr2_ul'];
if (isset($_GET['adr_d'])) $adrr['house']=$_GET['adr_d'];
if (isset($_GET['adr2_d'])) $adrr2['house']=$_GET['adr2_d'];
if (isset($_GET['adr_k'])) $adrr['korpus']=$_GET['adr_k'];
if (isset($_GET['adr2_k'])) $adrr2['korpus']=$_GET['adr2_k'];
if (isset($_GET['adr_kv'])) $adrr['office']=$_GET['adr_kv'];
if (isset($_GET['adr2_kv'])) $adrr2['office']=$_GET['adr2_kv'];
if (isset($_GET['adr_c'])) $adrr['comm']=$_GET['adr_c'];
if (isset($_GET['adr2_c'])) $adrr2['comm']=$_GET['adr2_c'];
//echo "err=!!!".$action.":".$nom;
//exit;

//echo "!!!".$_GET['n'];
if (isset($_GET['ph'])) $phone=$_GET['ph']; else $phone="";
$phone=GetCifr2($phone);
if (strlen($phone)==11) $phone=substr($phone,1);
//if (strlen($phone)==10) $phone=substr($phone,1);
if (strlen($phone)==9) $phone="9".$phone;
if (strlen($phone)!=10) $phone="###";
//echo $phone;
//exit;

if (isset($_GET['ph2'])) $phone2=$_GET['ph2']; else $phone2="";
$phone2=GetCifr2($phone2);
//echo $phone;
if (strlen($phone2)==11) $phone2=substr($phone2,2);
if (strlen($phone2)==10) $phone2=substr($phone2,1);
if (strlen($phone2)==9) $phone2="9".$phone2;
if (strlen($phone2)!=10) $phone2="###";

if (isset($_GET['c1'])) $city1=intval($_GET['c1']); else $city1=112;
if (isset($_GET['c2'])) $city2=intval($_GET['c2']); else $city2=0;
if (isset($_GET['c1n'])) $city1name=$_GET['c1n']; else $city1name="Петрозаводск";
if (isset($_GET['c2n'])) $city2name=$_GET['c2n']; else $city2name="";
if (isset($_GET['w'])) $weight=GetCifr2($_GET['w']); else $weight="";
if (isset($_GET['v'])) $volume=GetCifr2($_GET['v']); else $volume="";
//if (isset($_GET['m'])) $mest=GetCifr2($_GET['m']); else $mest=1;


if ($action==0) {echor("err=Не указанно действие!"); exit;};


$sql="INSERT INTO `posthist` (`data`,`a`,`c1`,`c2`,`t`,`o`,`w`,`v`,`ret`) VALUE (NOW(),'".$action."','".$city1."','".$city2."','".$tk."','".$order."','".$weight."','".$volume."','')";
//echo $sql;
$sql=mysql_query($sql);
$idh=mysql_insert_id();
//echo $idh;


//--------------Получить расчет по доставке----------------
if ($action==1) {
	if ($city1==0) {echor("err=Не указан город-отправитель!\r\n"); exit;};
	if ($city2==0) {echor("err=Не указан город-получатель!\r\n"); exit;};
	if ($weight==0) {echor("err=Не указан вес!\r\n"); exit;};
	if ($volume==0) {echor("err=Не указан объем!\r\n"); exit;};

    $tkl=array();
    if ($tk==0) {
    	$tkl[]=1;
		$tkl[]=2;
		$tkl[]=3;
		$tkl[]=4;
    	}
    	else $tkl[]=$tk;

	foreach ($tkl as $tk){
	//------------------DPD-----------
	if ($tk==1) {
		$dpd = new DPD_service();
		$sql="select post.row_id, city.row_id, city.dpdcityid, post.name, post.street, post.house from post, city where post.del=0 and post.city_id=city.row_id and post.posttype_id=1 and city.row_id=".$city2;
		$sql=mysql_query($sql);
		while ($sqlg=mysql_fetch_row($sql)){
        if (intval($sqlg[2])==0) {echor("err=Неверный код города-получателя [DPD]!\r\n"); exit;};
        $arData=array();
		$arData['delivery']['cityId']=intval($sqlg[2]);
		$arData['weight'] = $weight;
		$arData['volume'] = $volume;
		$arData['serviceCode'] = "ECN";

		if ($adr2!="") {
			$arData['selfDelivery']=false;
			}


		$ret=$dpd->getServiceCost($arData);
		$price=$ret['cost'];
		$days=$ret['days'];
		$comm=$ret['serviceName'];
		$adr=GetStr($sqlg[3].": ".$sqlg[4].", ".$sqlg[5],false);
		echor("1|".$tk."|".$price."|".$days."|".$adr."|".$comm."\r\n");
		}
	}
	//------------------ЖелДор-----------
	if ($tk==2) {
		$sql="select post.row_id, post.code, post.name, post.street from post where post.del=0 and post.posttype_id=2 and post.city_id=".$city2;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
        if (intval($sqlg[1])==0) {echor("err=Неверный код города-получателя [JDE]!\r\n"); exit;};

		$dop="";
		if ($adr2!="") {
			$dop="&delivery=1";
			}

		$url='http://apitest.jde.ru:8000/calculator/price?from=1125899906842728&to='.$sqlg[1].'&weight='.$weight.'&volume='.$volume.'&quantity='.$mest.$dop;
		echo $url;
		$f=GetFile ($url , 0, "");
		$ret=json_decode($f, true);
		$price=$ret['price'];
		$days=$ret['maxdays'];
		$comm="";
		$adr=GetStr($sqlg[2].": ".$sqlg[3],false);
		echor("1|".$tk."|".$price."|".$days."|".$adr."|".$comm."\r\n");
	}
	//------------------Деловые линии-----------
	if ($tk==3) {
		$delline = new DLClient('4518ADB2-731A-11E5-A6BB-00505683A6D3', 'https://api.dellin.ru', 'json', 'array', 'array');
		$sql="select post.row_id, post.code, post.name, post.street from post where post.del=0 and post.posttype_id=3 and post.city_id=".$city2;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
        if (intval($sqlg[0])==0) {echor("err=Неверный код города-получателя [DL]!\r\n"); exit;};
		$kladr=$sqlg[1];
		$kladr=substr($kladr,0,strpos($kladr,"|"));

		$flad=false;
		if ($adr2!="")$flad=true;

		$request = array (
	    "derivalPoint" =>     "1000000100000000000000000",
	    "derivalDoor" =>      false,
	    "arrivalPoint" =>     $kladr,
	    "arrivalDoor" =>      $flad,
	    "sizedVolume" =>      $volume,
	    "sizedWeight" =>      $weight,
	    "quantity"=> 		  $mest
		);
		$ret = $delline->calculator($request);
		$price=$ret['price'];
		$days=$ret['time']['value'];
	    $terml=$ret['arrival']['terminals'];
	    foreach ($terml as $tmp){
	    	$comm=GetStr($tmp['address']);
			$adr=GetStr($sqlg[2].": ".$sqlg[3],false);
			echor("1|".$tk."|".$price."|".$days."|".$adr."|".$comm."\r\n");
        }


	}
	//------------------ПЭК-----------
	if ($tk==4) {
	$sdk = new PecomKabinet('vladrmg', '528E8D5F9A553C5FFB91970D6CD4A688983644E3');
	$sql="select post.row_id, post.code, post.name, post.street from post where post.del=0 and post.posttype_id=4 and post.city_id=".$city2;
	$sql=mysql_query($sql);
	$sqlg=mysql_fetch_row($sql);
	if (intval($sqlg[0])==0) {echor("err=Неверный код города-получателя [PEC]!\r\n"); exit;};
	$bitrix=$sqlg[1];
	$bitrix=substr($bitrix,0,strpos($bitrix,"-"));

	$flad=false;
	if ($adr2!="")$flad=true;

	$r=array('senderCityId' => 464,
	'receiverCityId' => $bitrix,
	'senderDistanceType' => 0,

    'isOpenCarSender'=>false, // Растентовка отправителя [Boolean]
    'isDayByDay'=>false, // Необходим забор день в день [Boolean]
    'isOpenCarReceiver'=>false, // Растентовка получателя [Boolean]
    'receiverDistanceType'=>0, // Тип доп. услуг отправителя [Number]
                              // кодируется аналогично senderDistanceType
    'isHyperMarket'=>false, // признак гипермаркета [Boolean]
    'isInsurance'=>false, // Страхование [Boolean]
	//   'isInsurancePrice'=>234.15, // Оценочная стоимость, руб [Number]
    'isPickUp'=>false, // Нужен забор [Boolean]
//    'calcDate'=> э2016-10-23',
    'isDelivery'=>$flad, // Нужна доставка [Boolean]
		'Cargos' => array(array(
	          'length' => pow($volume, 1/3), // Длина груза, м [Number]
	      	  'width' => pow($volume, 1/3), // Ширина груза, м [Number]
		      'height' => pow($volume, 1/3), // Высота груза, м [Number]
		      'volume' => $volume, // Объем груза, м3 [Number]
		      'isHP' => false, // Жесткая упаковка [Boolean]
		      'sealingPositionsCount' => 0, // Количество мест для пломбировки [Number]
		      'weight' => $weight, // Вес, кг [Number]
		      'overSize' => false // Негабаритный груз [Boolean]
			))
		);
	$ret = $sdk->call('calculator', 'CALCULATEPRICE',$r);
	//print_r($ret);
	$price=0;
	if ($ret->transfers[0]->transportingType==1) $price=$ret->transfers[0]->services[0]->cost;
	$days=0;
   	$comm="";
	$adr=GetStr($sqlg[2].": ".$sqlg[3],false);
	echor("1|".$tk."|".$price."|".$days."|".$adr."|".$comm."\r\n");
	$sdk->close();
	}
	//-----------------------------------
	}
	exit;
}
//--------------Конец: Получить расчет по доставке----------------





//--------------Получить статус заказа----------------
if ($action==2) {
	if ($order=="")	{echor("err=Не указан № заказа!\r\n"); exit;};
	if ($tk==0) {echor("err=Не указана Транспортная компания!\r\n"); exit;};
    $flyes=false;
	//------------------Наш перевозчик-----------
	if ($tk==6) {
	    $flyes=true;
		$sql="select deliveryakt.nomer, deliveryakt.sum
		, case when (locate('Склад:',delivery.source)>0 or locate('Северный ветер',delivery.source)>0) and (locate('Склад:',delivery.destination)>0 or locate('Северный ветер',delivery.destination)>0) then 1+sum(delivery.weight) else 0 end as sklskl
		, deliveryzak.data
		from deliveryakt, delivery, deliveryzak
		where delivery.row_id=".intval($order)." and delivery.del=0 and delivery.deliveryzak_id=deliveryakt.deliveryzak_id
		and deliveryzak.row_id=delivery.deliveryzak_id
		order by deliveryakt.dataadd desc";
//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);

		$state_id=1;
		if (trim($sqlg[0])!="") $state_id=13;
			else {
			$sql="select case when (locate('Склад:',delivery.source)>0 or locate('Северный ветер',delivery.source)>0) and (locate('Склад:',delivery.destination)>0 or locate('Северный ветер',delivery.destination)>0) then 1+sum(delivery.weight) else 0 end as sklskl
			, deliveryzak.data
			from delivery, deliveryzak
			where delivery.row_id=".intval($order)." and delivery.del=0 and deliveryzak.row_id=delivery.deliveryzak_id";
			$sql=mysql_query($sql);
			$sqlg=mysql_fetch_row($sql);

            $sklskl=$sqlg[0];
			if ($sklskl>0){
				$sql="select deliveryakt.nomer, deliveryakt.sum
				from deliveryakt, delivery, deliveryzak
				where TO_DAYS(delivery.data)=TO_DAYS('".$sqlg[1]."') and deliveryzak.row_id=delivery.deliveryzak_id and delivery.del=0 and delivery.deliveryzak_id=deliveryakt.deliveryzak_id
				and case when (locate('Склад:',delivery.source)>0 or locate('Северный ветер',delivery.source)>0) and (locate('Склад:',delivery.destination)>0 or locate('Северный ветер',delivery.destination)>0) then true else false end
				order by deliveryakt.dataadd desc";
				$sql=mysql_query($sql);
				$sqlg=mysql_fetch_row($sql);
                if (trim($sqlg[0])!="") $state_id=13;
				}
			}

		echor ($state_id."|".$comm);
	}

	//------------------Деловые линии-----------
	if ($tk==3) {
	    $flyes=true;
//		echo "!!!!!";
		$delline = new DLClient('4518ADB2-731A-11E5-A6BB-00505683A6D3', 'https://api.dellin.ru', 'json', 'array', 'array');
		$request = array ("docid" => $order);
		$ret = $delline->tracker($request);
//		var_dump($ret);
//		echo "!!!!!!!!2";
//		print_r($ret);
		$sql="select row_id, name from poststate where pref LIKE('%".$ret['state']."|%')";
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$state_id=intval($sqlg[0]);
		if ($state_id==0) {echor("err=Неверный статус заказа '".$ret['state']."' (СКАЗАТЬ ВЛАДУ!)!\r\n"); exit;};
		$comm=GetStr($ret['arrival_date']);
		if ($comm!="") $comm="Дата доставки груза до адреса: ".$comm;
		$sql="select row_id from postorder where posttype_id=".$tk." and `order`='".$order."' and poststate_id=".$state_id." and comm=".inputclean($comm);
//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		if ($sqlg[0]==0){
			$sql="insert into postorder set data=NOW(), poststate_id=".$state_id.", posttype_id=".$tk.", `order`='".$order."', comm=".inputclean($comm);
			$sql=mysql_query($sql);
			}
		echor ($state_id."|".$comm);
	}
	//------------------Boxberry-----------
	if ($tk==14) {
	    $flyes=true;
		$url='http://api.boxberry.de/json.php?token='.$Boxtoken.'&&method=ListStatusesFull&ImId='.$order;
		$handle = fopen($url, "rb");
		$contents = stream_get_contents($handle);
		fclose($handle);
		$data=json_decode($contents,true);
		if(count($data)<=0 or $data['err']){
			echo "err=".mb_convert_encoding($data['err'],'windows-1251','utf-8');
		}
		else {

		//print_r($data);
		//exit;
		//$stat="";
		foreach ($data['statuses'] as $zn){
		$stat=mb_convert_encoding($zn['Name'],'windows-1251','utf-8');
		$comm=$zn['Date'];
//		echo "".$stat."-".$comm."<br>";
		}

		//echor ($state_id."|".$comm);
//		echo ;
		$sql="select row_id, name from poststate where pref LIKE('%".$stat."|%')";
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$state_id=intval($sqlg[0]);
		if ($state_id==0) {echor("err=Неверный статус заказа '".$stat."' (СКАЗАТЬ ВЛАДУ!)!\r\n"); exit;};
        echor ($state_id."|".$comm);
	//		print_r($data);
//	    	echo $data['label'];
		}


	}
	//------------------ЖелДор-----------
	if ($tk==2) {
 	    $flyes=true;
		$url="https://api.jde.ru/vD/cargos/status?user=".$jdeuser."&token=".$jdetoken."&ttn=".trim($order);
		$ret=file_get_contents($url);
		$ret=json_decode($ret, true);
		$state=$ret['info']['cargostatus'];
//		echo $state;
		$comm="";

		$sql="select row_id, name from poststate where pref LIKE('%".$state."|%')";
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$state_id=intval($sqlg[0]);
		if ($state_id==0) {echor("err=Неверный статус заказа '".$state."' (СКАЗАТЬ ВЛАДУ!)!\r\n"); exit;};
		$sql="select row_id from postorder where posttype_id=".$tk." and `order`='".$order."' and poststate_id=".$state_id." and comm='".inputclean($comm)."'";
//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		if ($sqlg[0]==0){
			$sql="insert into postorder set data=NOW(), poststate_id=".$state_id.", posttype_id=".$tk.", `order`='".$order."', comm=".inputclean($comm);
			$sql=mysql_query($sql);
			}
		echor ($state_id."|".$comm);


		//print_r($ret);

		///Зарегился на http://cabinet.jde.ru
		// nordcom@mail.ru nord....02
		//Жду код подтвержения
		//https://api.jde.ru/dev/
	}
	//------------------DPD-----------
	if ($tk==1) {
	    $flyes=true;
		$dpd = new DPD_service();
	    $arData=array();
		$arData['dpdOrderNr']=$order;
//		$arData['weight'] = $val;
//		$arData['serviceCode'] = "ECN";
//		print_r($arData);
		$ret=$dpd->getStatesByDPDOrder($arData);
//		$ret=$dpd->getStatesByClient();
//		$ret=$dpd->getTerminalList();
//		if ($dpd->arMSG) print_r($dpd->arMSG);
//		print_r($ret);
//		echo "<br><br>";
		$tmp=$ret['states'];
//		print_r($tmp);
		$state="";
		if ($tmp['newState']) {
	    	$state=trim($tmp['newState']);
	    	$comm=trim($tmp['planDeliveryDate']);
		}
		else {
	    foreach ($tmp as $zn){
//	    	print_r($zn);
//	    	echo $zn['newState']."<br>";
//	    	echo $zn['planDeliveryDate']."<br>";
	    	$state=trim($zn['newState']);
	    	$comm=trim($zn['planDeliveryDate']);
	 	}
	 	}
        if ($comm!="") $comm="Дата доставки груза до адреса: ".$comm;

		$sql="select row_id, name from poststate where pref LIKE('%".$state."|%')";
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$state_id=intval($sqlg[0]);
		if ($state_id==0) {echor("err=Неверный статус заказа '".$state."' (СКАЗАТЬ ВЛАДУ!)!\r\n"); exit;};
		$sql="select row_id from postorder where posttype_id=".$tk." and `order`='".$order."' and poststate_id=".$state_id." and comm='".inputclean($comm)."'";
//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		if ($sqlg[0]==0){
			$sql="insert into postorder set data=NOW(), poststate_id=".$state_id.", posttype_id=".$tk.", `order`='".$order."', comm=".inputclean($comm);
			$sql=mysql_query($sql);
			}
		echor ($state_id."|".$comm);

	}

if ($flyes==false)	{
	echor ("#02. ТК (".$tk.") не подключена на получение статуса");
	}
}



//--------------Обновить пункты выдачи у города----------------
if ($action==3) {
	if ($tk==0) {echor("err=Не указана Транспортная компания!\r\n"); exit;};
	if ($city1==0) {echor("err=Не указан город!\r\n"); exit;};
	//------------------Boxberry-----------
	if ($tk==14) {
		BoxPointGet($city1);
	}
}
//--------------КОНЕЦ: Обновить пункты выдачи у города----------------


//--------------Оформить заказ----------------
if ($action==10) {

	if ($tk==0) {echor("err=Не указана Транспортная компания!\r\n"); exit;};
	//------------------Boxberry-----------
	if ($tk==14) {

		if (count($adrr)>1) {
			$adr="";
			if ($adrr['street']!="") $adr.=$adrr['street'].", ";
			if ($adrr['house']!="") $adr.="дом ".$adrr['house'].", ";
			if ($adrr['korpus']!="") $adr.="корп. ".$adrr['korpus'].", ";
			if ($adrr['office']!="") $adr.="кв./офис: ".$adrr['office'].", ";
			if ($adrr['comm']!="") $adr.=" (".$adrr['comm']."), ";
            $adr=trim(substr($adr,0,strlen($adr)-2));
			}
		if (count($adrr2)>1) {
			$adr2="";
			if ($adrr2['street']!="") $adr2.=$adrr2['street'].", ";
			if ($adrr2['house']!="") $adr2.="дом ".$adrr2['house'].", ";
			if ($adrr2['korpus']!="") $adr2.="корп. ".$adrr2['korpus'].", ";
			if ($adrr2['office']!="") $adr2.="кв./офис: ".$adrr2['office'].", ";
			if ($adrr2['comm']!="") $adr2.=" (".$adrr2['comm']."), ";
            $adr2=trim(substr($adr2,0,strlen($adr2)-2));
			}

//		echo "!!".$id;
		$adr=mb_convert_encoding($adr,'utf-8','windows-1251');
		$SDATA=array();
		$SDATA['updateByTrack']=$id;
		$SDATA['order_id']=mb_convert_encoding("№".$order,'utf-8','windows-1251');
//		$SDATA['PalletNumber']='Номер палеты';
//		$SDATA['barcode']='Штрих-код заказа';
		$SDATA['price']=$price;
		$SDATA['payment_sum']=0;
		$SDATA['delivery_sum']=$deliveryprice;
		$SDATA['vid']=$vid;

		if ($vid==2) $ppol="";

		$sql="select post.code from post where row_id=".intval($ppol);
	//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$ppolb=$sqlg[0];

		$sql="select post.code from post where row_id=".intval($potpr);
	//		echo $sql;
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$potprb=$sqlg[0];


		$SDATA['shop']=array(
		'name'=>$ppolb,
		'name1'=>$potprb
		);

		$SDATA['customer']=array(
		'fio'=>$fio,
		'phone'=>$phone,
//		'phone2'=>'Доп. номер телефона',
//		'email'=>'E-mail для оповещений',
//		'name'=>'Наименование организации',
//		'address'=>'Адрес',
//		'inn'=>'ИНН',
//		'kpp'=>'КПП',
//		'r_s'=>'Расчетный счет',
//		'bank'=>'Наименование банка',
//		'kor_s'=>'Кор. счет',
//		'bik'=>'БИК'
		);

		if ($vid==2){
			$sql="select city.city from city where row_id=".intval($city2);
	//		echo $sql;
			$sql=mysql_query($sql);
			$sqlg=mysql_fetch_row($sql);
			$city2name=$sqlg[0];

  			$SDATA['kurdost'] = array(
//	  		'index' => 'Индекс',
	  		'citi' => $city2name,
	  		'addressp' => $adr,
//	  		'timesfrom1' => 'Время доставки, от',
//	  		'timesto1' => 'Время доставки, до',
//	  		'timesfrom2' => 'Альтернативное время, от',
//	  		'timesto2' => 'Альтернативное время, до',
//	  		'timep' => 'Время доставки текстовый формат',
//	  		'delivery_date' => 'Дата доставки от +1 день до +5 дней от текущей даты (только для Москвы, МО и Санкт-Петербурга)',
//	  		'comentk' => 'Комментарий'
	  		);
	 	}


		$arr=array();
		foreach (explode("|RN|",$nom) as $zn){
		if ($zn!=""){
		$zn2=explode("|",$zn);
		if (is_array($zn2)){
			$arr[]=array(
	    	'id'=>$zn2[0],
	    	'name'=>$zn2[1],
	    	'UnitName'=>$zn2[3],
	    	'nds'=>20,
	    	'price'=>$zn2[4],
	    	'quantity'=>$zn2[2]
	    	);
		}
		}
		}

  		$SDATA['items']=$arr;

		$i=0;
		$arr=array();
		foreach (explode("|RN|",$mest) as $zn){
		if ($zn!=""){
		$zn2=explode("|",$zn);
		if (is_array($zn2)){
			$pref="";
			if ($i>0) $pref=$i;
		    $arr['weight'.$pref]=intval($zn2[0]*1000);
			$i++;
		}
		}
		}

		$SDATA['weights']=$arr;
	   	//$SDATA['weights']=array(
    	//'weight'=>'Вес 1-ого места',
    	//'weight2'=>'Вес 2-ого места',
    	//'weight3'=>'Вес 3-его места',
    	//'weight4'=>'Вес 4-ого места',
    	//'weight5'=>'Вес 5-ого места'
    	//);

//	   	print_r($SDATA);
//    	exit;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.boxberry.de/json.php');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		array(
		'token'=>$Boxtoken,
		'method'=>'ParselCreate',
		'sdata'=>json_encode($SDATA)
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($ch),1);

//		$url='http://api.boxberry.de/json.php?token='.$Boxtoken.'&method=ParselCreate';
//		$handle = fopen($url, "rb");
//		$contents = stream_get_contents($handle);
//		fclose($handle);
//		$data=json_decode($contents,true);
		if(count($data)<=0 or $data['err']){
		echo "err=".mb_convert_encoding($data['err'],'windows-1251','utf-8');
		}
		else {
		echo $data['track']."|".$data['label'];
		}
		//echo "err=";
		//print_r($data);
//  		print_r($data);
//  		print_r(mb_convert_encoding($data['err'],'windows-1251','utf-8'));


	}
	//------DPD-------
	if ($tk==1){

		$sql="select city.city from city where row_id=".intval($city1);
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$city1name=$sqlg[0];

		$sql="select city.city from city where row_id=".intval($city2);
		$sql=mysql_query($sql);
		$sqlg=mysql_fetch_row($sql);
		$city2name=$sqlg[0];

		//echo "!!".$contr1;
		$contr=cp($contr);
		$contr2=cp($contr2);

        $fio=cp($fio);
        $fio2=cp($fio2);


		$tarif="ECN";
		$sposob="ДТ";
		if ($vid==2) {
	//		$tarif="PCL";
			$tarif="ECN";
			$sposob="ДД";
			}

		$mestcount=0;
		$weight=0;
		foreach (explode("|RN|",$mest) as $zn){
		if ($zn!=""){
		$zn2=explode("|",$zn);
		if (is_array($zn2)){
			$mestcount++;
			$weight+=$zn2[0];
		}
		}
		}

       	$city1street="";
       	$city2street="";
       	$city1streetab="";
       	$city2streetab="";
       	$city1tcode="";
       	$city2tcode="";
       	$city1house="";
       	$city2house="";
       	$city1korpus="";
       	$city2korpus="";
       	$city1office="";
       	$city2office="";
       	$city1comm="";
       	$city2comm="";

		$city1street=trim(cp($adrr2['street']));
//		echo $city1street."!";
		if (strrpos($city1street,' ')!==false){
			$city1streetab=trim(substr($city1street,strrpos($city1street,' ')));
			$city1street=trim(substr($city1street,0,strrpos($city1street,' ')));
		}
//					echo $city1street;
		$city1house=cp($adrr2['house']);
		$city1korpus=cp($adrr2['korpus']);
		$city1office=cp($adrr2['office']);
		$city1comm=cp($adrr2['comm']);


		$ord=$nz;
        if ($order!="") $ord="И".$order;

        	if ($vid==2) {

//                   print_r($adrr);

					$city2street=trim(cp($adrr['street']));
					if (strrpos($city2street,' ')!==false){
						$city2streetab=trim(substr($city2street,strrpos($city2street,' ')));
						$city2street=trim(substr($city2street,0,strrpos($city2street,' ')));
						}
					$city2house=cp($adrr['house']);
					$city2korpus=cp($adrr['korpus']);
					$city2office=cp($adrr['office']);
					$city2comm=cp($adrr['comm']);
        		}
        		else {
       				$sql="select street, house, code from post where row_id=".intval(substr($adr,0,strpos($adr,":")));
					$sql=mysql_query($sql);
					$sqlg=mysql_fetch_row($sql);
					$city2street=$sqlg[0];
					$city2house=$sqlg[1];
					$city2tcode=$sqlg[2];
        		}



//        echo $city1street."|".$city1streetab."|".$city1house;

		$datepickup=date('Y-m-d',strtotime($datepickup));

		$dpd = new DPD_service();
		$arData = array();
		$arData['header'] = array( //отправтель
			'datePickup' => $datepickup, //дата того когда вашу посылку заберут
			'senderAddress' => array(
                  'terminalCode' => utf($city1tcode),
				  'name' => utf($contr),
                  'countryName' => utf('Россия'),
//				  'region' => 'Карелия',
                  'city' => utf($city1name),
                  'street' => utf($city1street),
                  'streetAbbr' => utf($city1streetab),
                  'house' => utf($city1house),
//                  'houseKorpus' => utf($city1korpus),
				  'office' => utf($city1office),
				  'extraInfo' => utf($city1comm),
                  'contactFio' => utf($fio2),
                  'contactPhone' => utf("8".$phone2)
			),
			'pickupTimePeriod' => '9-18'//время работы отправителя
			);

		if (trim($city1korpus)!="") $arData['header']['senderAddress']['houseKorpus']=utf($city1korpus);


		$arData['order'] = array(
			'orderNumberInternal' => utf($ord), // ваш личный код (я использую код из таблицы заказов ID)
               'serviceCode' => utf($tarif), // тариф
               'serviceVariant' => utf($sposob), // вариант доставки ДД - дверь  дверь
               'cargoNumPack' => utf($mestcount), //количество мест
               'cargoWeight' => utf($weight),// вес посылок
//               'cargoVolume' => $gas, // объём посылок
//               'cargoValue' => $select['OC'], // ОЦ
               'cargoCategory' => utf("крепеж"), // название товара через / товаров

			   'receiverAddress' => array( // информация о получателе
                  'terminalCode' => utf($city2tcode),
				  'name' => utf($contr2),
                  'countryName' => utf('Россия'),
                  'city' => utf($city2name),
//				  'region' => $region,
                  'street' => utf($city2street),
                  'streetAbbr' => utf($city2streetab),
//                  'streetAbbr' => $ul,
                  'house' => utf($city2house),
				  'office' => utf($city2office),
				  'extraInfo' => utf($city2comm),
                  'contactFio' => utf($fio),
                  'contactPhone' => utf("8".$phone)
			   ),
			   'cargoRegistered' => false
			);

		if (trim($city2korpus)!="") $arData['order']['receiverAddress']['houseKorpus']=utf($city2korpus);


		//if(isset($kv)){
		//$arData['order']['receiverAddress']['flat'] = $kv;	//если задана квартира записываем её
		//}
		//if ($sposob == 'ТТ'){
			//$arData['order']['receiverAddress']['terminalCode'] = $terminal; //если указан способ ТТ то указываем наш терминал который мы искали
		//}
		//$arData['order']['extraService'][0] = array('esCode' => 'EML', 'param' => array('name' => 'email', 'value' => $select["email"]) );
		//$arData['order']['extraService'][1] = array('esCode' => 'НПП', 'param' => array('name' => 'sum_npp', 'value' => $select["cena"]) );
		//$arData['order']['extraService'][2] = array('esCode' => 'ОЖД', 'param' => array('name' => 'reason_delay', 'value' => 'СООТ') ); // пример нескольких опций

//		print_r($arData);
//		exit;

//		$arRequest['orders'] = $arData; // помещаем запрос в orders

		$data = $dpd->createOrder($arData); //делаем запрос в DPD
		//print_r($dpd->arMSG);
//		print_r($data);
		if($data['orderNum']){
        	echo $data['orderNum']."|".$data['status'];
		}
		else {
				echo "err=".$data['errorMessage'];
		}
        exit;
//		print_r($ret);
//        print_r(mb_convert_encoding($dpd->arMSG['str'],'windows-1251','utf-8'));
		//$echo = stdToArray($ret); //функция из объекта в массив

		//if ($echo['return']['errorMessage'][0] == ''){
		//print_r ($echo['return']['orderNum'][0]); //выводим номер заказа (созданного)
		//}
		//else {
		//	print_r ($echo['return']['errorMessage'][0]); //выводим ошибки
		//}

	}
	//----------------------


}
//--------------КОНЕЦ: Оформить заказ----------------










//--------------Удалить заказ----------------
if ($action==11) {

	if ($tk==0) {echor("err=Не указана Транспортная компания!\r\n"); exit;};
	//------------------Boxberry-----------
	if ($tk==14) {

		$url='http://api.boxberry.de/json.php?token='.$Boxtoken.'&method=ParselDel&ImId='.$id;
		$handle = fopen($url, "rb");
		$contents = stream_get_contents($handle);
		fclose($handle);
		$data=json_decode($contents,true);
		if(count($data)<=0 or $data['err']){
			echo "err=".mb_convert_encoding($data['err'],'windows-1251','utf-8');
		}
		else {
			//print_r($data);
	    	echo "ok=".$data['text'];
		}
	}

	//------DPD-------
	if ($tk==1){
		$dpd = new DPD_service();
		$arData=array();
		$arData['cancel']= array(
			'orderNum'=>$id
		);

		$data = $dpd->cancelOrder($arData);
		if ($data['errorMessage']) echo $data['errorMessage'];
			else echo "ok=";
		//print_r($dpd->arMSG);
		//print_r($data);
	}

}
//--------------КОНЕЦ: Удалить заказ----------------




//--------------Создать АКТ----------------
if ($action==12) {

	if ($tk==0) {echor("err=Не указана Транспортная компания!\r\n"); exit;};
	//------------------Boxberry-----------
	if ($tk==14) {

	$url='http://api.boxberry.de/json.php?token='.$Boxtoken.'&method=ParselSend&ImIds='.$id;
	$handle = fopen($url, "rb");
	$contents = stream_get_contents($handle);
	fclose($handle);
	$data=json_decode($contents,true);
	if(count($data)<=0 or $data['err']){
		echo "err=".mb_convert_encoding($data['err'],'windows-1251','utf-8');
	}
	else {
//		print_r($data);
    	echo $data['label'];
	}
	}
}
//--------------КОНЕЦ: Создать АКТ----------------












//----------------------------
function cp($s){
return mb_convert_encoding($s,'windows-1251','utf-8');
}
//----------------------------
function utf($s){
return mb_convert_encoding($s,'utf-8','windows-1251');
}
//----------------------------
function GetCifr2($i){
$i=preg_replace('/[^\d.]/','',$i);
return $i;
}
//----------------------------
function GetStr($s,$fl=true){
if ($fl==true) $s=mb_convert_encoding($s,'windows-1251','utf-8');
$s=str_replace("\r"," ",$s);
$s=str_replace("\n"," ",$s);
$s=str_replace("|"," ",$s);
$s=str_replace("'","",$s);
return $s;
}
//----------------------------
function echor($s){
global $idh;
echo $s;
$sqlh="update `posthist` set ret=concat(ret,'".$s."') where row_id=".$idh;
//echo $sqlh;
$sqlh=mysql_query($sqlh);
}

//----------------------------
?>

