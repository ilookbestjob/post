<?php

include "dpd_service.class.php"; //класс от разаработчиков DPD

class dpd_delivery extends delivery_company
{
    private $dpd;
    private $messagelog;
    private $detalization;
    private $postid;
    private $weights;
    private $delivery;
    private $priceslimit;
    private $volumes;
    private $name;
    public $logo;
    private $compareBase;
    public function __construct(array $weights, array $volumes, $postid, $priceslimit, $logdetalization, array $logflags, $logo = null, $graph = true)
    {


        $this->postid = $postid;
        $this->detalization = $logdetalization;
        $this->priceslimit = $priceslimit;
        $this->weights = $weights;
        $this->volumes = $volumes;
        $this->logo = $logo;
        $this->dpd = new DPD_service();
        $this->delivery = new Delivery($this->weights, $this->postid, $this->detalization);        
        
        $this->name = $this->delivery->getPostbyId($this->postid);
        //  $this->compareBase = new compareBase();



        if ($graph) {
            $this->buildbraph($this->delivery->connection, $this->postid, $logo);
        }
        else{
            $this->messagelog = new messagelog($this->postid, "", $logflags[0], $logflags[1], $logflags[2], $this->detalization);
            $this->messagelog->statistics="Запуск запрещен. Работает Другой скрипт.";
            $this->messagelog->status=3;
        }

    }


    ///////////////////////////////////////////////////////////////////////////////
    //////////////////////////////Переопределение наследумеых функций//////////////
    ///////////////////////////////////////////////////////////////////////////////

    function check_Bases()
    {
        // $this->delivery->checkPricesDate(5);
        // $this->delivery->checkScheduleDate(15);
        $this->delivery->checkPricesintegrity();
        $this->delivery->checkSheduleintegrity();
        //  $this->compareBase->checkBases($this->postid);
    }
    function compare_Bases()
    {

        $this->compareBase->checkBases($this->postid);
    }


    //-------------------------------------------------------

    function getCityList()
    {


        //        cityId  Идентификатор города отправления
        //cityId  Идентификатор города отправления


        $temp = $this->dpd->getCityList(true);

        $temp2 = $this->dpd->getTerminalList();
        //  print_r($affiliates);
        $aff = array_merge($temp2->terminal, ["==================================="], $temp->return->parcelShop);
        //  print_r($aff);
        return array_merge($temp2->terminal, $temp->return->parcelShop);
    }


