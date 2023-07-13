<?php
//TODO   отладить алгоритм получения расписания
class cdek_delivery extends delivery_company
{

    //Переменные подключения к api
    private $username = '1b3137cbe2bf593c174413dc39255b09';
    private $password = '';
    private $dpd;
    private $messagelog;
    private $detalization;
    private $postid;
    private $weights;
    private $delivery;
    private $priceslimit;
    private $volumes;
    private $name;
    private $APItools;
    private $lastAPI;
    private $logo;
    private $token;

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
        $this->messagelog = new messagelog($this->postid, "", $logflags[0], $logflags[1], $logflags[2], $this->detalization);
        $this->delivery = new Delivery($this->weights, $this->postid, $this->detalization);
        $this->name = $this->delivery->getPostbyId($this->postid);
      //  $this->compareBase = new compareBase();
        if ($graph) {
            echo "<div style=\"width:auto;border-bottom:solid 1px #ccc;margin:0 150px;margin-bottom:15px;display:grid;grid-template-columns:200px,1fr \"><img src=\"" . $this->logo . "\" style=\"display:block;height:30px;width:auto;margin-bottom:5px\"><div style=\"display:flex;align-items:flex-end;justify-content:flex-end;\"><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 1);\">Получить все</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 2);\">Получить терминалы</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 3);\">Получить стоимость</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 4);\">Проверить БД</div><div class=\"button\" onclick=\"sendAction(" . $this->postid . ", 5);\">Сравнить БД</div></div></div>";
            $this->buildStatistics($this->delivery->connection,$postid);
        }
    }



    ///////////////////////////////////////////////////////////////////////////////
    //////////////////////////////Переопределение наследумеых функций//////////////
    ///////////////////////////////////////////////////////////////////////////////


    function check_Bases()
    { //$this->delivery->checkPricesDate(5);
        //$this->delivery->checkScheduleDate(15);
        $this->delivery->checkPricesintegrity();
        $this->delivery->checkSheduleintegrity();
        //  $this->compareBase->checkBases($this->postid);
    }

    function compare_Bases()
    {

        $this->compareBase->checkBases($this->postid);
    }




    function save_terminalsdata($city_id = null)
    {

        /*
        //Получаем терминалы
        $affiliates = $this->get_affileates();

        // print_r($this->APItools->prepareResponseStructure($affiliates[0])['html']);

        //  APItools::displayResponseStructure("v1/affiliate", $affiliates[0]);

        //Очищаем флаги обновления данных
        $this->delivery->clear_UpdateFlags();

        //print_r($affiliates);
        foreach ($affiliates as $affiliate) {


            //Получаем  id  города из таблицы cityfias
            $city_id = $this->delivery->get_TownIdByFias($affiliate->guid);


            //обход результата
            foreach ($affiliate->terminals as $terminal) {
                $terminal_id = $terminal->code;


                if ($terminal_id == "") {
                    $this->messagelog->addLog(1, $this->postid, "Отсутвует код терминала",  "В полученном ответе: " . json_encode($terminal) . " отсутвуют данные о коде терминала ", 0);
                }


                $terminal_name = $terminal->name;
                $terminal_street = $terminal->address;
                $terminal_house = "";
                $terminal_x = explode(',', $terminal->map)[0];
                $terminal_y = explode(',', $terminal->map)[1];
                $schedule = $this->format_TerminalShedule($terminal, 1);


                $post = $this->delivery->save_TerminalData(
                    $city_id,
                    $terminal_id,
                    $terminal_name,
                    $terminal_street,
                    $terminal_house,
                    $terminal_x,
                    $terminal_y,
                    array('flholiday' => $this->checkHolidayWork($schedule), "flkurier" => 0)
                );


                $this->delivery->save_Schedule($post, $schedule);


                $post = $this->delivery->save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y, array('flholiday' => $this->checkHolidayWork($schedule), "flkurier" => 1));


                $this->delivery->save_Schedule($post, $schedule);
            }
        }

        $this->messagelog->addLog(1, $this->postid, "Информация от таблице Post",  "API " . mb_convert_encoding($this->name['name'], 'utf-8', 'windows-1251') . " содержит следующую информацию: " . $this->delivery->checkFlags(), 0);
        $this->delivery->set_DeleteFlags();*/
    }


    function save_deliveryprices($city_id=null)
    {
        /*
        $ctr = 0;

        $FIASes = $this->delivery->get_FIAStoUpdatePrices();

        foreach ($this->weights as $weight) {


            foreach ($FIASes as $FIAS) {


                if ($ctr < $this->priceslimit) {
                    $ctr++;
                    $pricedata = $this->get_price($FIAS->FIAS, $weight, false);

                    echo "<br>";
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";

                    if (!isset($pricedata->error)) {
                        $price = $pricedata->total->int;
                        $days = isset($pricedata->transit) ? $pricedata->transit->int : 0;
                        $this->delivery->save_pricedata($FIAS->post_id, 0, $price, $weight, 0.1, $days, array());
                    } else {
                        $this->delivery->printlog($pricedata->error . " Транспортная компания: Байкал-Сервис. Пункт назначения: " . mb_convert_encoding($FIAS->city,  'utf-8', 'windows-1251') . ". Вес: " . $weight, 1);
                    }
                    $ctr++;
                    $pricedata = $this->get_price($FIAS->FIAS, $weight, true);
                    print_r($pricedata);
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";
                    echo "<br>";
                    if (!isset($pricedata->error)) {
                        $price = $pricedata->total->int;
                        $days = isset($pricedata->transit) ? $pricedata->transit->int : 0;
                        $this->delivery->save_pricedata($FIAS->post_id, 1, $price, $weight, 0.1, $days, array());
                    } else {
                        $this->delivery->printlog($pricedata->error . " Транспортная компания: Байкал-Сервис. Пункт назначения: " . mb_convert_encoding($FIAS->city,  'utf-8', 'windows-1251') . ". Вес: " . $weight, 1);
                    }
                }
            }
        }
        $this->delivery->set_DeleteFlags();*/
    }



    ///////////////////////////----функции работы с API----/////////////////////////////////

    //Базовая функция вывода
    function get_token()
    {

        try {



            $array = array(
                'grant_type'    => 'client_credentials',
                'client_id' => 'epT5FMOa7IwjjlwTc1gUjO1GZDH1M1rE',
                'client_secret' => 'cYxOu9iAMZYQ1suEqfEvsHld4YQzjY0X'
            );

            $ch = curl_init('https://api.edu.cdek.ru/v2/oauth/token?parameters');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $array);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $data = curl_exec($ch);
            //echo curl_error($ch);
            curl_close($ch);
        } catch (Exception $e) {
            echo 'УПС! Ошибочка: ',  $e->getMessage(), "\n";
        }


        return json_decode($data)->access_token;
    }





    //Получаем пункты выдачи
    function get_affileates()
    {
        $this->lastAPI = "v1/affiliate";
     //   $res = json_decode($this->get_data("v1/affiliate", $this->username, $this->password));
        $total = 0;
       // foreach ($res as $terminals) {
           // $total = $total + count($terminals->terminals);
    //    }
     //   $this->delivery->printlog("Получено " . $total . " терминалов. В " . count($res) . " городах", array("margin-left" => "50px"));
        //return  $res;
    }

    //Получаем стоимость доставки до каждого пункта груза обемом 1 куб и весом 5,10,25,50,100,150,250,300,400,500,600,700,800,900,1000,1500,2000 кг


    function get_price($guid, $weight, $delivery = false)
    {
        $this->lastAPI = "v1/calculator?from[guid]=ccc34487-8fd4-4e71-b032-f4e6c82fb354&to[guid]=" . $guid . "&cargo[weight]=" . $weight . "&cargo[volume]=0.1&" . ($delivery ? "to[delivery]=1" : "");

       // return  json_decode($this->get_data("v1/calculator?from[guid]=ccc34487-8fd4-4e71-b032-f4e6c82fb354&to[guid]=" . $guid . "&cargo[weight]=" . $weight . "&cargo[volume]=0.1&" . ($delivery ? "to[delivery]=1" : ""), $this->username, $this->password));
    }



    function get_citycode($url = null)
    {
        $city = '';
        if (!$url) $city = 112;
        else $city = $url['city'];

        $fias = $this->delivery->get_FiasbyTownId($city);
        $this->token = $this->get_token();

        $headers = array(
            'Authorization: Bearer ' . $this->token
        );


        $ch = curl_init('https://api.edu.cdek.ru/v2/location/cities?fias_guid=' . $fias);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);








        $data = curl_exec($ch);

        curl_close($ch);
        // print_r($data);

        return json_decode($data)[0]->code;
    }


    function get_currentprice($url)
    {

        $citycodeto = $this->get_citycode($url);
        $citycodefrom= $this->get_citycode();

        $headers = array(
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
            );


        $array = array(
            'from_location'    =>  array("code" => $citycodeto),
            'to_location' => array("code" => $citycodefrom),
            'tariff_code' => 136,
            'packages' => array("weight" => $url['weight']),
            
        );

       //136


        $ch = curl_init('https://api.edu.cdek.ru/v2/calculator/tariff');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $data = json_decode(curl_exec($ch));

       


 
        $ret['price'] = $data->$url['city'];
        $ret['days'] = $data->period_max;

        $array = array(
            'from_location'    =>  array("code" => $citycodeto),
            'to_location' => array("code" => $citycodefrom),
            'tariff_code' => 137,
            'packages' => array("weight" => $url['weight']),
            
        );


        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));

        $data = json_decode(curl_exec($ch));
        

        $ret['dprice'] = $data->total_sum;
        $ret['ddays'] = $data->period_max;

        $ret['weight'] = $url['weight'];
        $ret['volume'] =  $url['volume'];;
        $ret['places'] = $url['city']."hjghj:";

        curl_close($ch);
  
        return $ret;
    }


    function format_TerminalShedule($terminal, $display)
    {
        $localschedule = json_decode(json_encode($terminal), true);
        //print_r($localschedule);

        $previousSchedule = '';

        $result = array();



        for ($t = 1; $t < 7; $t++) {

            $index = 'schedule_' . $t;

            if ($previousSchedule != $localschedule[$index]) {
                $result[]['days'] = pow(2, $t - 1);
                $result[count($result) - 1]['time'] = $localschedule[$index];
                $previousSchedule = $localschedule[$index];
            } else {
                $result[count($result) - 1]['days'] = $result[count($result) - 1]['days'] + pow(2, $t - 1);
                $result[count($result) - 1]['time'] =     $localschedule[$index];
            }
        }



        // print_r($result);


        return $result;
    }
    public function checkHolidayWork($schedule)
    {
        $flholiday = 0;
        $DayBytes = array(127, 96, 64, 32);
        /// print_r($schedule);
        foreach ($schedule as $scheduleItem) {
            //  print_r($scheduleItem);
            if (in_array($scheduleItem['days'], $DayBytes)) {
                $flholiday = 1;
            }
        }
        return $flholiday;
    }
}
