<?php
include "dpd_service.class.php";
include "delivery.php";
include "messagelog.class.php";


$dpd = new DPD_service();
$messagelog = new messagelog(1,"DPD",false,false,true,0);

$detalization = 0;
$postid = 1;


$weights = array(5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000);
$delivery = new Delivery($weights, 1, $detalization);
//$citylist=$dpd->getCityList();

//Настройка отображения ошибок
ini_set('display_errors', 0);
error_reporting(E_ALL);

function save_terminalsdata()
{
	global $delivery, $dpd,$messagelog,$postid;
	//Получаем терминалы
	$affiliates = $dpd->getTerminalList();
	//print_r($affiliates);
	//Очищаем флаги обновления данных
	$delivery->clear_UpdateFlags();

	foreach ($affiliates->terminal as $terminal) {

		//Получаем  id  города из таблицы cityfias
		$city_id = $delivery->get_TownIdByField("dpdcode", $terminal->address->cityCode);
       if ($city_id==''){	$messagelog->addLog(2, $postid, "Проверка наличия в таблице городов", "Города ".$terminal->address->cityName."(".$terminal->address->countryCode.") из региона ".$terminal->address->regionName."(".$terminal->address->regionCode.") c кодом dpdcity=\"".$terminal->address->cityCode."\" не обнаружено в таблице city", 0);
	}

		//обход результата

		$terminal_id = $terminal->terminalCode;
		$terminal_name = $terminal->terminalName;
		$terminal_street = $terminal->address->cityName . ", " . $terminal->address->streetAbbr . " " . $terminal->address->street . ", " . $terminal->address->houseNo;
		$terminal_house = $terminal->address->houseNo;
		$terminal_x = '';
		$terminal_y = '';

		/////////////////////////////////////////////Написать функцию поиска id улицы

		$post = $delivery->save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y);
		$delivery->save_Schedule($post, format_TerminalShedule($terminal->schedule[3], 1));

	}
}
function format_TerminalShedule($schedule)
{
	$result = [];

	if ($schedule) {
		foreach ($schedule->timetable as $timetableitem) {
			if ($timetableitem->weekDays != "") {
				$mask = count_bitmask(trim($timetableitem->weekDays));
				$result[]['days'] = $mask;
				$result[count($result) - 1]['time'] = $timetableitem->workTime;
			}
		}
	}
	return count($result) > 0 ? $result : null;
}

function count_bitmask($workdays)
{
	$days = array("пн", 'вт', 'ср', 'чт', 'пт', 'сб', 'вс');
	$workdays = explode(",", $workdays);
	$result = 0;
	foreach ($workdays as $workday) {
		$workday = mb_strtolower($workday);
		$MaskPosition = array_search($workday, $days);
		$result = $result + pow(2, $MaskPosition);
	}

	return $result;
}



function save_deliveryprices($limit)
{
	global $delivery, $dpd, $postid, $messagelog;
	$ctr = 0;


	$Cities = $delivery->get_CitytoUpdatePrices();
	$delivery->printlog("Загрузка стоимости доставки до пункта выдачи", 0);
	foreach ($delivery->weights as $weight) {
		$delivery->printlog("Вес: " . $weight, 1);

		foreach ($Cities as $City) {
			$ctr++;

			if ($ctr < $limit) {
				$arData = array();
				$arData['delivery']['cityId'] = "" . $City->dpdid;
				$arData['weight'] = $weight;
				$arData['serviceCode'] = "ECN";
				$pricedata = $dpd->getServiceCost($arData);
				if (isset($pricedata->error)) {
					$messagelog->addLog(2, $postid, "Получение данных стоимости", $pricedata->error, 0);
				
				}

				$price = $pricedata->return->cost;
				$days = $pricedata->return->days;
				$delivery->save_pricedata($City->post_id, 0, $price, $weight, 0.1, $days);
			}
		}
	}


	//$delivery->check_TerminalPricesChanges();
}

save_terminalsdata();
save_deliveryprices(50);
$messagelog->totalLog();