    function save_terminalsdata($cityid = null)
    {
      
        
        $this->messagelog->statistics="Запуск запрещен. Работает Другой скрипт.";
        
        $this->messagelog->status=4;
        $fp = $this->lockPost($this->postid,2);
        $this->messagelog->statistics="";
       
        $this->messagelog->status=5;

        //Получаем терминалы
        //$affiliates = $this->dpd->getTerminalList();



        $affiliates = $this->getCityList();

        //print_r($this->checkDPDcodes($affiliates));


        $this->delivery->printlog("Получено " . count($affiliates) . " терминалов.", array("margin-left" => "50px"));

        $postcount = 0;
        //APItools::displayResponseStructure("getTerminalList()", $affiliates);
        //Очищаем флаги обновления данных
        $this->delivery->clear_UpdateFlags($cityid);
        $arrcityadr = array();
        foreach ($affiliates as $terminal) {
            // echo "erer</br>";
            //Получаем  id  города из таблицы cityfias
            $city_id = $this->delivery->get_TownIdByField("dpdcode", $terminal->address->cityCode);
            if ($city_id == '') {
                $this->messagelog->addLog(2, $this->postid, "Проверка наличия в таблице городов", "Города " . $terminal->address->cityName . "(" . $terminal->address->countryCode . ") из региона " . $terminal->address->regionName . "(" . $terminal->address->regionCode . ") c кодом dpdcity=\"" . $terminal->address->cityCode . "\" не обнаружено в таблице city", 0);
            }


            //обход результата
            if (($cityid) && ($cityid != $city_id)) continue;

            $terminaltypes = ["ПВП" => "Пункт выдачи посылок", "П" => "Постомат"];

            $terminal_id = $terminal->terminalCode ? $terminal->terminalCode : $terminal->code;
            $terminal_name = $terminal->terminalName ? $terminal->terminalName : ($terminaltypes[$terminal->parcelShopType] . ", " . (isset($terminal->address->streetAbbr) ? $terminal->address->streetAbbr : "") . " " . $terminal->address->street . ", " . (isset($terminal->address->houseNo) ? $terminal->address->houseNo : ""));;

            $terminal_street = $terminal->address->cityName . ", " . (isset($terminal->address->streetAbbr) ? $terminal->address->streetAbbr : "") . " " . $terminal->address->street;

            //убрано задача от 09.11.2022
            //. (isset($terminal->address->houseNo) ? $terminal->address->houseNo : "");
            $terminal_house = isset($terminal->address->houseNo) ? $terminal->address->houseNo : "";

            $terminal_x = $terminal->geoCoordinates->latitude;
            $terminal_y = $terminal->geoCoordinates->longitude;

            $terminal_comm = $terminal->address->descript;


            $postcount++;
          
            $post = $this->delivery->save_TerminalData(
                $city_id,
                $terminal_id,
                $terminal_name,
                $terminal_street,
                $terminal_house,
                $terminal_x,
                $terminal_y,
                array(
                    "flkurier" => 0,
                    'comm' => mb_convert_encoding($terminal_comm, 'windows-1251', 'utf-8'),
                )
            );
            try {
                if (!isset($terminal->terminalName)) {
                    $this->delivery->save_Schedule($post, $this->format_TerminalShedule($terminal->schedule));
                }
            } catch (Exception $e) {
                echo "Ошибка формирования расписания ";
            }

            if (!isset($arrcityadr[$city_id])) {
                $arrcityadr[$city_id] = 1;
                echo "Добавляем адресную доставку в город!<br>";
                $post = $this->delivery->save_TerminalData($city_id, '', 'Адресная доставка в г.' . $terminal->address->cityName, 'Адресная доставка в г.' . $terminal->address->cityName, $terminal_house, 0, 0, array("flkurier" => 1));
            }
        }
        echo "OK";
        if ($postcount > 100) $this->delivery->set_DeleteFlags($cityid);

        $this->unlockPost($fp, $this->postid);
    }
    //-----------------------------------------------------------------------------
    function save_deliveryprices($city_id = null)
    {
        echo "получаем цена";
        $ctr2 = 0;
        $weighttime_start = 0;
        $time_start = 0;
        $currentdalay = 3;
       // echo $this->action;
        $this->messagelog->statistics="Запуск запрещен. Работает Другой скрипт.";
        $this->messagelog->status=4;
        $fp = $this->lockPost($this->postid,3);
        $this->messagelog->statistics="";
        $this->messagelog->status=5;
        $Start = $this->delivery->get_current_date();
        $Cities = $this->delivery->get_CitytoUpdatePrices($city_id);
        $limit = 3;
        $currentlimit = 0;
        //echo "получили города";

        if (count($Cities) == 0) {

            $this->messagelog->addLog(1, $this->postid, "Ошибка получения списка терминалов",  "Попытка получить  список терминалов для обновления цен не вернула ни одного результата", 0);
        }
        //echo "для $city_id их " . count($Cities) . "<br><br>";

       // print_r($this->weights);


        $this->messagelog->addLog(0, $this->postid, "информация об обновлении цен", "Получено следующее количество строк для обновления цен доставки:" . count($Cities), 0);

        $ctr = 0;

        $affiliates = $this->getCityList();

        //print_r($affiliates);
        //echo "<br><br>+++++++++++++++++++++++++++++++++++++++++++++<br>";
        //print_r($Cities);

        foreach ($Cities as $City) {
            $this->messagelog->status=5;
           // echo $City->dpdid . "|| <br><br><br>";
            $time_start = microtime(true);
            //   echo "Начало получения данных терминала $City->name";
            $ctr++;
            if ($ctr > $this->priceslimit)  {

                $this->messagelog->addLog(0, $this->postid, "Превышен лимит", "Превышен лимит$this->priceslimit", 0);
                break;
                
            }
            if (!$this->findDPDcode($affiliates, $City->dpdid)) {

                $this->messagelog->addLog(0, $this->postid, "Не верный код города DPD", "Не найден код города dpd $City->сity  в списе доступнух городов $City->dpdid  $City->dpdcode", 0);
                continue;
            }

            $ok = 0;
            foreach ($this->weights as $weight) {
                $ctr2++;
                $weighttime_start = microtime(true);
                $this->messagelog->status=2;
                $this->delivery->checkstopAll();
               // $this->messagelog->status=5;
                $arData = array();
                $arData['delivery']['cityId'] = "" . $City->dpdid;
                $arData['weight'] = $weight;
                if ($City->flkurier > 0) $arData['selfDelivery'] = false;
                $arData['serviceCode'] = "ECN";

                $this->messagelog->addLog(0, $this->postid, "Итерция", "Получаем данные города ".mb_convert_encoding($City->city, 'UTF-8', "Windows-1251")."  вес $weight кг. Дата актуальности: ".$City->date, 0);

                $this->messagelog->statistics="Получено $ctr2 записей для $ctr городов. Последний город: ".mb_convert_encoding($City->city, 'UTF-8', "Windows-1251"). " вес $weight кг";

                try {
                    $pricedata = $this->dpd->getServiceCost($arData);
                } catch (Exception $e) {
                   // echo "Ошибка добавления в БД 2";

                    $this->messagelog->addLog(2, "Ошибка добавления данных терминала ", $this->postid, "ошибка добавления данных терминала $City->name post_id=$City->post_id flkurier=$City->flkurier pric=$price weight=$weight  ctr=$ctr ctr2=$ctr2", 0);
                }
                
            
            //    $this->messagelog->addLog(0, $this->postid, "Итерция", "Итерация  ctr=$ctr ctr2=$ctr2", 0);
           // print_r($City);


            $this->messagelog->addLog(0, $this->postid, "Итерция", "Получили данные города ".mb_convert_encoding($City->city, 'UTF-8', "Windows-1251")."  вес $weight кг цена ".$pricedata->return->cost." руб. ", 0);

                if ($ctr2 % 500 == 0) {
                    sleep(3);
                   // echo "<br><br>Пауза 10 сукнд...<br><br>";
                   // $this->messagelog->addLog(2, "Пауза по достижению лимита обращений", $this->postid, "Ожидание 10 секунд. ctr=$ctr ctr2=$ctr2", 0);
                }



                if (isset($pricedata->error)) {
                    $this->messagelog->addLog(2, $this->postid, "Получение данных стоимости. ctr=$ctr ctr2=$ctr2", $pricedata->error, 0);

                    $attempts = 5;
                    //echo "<br><br>Ошибка получения данных о цене.<br>";
                    //  if ($currentdalay + 10 < 600) $currentdalay = $currentdalay + 10;
                    //$this->messagelog->addLog(2, "Ошибка получения данных терминала ", $this->postid, "ошибка получения данных терминала $City->$City post_id=$City->post_id flkurier=$City->flkurier weight=$weight. ctr=$ctr ctr2=$ctr2 total=" . count($Cities), 0);

                    // sleep($currentdalay);
                    //  echo "<br><br>Пауза $currentdalay сукнд...<br><br>";


                    $currentlimit++;

                    sleep($currentdalay);
                    $this->messagelog->addLog(2, "Ошибка получения данных", $this->postid, "Ожидание  $currentdalay секунд. ctr=$ctr ctr2=$ctr2. Лимит $limit", 0);

                    if ($currentlimit >= $limit) {

                        $this->messagelog->addLog(2, "Достигнут предел ошибок ", $this->postid, "Достигнут предел ошибок по одному пункту. ctr=$ctr ctr2=$ctr2. Лимит $limit", 0);
                        $ctr2 = 0;
                        $currentlimit = 0;
                        break;
                    }





                    /*

                    for ($ctr = 1; $ctr <= $attempts; $ctr++) {
                        $pricedata = $this->dpd->getServiceCost($arData);
                        echo "Попытка получения № $ctr:<br>";
                        if (!isset($pricedata->error)) {
                            $ctr = $attempts + 2;
                            echo " Успех!<br>";
                        } else {
                            echo " Неудачно!<br>";
                            sleep(30);
                        }
                    }
*/
                    //echo "<br><br><br>";
                   // print_r($pricedata->error);
                   // echo "<br><br><br>";
                } else {
                    $currentdalay = 3;
                    $price = $pricedata->return->cost;
                    $days = $pricedata->return->days;
                    //echo  "Получен ответ для терминала " . $City->post_id . " города " . mb_convert_encoding($City->city, 'utf-8', 'windows-1251') . ":цена " . $price . " вес " . $weight . " объем 0.1 срок " . $days . "   время работы " . (microtime(true) - $weighttime_start) . " сек.</BR></BR>";

                    try {
                        $this->delivery->save_pricedata($City->post_id, $City->flkurier, $price, $weight, 0.1, $days, array("postservice_id" => 2));
                    } catch (Exception $e) {
                       // echo "Ошибка добавления в БД";

                        $this->messagelog->addLog(2, "Ошибка добавления данных терминала ", $this->postid, "ошибка добавления данных терминала $City->name post_id=$City->post_id flkurier=$City->flkurier pric=$price weight=$weight  ctr=$ctr ctr2=$ctr2", 0);
                    }
                    $ok++;
                }
               // echo "Время окончания получения цены за вес $weight";


                //print_r($this->delivery->get_current_date());
            }
            if ($ok > 0) $this->delivery->set_PriceDeleteFlagsPost($City->post_id, $Start);
            else $this->delivery->set_PriceDeleteFlagsPost($City->post_id);

            //echo "Окончание получения данных терминала $City->name. Время работы " . (microtime(true) - $time_start) . " сек.</BR></BR>";

            $this->messagelog->addLog(2, "получение данных завершено", $this->postid, "Окончание получения данных терминала $City->name. Время работы " . (microtime(true) - $time_start) . " сек.  ctr=$ctr ctr2=$ctr2", 0);
        }
        $this->Endtime = time();
      //  echo "OK";

        $this->messagelog->addLog(2, $this->postid, "Работа завершена", "Работа завершена получено $ctr2 цен в $ctr пунктах из " . count($Cities) . "ctr=$ctr ctr2=$ctr2", 0);
        $this->unlockPost($fp, $this->postid);
        $this->messagelog->status=3;
    }

