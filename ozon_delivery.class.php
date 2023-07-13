<?
//  https://docs.ozon.ru/api/rocket/

class ozon_delivery extends delivery_company
{

    //Переменные подключения к api
    private $secret = "6ziSXTn8rNNSYmFVs/bMgIK7YOXmrWOyFXXj0M90eYM=";
	private $client_id = "Principal_22203415677000_aa595957-14d7-44be-ace5-ebf3ae65f3ce";
	private $token;
    private $messagelog;
    private $detalization;
    private $postid;
    private $weights;
	private $from_id;
    private $delivery;
    private $priceslimit;
    private $volumes;
    private $name;
    private $logo;
    private $compareBase;
    private $Starttime;
    private $Endtime;
    private $Duration;

    public function __construct(array $weights, array $volumes, $postid, $priceslimit,  $logdetalization, array $logflags, $logo = null, $graph = true)
    {
		//echo round(floatval("1.0110000000018E+15"),4);
		//exit;

        $this->postid = $postid;
        $this->detalization = $logdetalization;
        $this->priceslimit = $priceslimit;
        $this->weights = $weights;
        $this->volumes = $volumes;
        $this->logo = $logo;
        $this->messagelog = new messagelog($this->postid, "", $logflags[0], $logflags[1], $logflags[2], $this->detalization);
        $this->delivery = new Delivery($this->weights, $this->postid, $this->detalization);
        $this->name = $this->delivery->getPostbyId($this->postid);
        $this->compareBase = new compareBase();		
		$this->get_token();
		$this->from_id=$this->get_from();
		//echo $from_id;
		//$this->save_city();
		//$this->save_terminalsdata(112);
		//$this->save_deliveryprices(112);


        if ($graph) {
            $pending = $this->trylockPost($this->postid);
            if (!$pending) $last_update = date('Y-m-d H:i:s', stat("id_" . $this->postid . ".txt")['ctime']);

            echo "<div style=\"width:auto;border-bottom:solid 1px #ccc;margin:0 150px;margin-bottom:15px;display:grid;grid-template-columns:200px,1fr \"><img src=\"" . $this->logo . "\" style=\"display:block;height:30px;width:auto;margin-bottom:5px\"><div style=\"display:flex;align-items:flex-end;justify-content:flex-end;\">" . ($pending ? "<div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 1);\">Получить все</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 2);\">Получить терминалы</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 3);\">Получить стоимость</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 4);\">Проверить</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 5);\">Сравнить</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 6);\">" . (!$this->delivery->checkstop($postid) ? "Разрешить" : "Стоп") . "</div>" : "Скрипт уже выполняется c $last_update<div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 6);\">Стоп</div>") . "</div></div>";
        }
    }	

