<?php

include "../debug/debug.class.php";

$debug = new Debug("Отладка сервиса транспортной компании");


set_time_limit(0);
ini_set('max_execution_time', 0);
ignore_user_abort(true);
ini_set('memory_limit', '2200M');

header("Access-Control-Allow-Origin:*");


$grapth = true;



if (isset($_GET['a'])) $action = 1000 + $_GET['a'];
else $action = 1000;
$action = $_GET['action'];


if (isset($_GET['display'])) {

    $grapth = true;

?>
    <html>
    <header>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        <style>
            body {

                background-color: #fff;

            }

            .container {
                margin: 70px 150px;
                width: auto;
                height: auto;
                background-color: #fff;
                padding: 0px;
                padding-top: 100px;
            }

            .button {

                background: forestgreen;
                color: #fff;
                font-family: tahoma;
                margin: 3px;
                margin-top: 15px;
                padding: 8px;
                border-radius: 3px;
                cursor: pointer;

            }

            .button:hover {
                background: rgb(56, 165, 56);
                ;
                ;
            }
        </style>

        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/export-data.js"></script>
        <script src="https://code.highcharts.com/modules/accessibility.js"></script>

        <script src="https://code.jquery.com/jquery-3.7.0.slim.js" integrity="sha256-7GO+jepT9gJe9LB4XFf8snVOjX3iYNb0FHYr5LI1N5c=" crossorigin="anonymous"></script>

        <script src="js\postapi.js"></script>




        <style>
            .highcharts-figure,
            .highcharts-data-table table {
                min-width: 320px;
                max-width: 800px;
                margin: 1em auto;
            }

            .highcharts-data-table table {
                font-family: Verdana, sans-serif;
                border-collapse: collapse;
                border: 1px solid #ebebeb;
                margin: 10px auto;
                text-align: center;
                width: 100%;
                max-width: 500px;
            }

            .highcharts-data-table caption {
                padding: 1em 0;
                font-size: 1.2em;
                color: #555;
            }

            .highcharts-data-table th {
                font-weight: 600;
                padding: 0.5em;
            }

            .highcharts-data-table td,
            .highcharts-data-table th,
            .highcharts-data-table caption {
                padding: 0.5em;
            }

            .highcharts-data-table thead tr,
            .highcharts-data-table tr:nth-child(even) {
                background: #f8f8f8;
            }

            .highcharts-data-table tr:hover {
                background: #f1f7ff;
            }

            input[type="number"] {
                min-width: 50px;
            }


            .subheader {
                font-family: tahoma;
                margin-bottom: 40px;
                cursor: pointer;
            }

            .diagcontainer {
                padding: 20px;

            }



            .current,
            .status {
                display: grid;
                grid-template-columns: 200px 1fr;
                font-family: tahoma;
                margin-top: 10px;
            }

            .status_header,
            .current_header {
                font-weight: bold;
            }

            .status_text {}

            .current {}



            .current_text {}

            .historyrow {
                display: grid;
                grid-template-columns: 200px 200px 1fr 200px 100px;

                padding: 7px 0;
                font-family: tahoma;
            }

            .historyheader {
                display: grid;
                grid-template-columns: 200px 200px 1fr 200px 100px;
                position: sticky;
                top:0;
                padding: 7px 0;
                font-family: tahoma;
                background: #ccc;
            }

            .historyrow div,
            .historyheader div {

                padding-left: 7px;

            }

            .historyrow:nth-child(2n) {
                background: #eee;
            }

            .container2 {
                position: relative;
                width: calc(100% - 70px);
                margin-left: 50px;
                border: solid.1pc #eaeaea;
                min-height: 50px;
                max-height: 200px;
                overflow-y: scroll;
            }
            .playing,.playing:nth-child(2n){
                background: #bbffbb;
            }
            .historyfooter{
                font-family: tahoma;
                display:flex;
                align-items: flex-end;
                justify-content: flex-end;
                margin-top: 15px;
                margin-right:15px;
            }
        </style>

    </header>

    <body>
        <div class="container">
        <?php
    } else {
        $grapth = false;
    }
    //   phpinfo();
    //Настройка отображения ошибок
    ini_set('display_errors', 1);

    set_time_limit(36000000000);
    ini_set('max_execution_time', 360000000);
    ignore_user_abort(true);

    ini_set('memory_limit', '2200M');

    error_reporting(E_ALL);

    include "delivery.php"; //класс работы БД
    include "messagelog.class.php"; //Класс логирования сообщений
    include "delivery_company.interface.php"; //родительский класс от которого должны наследоваться все остальные транспортные компании
    include "APItools.class.php"; //Класс для анализа API    
    include "comparebase.class.php"; //Класс сравнения баз


    //////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////Переменные по умолчанию для создания экземпляра класса транспотрной компании/////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////





    /////веса для получения стоимости доставки
    $weights = array(5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000);

    /////объемы для получения стоимости доставки
    $volumes = array(0.1);
    ////Id транспортной компании из таблицы Posts
    $postid = 1;
    ////Лимит запрашиваемых строк с со стоимостью достваки
    $priceslimit = 1000000000;
    ////Уровень детализации
    $detalization = 1;
    ///Флаги для логирования 
    ///   [0]=>true/false  выводить информацию на экран
    ///   [1]=>true/false  выводить информацию в файл
    ///   [2]=>true/false  выводить информацию в БД
    $logflags = [true, true, false];




    ////////////////////////////////////////////////////////////////////
    ///////              ПОДКЛЮЧЕНИЕ ТРАНСПОРТНЫХ КОМПАНИЙ        //////
    ////////////////////////////////////////////////////////////////////

    ////стандартные методы классов транспортных компаний

    ///
    ///    save_terminalsdata()  сохранить данные терминала в таблицу posts и таблицу post_work
    ///


    ///    save_deliveryprices() сохранить стоимости доствки в зависимости от параметров при создаии экземпляра класса
    ///
    ///     ---$weights         веса которые нужно получить
    ///     ---$priceslimit     максимальный лимит строк со стоимостью доставки


    ///
    ///    save_all()  сохраняет данные терминалов и данные стоимости доставки
    ///



    ///
    ///    log() выводит log в зависимости отнастроек
    ///     ---$logflags
    ///             [0]=>true/false  выводить информацию на экран
    ///             [1]=>true/false  выводить информацию в файл
    ///


    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///    Подключение класа транспортной компании ozon             ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////

    /*
    include "ozon_delivery.class.php";
    $postid = 32;
    $weights = array(1000, 5000, 10000, 15000, 20000, 25000, 30000, 35000, 40000, 45000, 50000, 60000);
    $ozon_delivery = new ozon_delivery($weights, $volumes, $postid, 3000, $detalization, $logflags, "img\ozonrocked.jpg", $grapth);

*/


    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///        Подключение класа транспортной компании DPD          ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    include "dpd_delivery.class.php";
    $weights = array(5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 800, 900, 1000, 1500, 2000);
    //$weights = array(5, 10, 25, 50);
    $postid = 1;
    $dpd_delivery = new dpd_delivery($weights, $volumes, $postid, $priceslimit, $detalization, $logflags, "img\dpd.png", $grapth);
    // $dpd_delivery->getCityList();
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////




    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///    Подключение класа транспортной компании Байкал-Сервис    ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    include "baikal_delivery.class.php";
    $postid = 24;
    $priceslimit = 1000000;
    $baikal_delivery = new baikal_delivery($weights, $volumes, $postid, $priceslimit, $detalization, $logflags, "https://petrozavodsk.baikalsr.ru/local/templates/main/i/logo.svg", $grapth);



    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///    Подключение класа транспортной компании boxberry         ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    include "boxberry_delivery.class.php";
    $postid = 13;
    $weights = array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000, 11000, 12000, 13000, 14000, 15000);
    //$boxberry_delivery = new boxberry_delivery($weights, $volumes, $postid, $priceslimit, $detalization, $logflags, "img\boxberry.png", $grapth);


    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///    Подключение класа транспортной компании Деловые линии    ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    include "dl_delivery.class.php";
    $postid = 3;
    $weights = array(5, 10, 25, 50, 100, 150, 200, 250, 5, 10, 25, 50, 100, 150, 200, 250, 5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000);
    $volumes = array(0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1,  0.2, 0.2, 0.2, 0.2, 0.2, 0.2, 0.2, 0.2,  0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

    $dl_delivery = new dl_delivery($weights, $volumes, $postid, $priceslimit, $detalization, $logflags, "https://novtehkomponent.ru/upload/medialibrary/fe3/fe3eeae273e7802667b60813a895bb69.png", $grapth);


    ////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////// 



    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///    Подключение класа транспортной компании СДЭК             ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    include "cdek_delivery.class.php";
    $postid = 31;
    $weights = array(5, 10, 25, 50, 100, 150, 200, 250, 5, 10, 25, 50, 100, 150, 200, 250, 5, 10, 25, 50, 100, 150, 200, 250, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000);
    $volumes = array(0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1, 0.1,  0.2, 0.2, 0.2, 0.2, 0.2, 0.2, 0.2, 0.2,  0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 0.3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

    //$cdek_delivery = new cdek_delivery($weights, $volumes, $postid, $priceslimit, $detalization, $logflags, "https://www.waypark.ru/upload/iblock/911/91101b34736345fc6d3968398b0cfcf2.jpg", $grapth);





    ////////////////////////////////////////////////////////////////////


    $delivery = new delivery([], 13, []);
    //////////////////////////////////////////////////////////////////// 

    ////////////////////////////////////////////////////////////////////
    ///                                                             ////
    ///                      Обработка API                          ////
    ///                                                             ////
    ////////////////////////////////////////////////////////////////////


    if ((isset($_GET['action'])) && (isset($_GET['companyid']))) {
        $company = '';


        switch ($_GET['companyid']) {

            case 13:
                $company = $boxberry_delivery;
                break;
            case 24:
                $company = $baikal_delivery;
                break;
            case 1:
                $company = $dpd_delivery;
                break;
            case 3:
                $company = $dl_delivery;
                break;
            case 31:
                $company = $cdek_delivery;
                break;
            case 32:
                $company = $ozon_delivery;
                break;
        }

        switch ($_GET['action']) {

            case 1:
                $company->save_all_try();
                break;
            case 2:
                $company->save_terminalsdata_try();
                break;
            case 3:
                $company->save_deliveryprices_try();
                break;
            case 4:
                $company->check_Bases();
                break;
            case 5:
                $company->compare_Bases();
                break;
            case 6:
                $company->toggleStop($_GET['companyid']);
                break;
            case 7:
                $company->save_city_try();
                break;
            case 1001:
                echo json_encode($company->get_currentprice($_GET));
                break;
            case 1002:
                $company->save_deliveryprices_try($_GET['city2']);
                break;
            case 1003:
                $company->save_terminalsdata_try($_GET['city2']);
                break;
            case 1004:
                echo "1004</br>";
                if (!isset($_GET['date'])) exit;
                echo "date</br>";
                if (!isset($_GET['companyid'])) exit;
                echo "companyid</br>";

                if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_GET['date'])) exit;
                echo "datecheck</br>";
                $delivery->setActual($_GET['companyid'], $_GET['date'], $_GET['updatetype'], isset($_GET['city2']) ? $_GET['city2'] : false);
                break;
        }
    }




    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////


    if (isset($_GET['display'])) {
        ?>



        </div>
    </body>

    </html>
<?php
    } ?>