    //-----------------------------------------------------------------------------

    function get_currentprice($url)
    {




        $City = $this->delivery->get_TownArrIdByField("row_id", $url['city']);
        $arData = array();
        $arData['delivery']['cityId'] = "" . $City['dpdcityId'];

        $arData['weight'] = $url['weight'];
        $arData['serviceCode'] = "ECN";

        $pricedata = $this->dpd->getServiceCost($arData);


        $ret['days'] = $pricedata->return->days;
        $ret['price'] = $pricedata->return->cost;

        $arData['selfDelivery'] = false;
        $pricedata = $this->dpd->getServiceCost($arData);
        $ret['ddays'] = $pricedata->return->days;
        $ret['dprice'] = $pricedata->return->cost;

        $ret['weight'] = $url['weight'];
        $ret['volume'] =  $url['volume'];;
        $ret['places'] = $url['places'];;

        return $ret;
    }



    ///////////////////////////////////////////////////////////////////////////////
    //////////////////////////////Вспомогательные функции класса dpd_delivery//////
    ///////////////////////////////////////////////////////////////////////////////




    function format_TerminalShedule($schedule)
    {


        echo "<br>>Schedule Format<br>";
        print_r($schedule);
        echo "<br>>======================<br>";


        $result = [];
        $localschedule = json_decode(json_encode($schedule), true);

        print_r($localschedule);


        echo "<br>>======================<br>";

        try {
            if (is_array($schedule)) {
                foreach ($localschedule as $sch) {
                 
                    print_r($sch);

                    if ($sch['operation'] == "SelfPickup") {

                        if (isset($sch['timetable']['weekDays'])) {
                            $mask = $this->count_bitmask($sch['timetable']['weekDays']);
                            $result[]['days'] = $mask;
                            $result[count($result) - 1]['time'] = $sch['timetable']['workTime'];
                        } else {

                            foreach ($sch['timetable'] as $timetableitem) {

                                if ($timetableitem['weekDays'] != "") {
                                    $mask = $this->count_bitmask(trim($timetableitem['weekDays']));
                                    $result[]['days'] = $mask;
                                    $result[count($result) - 1]['time'] = $timetableitem['workTime'];
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {

            echo "Ошибка формирования  расписания";
        }

        echo "<br><br><br>///=======================<br>";
        print_r($result);
        echo "<br>=======================///<br>";
        return $result;
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

    function checkDPDcodes($affiliates)
    {


        $str = "";
        $ctr = 0;
        $dpdcodes = [];
        foreach ($affiliates as $terminal) {


            if (!in_array($terminal->address->cityId, $dpdcodes)) {
                $ctr++;
                $dpdcodes[] = $terminal->address->cityId;
                $str = $str . ($str == "" ? "" : ",") . $terminal->address->cityId;


                $sql = "set names utf8";

                // mysqli_query($this->delivery->connection, $sql);

                $sql = "select *  from nordcom.city where dpdcityId =" . $terminal->address->cityId;
                $sqlresult = mysqli_query($this->delivery->connection, $sql);
                $res = mysqli_fetch_array($sqlresult);


                echo    $sql = "select *  from nordcom.city where city =" . $terminal->address->cityName;
                echo "</br>";
                $sqlresult = mysqli_query($this->delivery->connection, $sql);

                $towns = "";
                while ($res2 = mysqli_fetch_array($sqlresult)) {
                    $towns .= " " . $res2['city'] . "  " . $res2['dpdcityId'] . "</br>";
                }







                echo "</br>" . $ctr . "  " . $terminal->address->cityId . "  " . $terminal->address->cityName . " ";



                echo ($res ? "DPD код найден в базе  " . $res["dpdcityId"] . " | " . mb_convert_encoding($res["city"], "utf-8") : ' <span style="color:red;">DPD код не найден найден в базе</span>');


                echo "</br></br>" . $towns;
            }
        }



        // echo "<br><br><br>";
        $sql = "select count(*) as qt from nordcom.city where dpdcityId in ($str)";
        $sqlresult = mysqli_query($this->delivery->connection, $sql);
        //print_r(mysqli_error($this->delivery->connection));

        //echo "count" . count($affiliates);
        $res = mysqli_fetch_array($sqlresult);


        return $res["qt"];
    }



    function findDPDcode($affiliates, $code)
    {

        ///51000001000
        ////
        $str = "";
        $ctr = 0;
        $dpdcodes = [];
        foreach ($affiliates as $terminal) {

           // echo $terminal->address->cityId . ($terminal->address->cityId == $code ? "=" : "<>") . $code . "<br><br>";
            if ($terminal->address->cityId == $code) return true;
        }

        return false;
    }


  
}


/*

 [schedule] => 
 Array ( 
    [0] => stdClass Object 
    ( 
        [operation] => Payment 
        [timetable] => stdClass Object ( 
            [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
            [workTime] => 08:00 - 23:59 ) ) 
    [1] => stdClass Object 
    ( 
        [operation] => PaymentByBankCard 
        [timetable] => stdClass Object ( 
            [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
            [workTime] => 08:00 - 23:59 ) ) 
    [2] => stdClass Object
     ( 
        [operation] => SelfDelivery 
        [timetable] => stdClass Object ( 
            [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
            [workTime] => 08:00 - 23:59 ) ) )

*/



/*
 [schedule] => 
 Array 
 ( [0] => stdClass Object ( 
    [operation] => Payment 
    [timetable] => stdClass Object ( 
        [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
        [workTime] => 08:00 - 23:59 ) ) 
    [1] => stdClass Object ( 
        [operation] => PaymentByBankCard 
        [timetable] => stdClass Object ( 
            [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
            [workTime] => 08:00 - 23:59 ) ) 
    [2] => stdClass Object ( 
        [operation] => SelfDelivery 
        [timetable] => stdClass Object ( 
            [weekDays] => Пн,Вт,Ср,Чт,Пт,Сб,Вс 
            [workTime] => 08:00 - 23:59 ) ) )            
*/