	//--------------------------------------------------------------------------------------
	function ftMinutes($s){
		if (strlen($s)==1) $s="0".$s;
		return $s;
	}
	//--------------------------------------------------------------------------------------
    function save_terminalsdata($cityid = null)
    {
		if ($cityid==null) $fp = $this->lockPost($this->postid);	


		//Очищаем флаги обновления данных        
		
		$this->delivery->clear_UpdateFlags($cityid);			

		$nextPageToken="#";
		$k=0;
		$postcount=0;
		while ($nextPageToken!=""){
			//$nextPageToken="+AAAAA==";
//			$nextPageToken="9wAAAA==";

			//if ($nextPageToken=="#") $nextPageToken="KgAAAA==";

			$k++;
			echo "Номер цикла: ".$k."<br>";
			//if ($k==3) break;
			//Получаем терминалы			
			$Terminals = $this->get_terminals($nextPageToken, $cityid);
			if ($nextPageToken=="#") {
				echo "Всего терминалов: ".$Terminals->totalCount."<br>";
				//exit;
				}
			//print_r($Terminals);
			//exit;
			$nextPageToken=$Terminals->nextPageToken;
			echo "nextPageToken: ".$nextPageToken."!";
			//    APItools::displayResponseStructure("get_cities()", $Terminals);

			//print_r($Terminals);
			//exit;
			foreach ($Terminals->data as $terminal) {


				//Получаем  id  города из таблицы cityfias
				$city_id = $this->delivery->get_TownIdByFias($terminal->fiasGuid);				 
				if ($city_id==0) $city_id = $this->delivery->get_TownIdByName($terminal->settlement,$terminal->region,$terminal->postalCode);
				

				//обход результата
				if (($cityid) && ($cityid != $city_id)) continue;
				if ($city_id==0) {
					echo "------------------<br>";
					echo "Не найден город: " . $terminal->address . " (FIAS: " . $terminal->fiasGuid . ")<br>";
					continue;
				}
				
				$cityopt = $this->delivery->get_TownOptByID($city_id);

				$terminal_flnoact=0;
				if ($terminal->stateName!="Active") $terminal_flnoact=1;

				$terminal_flkurier=0;
				if ($terminal->objectTypeName=="Курьерская") $terminal_flkurier=1;
				if ($terminal->objectTypeName=="Постамат") $terminal_flkurier=4;

				//$terminalinfo = $this->get_getterminalinfo($terminal->Code);
				$terminal_id = $terminal->id;
				$terminal_code = "";
				if (isset($terminal->code)) $terminal_code = $terminal->code;
				$terminal_name = $terminal->name;
				if ($terminal_flkurier==1 || $terminal_flkurier==2) $terminal_name = "Адресная доставка в г." . $cityopt['name'] ." (" . $cityopt['region'] . ")";
				$terminal_street = $terminal->address;
				$terminal_house = "";
				$terminal_x = $terminal->lat;
				$terminal_y = $terminal->long;
				$terminal_phone = "";
				if (isset($terminal->phone)) $terminal_phone = $terminal->phone;
				$terminal_minprice = "";
				if (isset($terminal->minPrice)) $terminal_minprice = $terminal->minPrice;
				$terminal_maxprice = "";
				if (isset($terminal->maxPrice)) $terminal_maxprice = $terminal->maxPrice;			
				$terminal_limitload = ord($terminal->maxWeight/1000);
				$terminal_comm = "";
				if (isset($terminal->howToGet)) $terminal_comm = $terminal->howToGet;
				$terminal_flmoney=0;
				if ($terminal->isCashForbidden==0) $terminal_flmoney=1;

				$terminal_flterinal=$terminal->cardPaymentAvailable;
				


				$schedule = "";
				$postcount++;
				echo "------------------<br>";
				echo "cityid: $cityid<br>";
				echo "city_id: $city_id<br>";
				echo "Наименование: " . $terminal_name . "<br>";
				echo " ID: " . $terminal_id . "<br>";
				echo " Code: " . $terminal_code . "<br>";
				echo " Flnoact: " . $terminal_flnoact . "<br>";
				echo " FIAS: " . $terminal->fiasGuid . "<br>";
				echo " Street: " . $terminal_street . "<br>";
				echo " House: " . $terminal_house . "<br>";
				echo " X: " . $terminal_x . "<br>";
				echo " Y: " . $terminal_y . "<br>";
				echo " Flkurier: " . $terminal_flkurier . " (" . $terminal->objectTypeName . ")" . "<br>";
				echo " Phone: " . $terminal_phone . "<br>";
				echo " Limitload: " . $terminal_limitload . "<br>";
				echo " Minprice " . $terminal_minprice . "<br>";
				echo " Maxprice: " . $terminal_maxprice . "<br>";
				echo " Flmoney: " . $terminal_flmoney . "<br>";
				echo " Flterminal: " . $terminal_flterinal . "<br>";			
				echo " Comm: " . $terminal_comm . "<br>";

				///print_r($terminal->workingHours);

				$days=array();
				foreach ($terminal->workingHours as $dt){
					$dayn=date("N", strtotime($dt->date));					
					if ($dayn==1) $daynm="Пн";
					if ($dayn==2) $daynm="Вт";
					if ($dayn==3) $daynm="Ср";
					if ($dayn==4) $daynm="Чт";
					if ($dayn==5) $daynm="Пт";
					if ($dayn==6) $daynm="Сб";
					if ($dayn==7) $daynm="Вс";
					$time="";
					$zp="";
					foreach ($dt->periods as $tm){

						$time.=$tm->min->hours.":".$this->ftMinutes($tm->min->minutes)."-";
						$time.=$tm->max->hours.":".$this->ftMinutes($tm->max->minutes).$zp;
						$zp="; ";
					}					
					$days[$dayn]['days']=pow(2, ($dayn-1));
					$days[$dayn]['time']=$time;
				}

				//print_r($days);
				//echo "<br>";

				$terminal_comm=mb_convert_encoding($terminal_comm, 'windows-1251', 'utf-8');

				$post = $this->delivery->save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y, 
				array('flkurier' => $terminal_flkurier,
				'minprice' => $terminal_minprice, 
				'maxprice' => $terminal_maxprice, 
				'flmoney' => $terminal_flmoney, 
				'flmoney' => $terminal_flmoney, 
				'comm' => $terminal_comm, 
				'limitload' => $terminal_limitload
				));

				$this->delivery->save_Schedule($post, $days);

			}
		}

