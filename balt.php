<html>
<header>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
</header>

<body>
    <?php

    //Подключения класса работы с БД
    require 'delivery.php';

    //Настройка отображения ошибок
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    $displaylevel = 0; //уровень детализации информации на экране при отладке    -1 - ничего не выводить


    //Переменные для авторизации на сервере API

    $host_api = "https://api.baikalsr.ru/";
    $username = '1b3137cbe2bf593c174413dc39255b09';
    $password = '';

    $headers = array(
        'Content-type: application/json',
        'Authorization: basic ' . $username,
    );

    //Переменные подключения к БД
    $server = 'localhost';
    $base = 'nordcom';
    $user = 'root';
    $bdpassword = 'pr04ptz3';


    //Настройки получения данных
    $flGoroda = 0; //закачавать города 1-да
    $flTerminal = 0; //закачавать список терминалов
    $flcalc = 1; //закачавать стоимость


    //Создание объекта для загрузки в локальную БД
    $weights = array(5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000);
    $delivery = new delivery($weights, 24, $displaylevel);



    ///////////////////////////----функции работы с API----/////////////////////////////////

    //Базовая функция вывода
    function get_data($command, $usr, $pwd)
    {

        $url = "https://api.baikalsr.ru/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $command);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$usr:$pwd");
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }


    //Получаем пункты выдачи
    function get_affileates()
    {
        global $username;
        global $password;
        return  json_decode(get_data("v1/affiliate", $username, $password));
    }

    //Получаем стоимость доставки до каждого пункта груза обемом 1 куб и весом 5,10,25,50,100,150,250,300,400,500,600,700,800,900,1000,1500,2000 кг
    function get_prices()
    {
        global $username;
        global $password;
        $limit = 10;
        $currentpos = 0;
        $weights = [5, 10];
        //$weights=[5,10,25,50,100,150,250,300,400,500,600,700,800,900,1000,1500,2000];
        $affiliates = get_affileates();
        $result = [];
        foreach ($affiliates as $affiliate) {
            foreach ($weights as $weight) {
                $currentpos++;

                if ($currentpos <= $limit) {
                    $result[] = json_decode(get_data("v1/calculator?from[guid]=ccc34487-8fd4-4e71-b032-f4e6c82fb354&to[guid]=" . $affiliate->guid . "&cargo[weight]=" . $weight . "&cargo[volume]=1", $username, $password));
                }
            }
        }
        return  $result;
    }

    function get_price($guid, $weight, $delivery = false)
    {
        global $username;
        global $password;

        return  json_decode(get_data("v1/calculator?from[guid]=ccc34487-8fd4-4e71-b032-f4e6c82fb354&to[guid]=" . $guid . "&cargo[weight]=" . $weight . "&cargo[volume]=0.1&" . ($delivery ? "to[delivery]=1" : ""), $username, $password));
    }


    ///////////////////////////----функции работы с БД----/////////////////////////////////
    //подключение к  БД
    function connectDB()
    {
        global $server, $base, $user, $bdpassword;
        $connection = mysqli_connect($server, $user, $bdpassword, $base);
        if (!$connection) {
            die("Ошибка подключения к базе");
        }
        return $connection;
    }

    //Получаем город по ФИАСу
    function get_TownIdByFias($fias)
    {
        $connection = connectDB();
        $sql = 'Select * from cityfias where fias="' . $fias . '"';
        $basedata = mysqli_query($connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        return $baserow['city_id'];
    }


    //Сохраняем данные в базу 
    function save_terminalsdata()
    {
        global $delivery;
        //Получаем терминалы
        $affiliates = get_affileates();
        //Очищаем флаги обновления данных
        $delivery->clear_UpdateFlags();

        //print_r($affiliates);
        foreach ($affiliates as $affiliate) {


            //Получаем  id  города из таблицы cityfias
            $city_id = $delivery->get_TownIdByFias($affiliate->guid);


            //обход результата
            foreach ($affiliate->terminals as $terminal) {
                $terminal_id = $terminal->id;
                $terminal_name = $terminal->name;
                $terminal_street = $terminal->address;
                $terminal_house = "";
                $terminal_x = explode(',', $terminal->map)[0];
                $terminal_y = explode(',', $terminal->map)[1];



                $post = $delivery->save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y);

                $delivery->save_Schedule($post, format_TerminalShedule($terminal, 1));
            }
        }
        $delivery->printlog("Загрузка пунктов выдачи", 0);
        $delivery->check_TerminalChanges();
        $delivery->printlog("Загрузка режима работы пунктов выдачи", 0);
        $delivery->check_TerminalSheduleChanges();
    }



    function save_deliveryprices()
    {
        global $delivery;
        $ctr = 0;
        $limit = 50;
        $FIASes = $delivery->get_FIAStoUpdatePrices();
        $delivery->printlog("Загрузка стоимости доставки до пункта выдачи", 0);
        foreach ($delivery->weights as $weight) {
            $delivery->printlog("Вес: " . $weight, 1);
            foreach ($FIASes as $FIAS) {
                $ctr++;
                $delivery->printlog("FIAS: " . $FIAS->FIAS, 1);
                if ($ctr < $limit) {
                    $pricedata = get_price($FIAS->FIAS, $weight, false);
                    if (!isset($pricedata->error)) {
                        $price = $pricedata->price->int;
                        $days = $pricedata->transit->int;
                        $delivery->save_pricedata($FIAS->post_id, 0, $price, $weight, 0.1, $days);
                    } else {
                        $delivery->printlog($pricedata->error . " Транспортная компания: Байкал-Сервис. Пункт назначения: " . mb_convert_encoding($FIAS->city,  'utf-8', 'windows-1251') . ". Вес: " . $weight, 1);
                        // $delivery->PricegetErrors++;
                    }
                }
            }
        }

        $delivery->check_TerminalPricesChanges();
    }
    function format_TerminalShedule($terminal, $display)
    {
        global $displaylevel;
        if (strlen($terminal->schedule) > 20) {
            if ($display == $displaylevel) {
                echo "<strong>Выгрузка режимов работы терминалов Байкал-сервис</strong>";
                echo " <strong>" . $terminal->name . "</strong></br></br>";
            }
            $start = strpos($terminal->schedule, "Понедельник") . "</br>";
            $end = strpos($terminal->schedule, "Пятница") . "</br>";
            $localShedule = [];
            $tempstr = $terminal->schedule;
            if ((strlen($tempstr < 200)) && (strpos(strtolower($tempstr), "круглосуточно") != 0)) {

                $localShedule['MonStart'] = "круглосуточно";
                $localShedule['MonEnd'] = "круглосуточно";

                $localShedule['TueStart'] = "круглосуточно";
                $localShedule['TueEnd'] = "круглосуточно";


                $localShedule['WedStart'] = "круглосуточно";
                $localShedule['WedEnd'] = "круглосуточно";


                $localShedule['ThuStart'] = "круглосуточно";
                $localShedule['ThuEnd'] = "круглосуточно";


                $localShedule['FriStart'] = "круглосуточно";
                $localShedule['FriEnd'] = "круглосуточно";

                $localShedule['SatStart'] = "круглосуточно";
                $localShedule['SatEnd'] = "круглосуточно";


                $localShedule['SunStart'] = "круглосуточно";
                $localShedule['SunEnd'] = "круглосуточно";

                $result[]['days'] = 127;
                $result[count($result) - 1]['time'] = 'круглосуточно';


                if ($display == $displaylevel) {
                    echo "Понедельник: " . ($localShedule['MonStart'] != "круглосуточно" ? $localShedule['MonStart'] . "-" . $localShedule['MonEnd'] : "круглосуточно") . "</br>";
                    echo "Вторник: " . ($localShedule['TueStart'] != "круглосуточно" ? $localShedule['TueStart'] . "-" . $localShedule['TueEnd'] : "круглосуточно") . "</br>";
                    echo "Среда: " . ($localShedule['WedStart'] != "круглосуточно" ? $localShedule['WedStart'] . "-" . $localShedule['WedEnd'] : "круглосуточно") . "</br>";
                    echo "Четверг: " . ($localShedule['ThuStart'] != "круглосуточно" ? $localShedule['ThuStart'] . "-" . $localShedule['ThuEnd'] : "круглосуточно") . "</br>";
                    echo "Пятница: " . ($localShedule['FriStart'] != "круглосуточно" ? $localShedule['FriStart'] . "-" . $localShedule['FriEnd'] : "круглосуточно") . "</br></br>";
                    echo "Суббота: " . ($localShedule['SatStart'] != "круглосуточно" ? $localShedule['SatStart'] . "-" . $localShedule['SatEnd'] : "круглосуточно") . "</br>";
                    echo "Воскресенье: " . ($localShedule['SunStart'] != "круглосуточно" ? $localShedule['SunStart'] . "-" . $localShedule['SunEnd'] : "круглосуточно") . "</br></br>";
                }
            } else {

                if ($end - $start < 60) {
                    if (strpos($tempstr, "круглосуточно") == 0) {
                        $tempstr = substr($terminal->schedule, $end + 8);
                        $firstcolon = strpos($tempstr, ':');
                        $StartTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);
                        $firstcolon = strpos($tempstr, ':');
                        $EndTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);

                        $localShedule['MonStart'] = $StartTime;
                        $localShedule['MonEnd'] = $EndTime;

                        $localShedule['TueStart'] = $StartTime;
                        $localShedule['TueEnd'] = $EndTime;


                        $localShedule['WedStart'] = $StartTime;
                        $localShedule['WedEnd'] = $EndTime;


                        $localShedule['ThuStart'] = $StartTime;
                        $localShedule['ThuEnd'] = $EndTime;


                        $localShedule['FriStart'] = $StartTime;
                        $localShedule['FriEnd'] = $EndTime;


                        $result[]['days'] = 31;
                        $result[count($result) - 1]['time'] = $StartTime . '-' . $EndTime;
                    } else {

                        $tempstr = substr($tempstr, strpos($tempstr, "круглосуточно") + 2);

                        $localShedule['MonStart'] = "круглосуточно";
                        $localShedule['MonEnd'] = "круглосуточно";

                        $localShedule['TueStart'] = "круглосуточно";
                        $localShedule['TueEnd'] = "круглосуточно";


                        $localShedule['WedStart'] = "круглосуточно";
                        $localShedule['WedEnd'] = "круглосуточно";


                        $localShedule['ThuStart'] = "круглосуточно";
                        $localShedule['ThuEnd'] = "круглосуточно";


                        $localShedule['FriStart'] = "круглосуточно";
                        $localShedule['FriEnd'] = "круглосуточно";

                        $result[]['days'] = 31;
                        $result[count($result) - 1]['time'] = "круглосуточно";
                    }


                    if ($display == $displaylevel) {
                        echo "Понедельник: " . ($localShedule['MonStart'] != "круглосуточно" ? $localShedule['MonStart'] . "-" . $localShedule['MonEnd'] : "круглосуточно") . "</br>";
                        echo "Вторник: " . ($localShedule['TueStart'] != "круглосуточно" ? $localShedule['TueStart'] . "-" . $localShedule['TueEnd'] : "круглосуточно") . "</br>";
                        echo "Среда: " . ($localShedule['WedStart'] != "круглосуточно" ? $localShedule['WedStart'] . "-" . $localShedule['WedEnd'] : "круглосуточно") . "</br>";
                        echo "Четверг: " . ($localShedule['ThuStart'] != "круглосуточно" ? $localShedule['ThuStart'] . "-" . $localShedule['ThuEnd'] : "круглосуточно") . "</br>";
                        echo "Пятница: " . ($localShedule['FriStart'] != "круглосуточно" ? $localShedule['FriStart'] . "-" . $localShedule['FriEnd'] : "круглосуточно") . "</br></br>";
                    }
                }

                $sat = strpos($tempstr, "Суббота");
                $sun = strpos($tempstr, "Воскресенье");
                $colon = strpos($tempstr, ":");
                $closed = strpos($tempstr, "Выходной");

                if ($sun - $sat > 33) {
                    if ($colon < $sun) {
                        $firstcolon =   $colon;
                        $StartTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);
                        $firstcolon = strpos($tempstr, ':');
                        $EndTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);

                        $localShedule['SatStart'] = $StartTime;
                        $localShedule['SatEnd'] = $EndTime;

                        $result[]['days'] = 32;
                        $result[count($result) - 1]['time'] =  $StartTime . '-' . $EndTime;;
                    }

                    if ($closed < $sun) {
                        $tempstr = substr($tempstr, $closed + 6);
                        $localShedule['SatStart'] = "Выходной";
                        $localShedule['SatEnd'] = "Выходной";
                        $result[]['days'] = 32;
                        $result[count($result) - 1]['time'] =  "Выходной";
                    }

                    $closed = strpos($tempstr, "Выходной");


                    $colon = strpos($tempstr, ":");
                    $closed = strpos($tempstr, "Выходной");

                    if ($colon <> 0) {
                        $firstcolon =   $colon;
                        $StartTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);
                        $firstcolon = strpos($tempstr, ':');
                        $EndTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);

                        $localShedule['SunStart'] = $StartTime;
                        $localShedule['SunEnd'] = $EndTime;

                        if (($localShedule['SunEnd'] == ($localShedule['SatEnd']) && ($localShedule['SunStart'] == $localShedule['SatStart']))) {
                            $result[count($result) - 1]['days'] = "96";
                        } else {

                            $result[]['days'] = 64;
                            $result[count($result) - 1]['time'] =  $StartTime . '-' . $EndTime;;
                        }
                    }

                    if ($closed <> 0) {
                        $tempstr = substr($tempstr, $closed + 6);
                        $localShedule['SunStart'] = "Выходной";
                        $localShedule['SunEnd'] = "Выходной";
                        if (($localShedule['SunEnd'] == ($localShedule['SatEnd']) && ($localShedule['SunStart'] == $localShedule['SatStart']))) {
                            $result[count($result) - 1]['days'] = "96";
                        } else {

                            $result[]['days'] = 64;
                            $result[count($result) - 1]['time'] =  "Выходной";
                        }
                    }
                } else {

                    $colon = strpos($tempstr, ":");
                    $closed = strpos($tempstr, "Выходной");

                    if ($colon <> 0) {
                        $firstcolon =   $colon;
                        $StartTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);
                        $firstcolon = strpos($tempstr, ':');
                        $EndTime = substr($tempstr, $firstcolon - 2, 5);
                        $tempstr = substr($tempstr, $firstcolon + 2);

                        $localShedule['SunStart'] = $StartTime;
                        $localShedule['SunEnd'] = $EndTime;

                        $localShedule['SatStart'] = $StartTime;
                        $localShedule['SatEnd'] = $EndTime;

                        $result[]['days'] = 96;
                        $result[count($result) - 1]['time'] = $StartTime . '-' . $EndTime;;
                    }

                    if ($closed <> 0) {
                        $tempstr = substr($tempstr, $closed + 6);
                        $localShedule['SunStart'] = "Выходной";
                        $localShedule['SunEnd'] = "Выходной";

                        $localShedule['SatStart'] = "Выходной";
                        $localShedule['SatEnd'] = "Выходной";

                        $result[]['days'] = 96;
                        $result[count($result) - 1]['time'] = "Выходной";
                    }
                }
                if ($display == $displaylevel) {
                    echo "Суббота: " . ($localShedule['SatStart'] != "Выходной" ? $localShedule['SatStart'] . "-" . $localShedule['SatEnd'] : "Выходной") . "</br>";
                    echo "Воскресенье: " . ($localShedule['SunStart'] != "Выходной" ? $localShedule['SunStart'] . "-" . $localShedule['SunEnd'] : "Выходной") . "</br></br>";
                }
            }


            return $result;
        }
    }



    save_terminalsdata();
    save_deliveryprices();




    ?>
</body>

</html>