        $this->delivery->check_TerminalChanges();

        $this->messagelog->addLog(1, $this->postid, "Информация от таблице Post",  "API " . mb_convert_encoding($this->name['name'], 'utf-8', 'windows-1251') . " содержит следующую информацию: " . $this->delivery->checkFlags(), 0);


        if ($postcount>100) $this->delivery->set_DeleteFlags($cityid);

        if ($cityid==null) $this->unlockPost($fp, $this->postid);
 
	}

	//--------------------------------------------------------------------------------------
    function save_deliveryprices($city_id = null)
    {
	    $fp = $this->lockPost($this->postid);
        $Start=$this->delivery->get_current_date();
        $FIASes = $this->delivery->get_CitytoUpdatePrices($city_id);
        if (count($FIASes) == 0) {
            $this->messagelog->addLog(1, $this->postid, "Ошибка получения списка терминалов",  "Попытка получить  список терминалов для обновления цен не вернула ни одного результата", 0);
        }

        $this->messagelog->addLog(0, $this->postid, "информация об обновлении цен", "Получено следующее количество строк для обновления цен доставки:" . count($FIASes), 0);
        $ctr = 0;
        foreach ($FIASes as $FIAS) {
			$ctr++;
			if ($ctr > $this->priceslimit)  break;
			$ok=0;
			foreach ($this->weights as $weight) {				
				$daysdata = $this->get_days($FIAS->code);
				$days=$daysdata->days;
				
				$pricedata = $this->get_price($FIAS->code, $weight, false);
				$price=$pricedata->amount;

				if ($price>90) {
					echo  "ОК. Терминал ID=" . $FIAS->post_id . " (" . mb_convert_encoding($FIAS->city, 'utf-8', 'windows-1251') . "): цена " . $price . " вес " . ($weight / 1000) . " объем 0.1  срок " . $days . "</br>\r\n";
					$this->delivery->save_pricedata($FIAS->post_id, 0, $price, $weight / 1000, 0.1, $days, array());
					$ok++;
				} else {
					$this->messagelog->addLog(1, $this->postid, "Ошибка получения стоимости. Цена менее 90 руб.",  "Попытка получить информацию о стоимости доствки груза весом $weight до " . $FIAS->city . " завершилось неудачей.Ответ API:" . $pricedata->err, 0);
				}
			}
			if ($ok>0) $this->delivery->set_PriceDeleteFlagsPost($FIAS->post_id,$Start);			            			
				else $this->delivery->set_PriceDeleteFlagsPost($FIAS->post_id);
        }
        $this->Endtime = time();
        echo "OK";
        $this->unlockPost($fp, $this->postid);
    }

	//--------------------------------------------------------------------------------------
    function save_city()
    {
        //Получаем города
        $City = $this->get_city();
        print_r($City);
	}	

	



    ///////////////////////////----функции работы с API----/////////////////////////////////
    function get_token(){

		$data='grant_type=client_credentials&client_id='.$this->client_id.'&client_secret='.$this->secret;
		$url='https://xapi.ozon.ru/principal-auth-api/connect/token';
        $ch = curl_init($url);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/x-www-form-urlencoded"));		  		
        $data = curl_exec($ch);
        curl_close($ch);
		$data=json_decode($data);
		echo "Новый токен: ";
		print_r($data->access_token);
		echo "<br>";
		$this->token = $data->access_token;
		if ($this->token=="") echo "Не могу получить токен.";
	}
	
	//Базовая функция вывода
    function get_data($command,$dt="")
    {				
		$p=3;
		while ($p!=0){
			$url="https://xapi.ozon.ru/principal-integration-api/".$command;
			//echo $url."<br>";
			$ch = curl_init($url);	
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("authorization: Bearer ".$this->token));		  		
			if ($dt!=""){
				curl_setopt($ch, CURLOPT_POST, 1);			
				curl_setopt($ch, CURLOPT_POSTFIELDS, $dt);		
			}
			$data = curl_exec($ch);
			curl_close($ch);
			//var_dump($data);
			$data2=$data;

			if (defined("JSON_C_VERSION") || version_compare(PHP_VERSION, '5.4.0', '<')) {
				$data=json_decode($data, false, 512);
			} else {
				$data=json_decode($data, false, 512, JSON_BIGINT_AS_STRING);
			}			
			
			//if (!isset($data->nextPageToken)){
			//	echo "<br>\r\n";
				//var_dump($data2);
				//echo "<br>\r\n";
				//var_dump($data);
				//echo "<br>\r\n";
				//print_r($data);
				//echo "<br>\r\n";				
			//}
			if(isset($data->httpStatusCode) || $data2==""){	
				echo "<br>\r\n";
				echo "----------------------------------------------";
				echo "<br>\r\n";
				echo $url;
				echo "<br>\r\n";
				if (strpos($data->message,"нет тарифа")!==false) { var_dump($data2); return "";}
				$this->get_token();
				echo "<br>\r\n";
				var_dump($data2);
				echo "<br>\r\n";
				var_dump($data);
				echo "<br>\r\n";
				print_r($data);
				echo "<br>\r\n";
				$p--;
			}
			else $p=0;
		}
		//var_dump($data);
		//print_r($data);
		//echo "!!!";
        return $data;
    }

	//-----------------------------------------------------------------
    //Получаем города
    function get_city()
    {
        return $this->get_data("v1/delivery/cities");
    }
	//-----------------------------------------------------------------
    //Получаем терминалы
    function get_terminals($nextPageToken="#", $cityid=0)
    {
		$dop="";
		if ($nextPageToken!="#") $dop="&pagination.token=".urlencode($nextPageToken);
		if ($cityid!=0){
			$cityopt = $this->delivery->get_TownOptByID($cityid);
			$dop.="&cityName=".urlencode($cityopt['name']);
		}
		
        return $this->get_data("v1/delivery/variants?pagination.size=100&payloadIncludes.includeWorkingHours=true&payloadIncludes.includePostalCode=true".$dop,"");
    }	
	//-----------------------------------------------------------------
    //Получаем стоимость доставки
    function get_price($target, $weight, $delivery = false)
    {
		
        return $this->get_data("v1/delivery/calculate?deliveryVariantId=" . $target . "&weight=" . intval($weight) . "&fromPlaceId=" . $this->from_id,"");
    }		
	//-----------------------------------------------------------------
    //Получаем сроки доставки в днях
    function get_days($target)
    {		
        return $this->get_data("v1/delivery/time?deliveryVariantId=" . $target . "&fromPlaceId=" . $this->from_id,"");
    }			
	//-----------------------------------------------------------------
	//Получаем места передачи отправления
	function get_from()
	{
		$ret=$this->get_data("v1/delivery/from_places","");
		return $ret->places[0]->id;
	}

}
?>