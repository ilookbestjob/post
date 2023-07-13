<?php
require 'terminaldata.php';


/*
Класс работы с БД сайта для загрузки пунктов достаки, стоимости и времени доставки

=================Функции===================

__construct(array $weights, int $postype_id, int $logtype)
Создает экземпляр класса

Параметры

$weights              -  веса для рассчета стоимости доставки
$postype_id           -  ID пункта выдачи в таблице posts 
$logtype              -  Вариант отображения отладосной информации 0 - не отображать, 1 - отображать

............................................

get_TownIdByFias($fias) Поиск id города в базе cityfias по ФИАС
        
Параметры
$fias         -строка FIAS

Возвращает
id города

............................................

clear_UpdateFlags() Очистка флагов обновления


...........................................

save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y)
Сохраняет данные одного терминала в базу

Параметры

$city_id           -id города из базы city

$terminal_id       -id терминала

$terminal_name     -Название терминала

$terminal_street   -Улица терминала

$terminal_house    -Дом терминала

$terminal_x        -Широта терминала 

$terminal_y        -Долгота терминала


...........................................

 get_FIAStoUpdatePrices()  -выбирает приоритетеные для обновления пункты выдачи 

 Возвращает

 объект  $terminal->FIAS        -ФИАС 
         $terminal->post_id     -id пункта выдачи
         $terminal->city        -Название города пункта выдачи


...........................................

save_pricedata($postid, $flkurier, $price, $weight, $volume, $days)
Сохраняет данные стоимости в бд

Параметры
$postid            -id пункта выдачи из таблицы post
$flkurier          -Вид доставки 0-до терминала, 1-курьерская доставка от терминала
$price             -стоимость доставки
$weight            -вес доставки
$volume            -объем доставки
$days              -срок доставки в днях

...........................................

check_Changes()   проверяет количество измененных строк

Возвращает

количество измененных строк

...........................................

clear_Schedule($posttype_id)   - Очищает расписание пункта выдачи по его row_id в базе posttype

Параметры
$posttype_id  -id пункта выдачи в таблице posts;



...........................................

save_Schedule($postid,$schedule)  - Добавляет расписание пункта выдачи в базу

$postid    -  id пункта выдачи из таблицы posts

$schedule  -  Расписание пункта выдачи


============================================



*/

class delivery
{
    public $weights, $posttype_id;
    private $server;
    private $base;
    private $user;
    private $port;
    private $bdpassword;
    public $connection;


    private $logtype;
    public $ctr = 0;
    public $addcounter = 0;
    public $updatecounter = 0;
    public $atemptscounter = 0;


    public $Scheduleaddcounter = 0;
    public $Scheduleemptycounter = 0;
    public $Scheduleatemptscounter = 0;

    public $Priceaddcounter = 0;
    public $Priceupdatecounter = 0;
    public $Priceatemptcounter = 0;
    public $PricegetErrors = 0;
    public $flagstofill = array('flholiday' => 'Информация о работе в выходные дни', 'fmoney' => "Информация о приеме наличных средств", 'fterminal' => 'Информация о приеме безналичных средств', 'period' => 'Информация о сроках достаки', 'limitvolume' => 'Информация о предельном объеме доставки', 'limitload' => 'Информация о предельном весе доставки');
    public $usedflags = array();

    public $affectedPostIds = array();



    public function __construct(array $weights, $postype_id, $logtype)
    {
        include('../conf.php');
        if (strpos($sServer, ":") !== false) $sServer = substr($sServer, 0, strpos($sServer, ":"));
        $this->server = $sServer;
        $this->base = $stDB;
        $this->user = $sUser;
        $this->bdpassword = $sPass;
        $this->port = $sPort;
        $this->connection = $this->connectDB();


        $this->weights = $weights;
        $this->posttype_id = $postype_id;
        $this->logtype = $logtype;

        //$this->messagelog = new messagelog($this->posttype_id, "", true, true, true, 0);
    }

    public function __destruct()
    {
        // $this->set_DeleteFlags();
        //echo "<script>alert(\"set_DeleteFlags()\");</script>";


    }



    //подключение к БД
    function connectDB()
    {
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);
        mysqli_query($connection, "set names cp1251");
        if (!$connection) {
            die("Ошибка подключения к базе");
        }
        // echo "соединение с БД:" . __CLASS__ . "(" . __FILE__ . ")";
        return $connection;
    }

    function reconnectDB()
    {
        sleep(5);
        $this->connection = $this->connectDB();
    }

    //получаем id города по FIAS  0  
    public function get_TownIdByFias($fias)
    {
        $sql = 'Select * from cityfias where fias="' . $fias . '"';
        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        return $baserow['city_id'];
    }
    //получаем Имя города по ID    
    public function get_TownOptByID($id)
    {
        $sql = 'Select row_id, city, state, region, (select cityregion.name from cityregion where city.cityregion_id=cityregion.row_id limit 1) as regionmain from city where row_id=' . intval($id);
        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        //echo mb_convert_encoding($baserow['regionmain'], 'utf-8', 'windows-1251');		  
        $region = mb_convert_encoding($baserow['regionmain'], 'utf-8', 'windows-1251');
        if ($region == "") $region = mb_convert_encoding($baserow['region'], 'utf-8', 'windows-1251');
        return array('id' => $baserow['row_id'], 'name' => mb_convert_encoding($baserow['city'], 'utf-8', 'windows-1251'), 'state' => mb_convert_encoding($baserow['state'], 'utf-8', 'windows-1251'), 'region' => $region);
    }
    //получаем Имя города по ID    
    public function get_TownIdByName($name, $region, $postindex)
    {
        $regionsql = mb_convert_encoding($region, 'windows-1251', 'utf-8');
        $namesql = mb_convert_encoding($name, 'windows-1251', 'utf-8');
        $sql = 'Select row_id from cityregion where name="' . $regionsql . '" or namelit="' . $regionsql . '"';
        //echo $sql;
        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        $region_id = intval($baserow['row_id']);
        $dop = "";
        if ($region_id != 0) $dop = " and cityregion_id=" . intval($region_id);
        $sql = 'Select row_id from city where city="' . $namesql . '"' . $dop;
        $basedata = mysqli_query($this->connection, $sql);
        if (mysqli_num_rows($basedata) != 1) {
            echo "Найдено " . mysqli_num_rows($basedata) . " городов: " . $name . "<br>";
            return 0;
        }
        $baserow = mysqli_fetch_array($basedata);
        return $baserow['row_id'];
    }
    //
    public function get_FiasbyTownId($id)
    {
        $sql = 'Select * from cityfias where city_id="' . $id . '"';
        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        return $baserow['fias'];
    }


    //получаем id города по произвольному полю таблицы сшен   
    public function get_TownIdByField($field, $query)
    {
        $sql = 'Select * from city where ' . $field . '="' . $query . '"';

        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        if ($baserow) {
            if ($baserow['row_id'] == "") {
                $this->printlog("ID города по значению [" . $query . "] поля [" . $field . "] не найдено!!!", 1);
            }
        } else {
            echo "<br><br>Город со значением поля $field=$query не найден!Запрос: $sql<br><br>";
        }
        return $baserow ? $baserow['row_id'] : null;
    }

    public function get_TownArrIdByField($field, $query)
    {
        $sql = 'Select * from city where ' . $field . '="' . $query . '"';

        $basedata = mysqli_query($this->connection, $sql);
        $baserow = mysqli_fetch_array($basedata);
        if ($baserow['row_id'] == "") {
            $this->printlog("ID города по значению [" . $query . "] поля [" . $field . "] не найдено!!!", 1);
        }
        return $baserow;
    }


    //-----------------------------------------------------------------------
    public function clear_UpdateFlags()
    {
        $sql = mysqli_query($this->connection, "update post set flupd=0 where posttype_id=" . $this->posttype_id . " and post.flkurier<>2");
        $sql = mysqli_query($this->connection, "update post, postwork set postwork.flupd=0 where post.posttype_id=" . $this->posttype_id . " and postwork.post_id=post.row_id and post.flkurier<>2");
        // $sql = mysqli_query($this->connection, "update post, postcalc set postcalc.del=0 where post.posttype_id=" . $this->posttype_id . " and postwork.post_id=post.row_id and post.flkurier<>2");

    }

    public function checkstopAll()
    {
        if (file_exists('stopall.txt')) {
            $fp = fopen('stopall.txt', 'rt');
            $stopstring = fgets($fp);


            $stops = explode(',', $stopstring);

            foreach ($stops as $stop) {
                if ($stop == $this->posttype_id) {
                    echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка данных терминалов остановлена. В файле stopall.txt присутвует значение $this->posttype_id</div>";
                    exit;
                }
            }
        }
    }

    //--------------------------------------------------------------------------
    //Записывет данные одного пункта выдачи в базу    
    //flkurier=0 - терминал
    //flkurier=1 - аресная доставка (есть пункт выдачи в том городе)
    //flkurier=2 - аресная доставка (нет пункта выдачи в том городе)
    //flkurier=3 - постамат

    public function save_TerminalData($city_id, $terminal_id, $terminal_name, $terminal_street, $terminal_house, $terminal_x, $terminal_y, $flags = array())
    {
        $this->checkstopAll();

        $flagslocal = $this->prepareFlags($flags);
        $id = 0;

        $terminal_name = mb_convert_encoding($terminal_name, 'windows-1251', 'utf-8');
        $terminal_street = mb_convert_encoding($terminal_street, 'windows-1251', 'utf-8');
        $terminal_house = mb_convert_encoding($terminal_house, 'windows-1251', 'utf-8');

        $this->atemptscounter++;


        //проверяем есть ли уже такая запись о пунктах выдачи
        $sql_id = "select row_id from nordcom.post where city_id=" . intval($city_id) . " and posttype_id=" . $this->posttype_id . "  and code='" .   $terminal_id . "' and flkurier=" . (isset($flagslocal["flkurier"]) ? " and " . $flagslocal['flkurier'] : "");


        if (isset($flags["flkurier"])) {
            if ($flags["flkurier"] > 0) {
                $sql_id = "select row_id from nordcom.post where city_id=" . intval($city_id) . " and posttype_id=" . $this->posttype_id  . (isset($flags["flkurier"]) ? " and flkurier=" . $flags['flkurier'] : "");
            } else {
                $sql_id = "select row_id from nordcom.post where city_id=" . intval($city_id) . " and posttype_id=" . $this->posttype_id . "  and code='" .   $terminal_id . "' " . (isset($flags["flkurier"]) ? " and flkurier=" . $flags['flkurier'] : "");
            }
        } else {
            $sql_id = "select row_id from nordcom.post where city_id=" . intval($city_id) . " and posttype_id=" . $this->posttype_id . "  and code='" .   $terminal_id . "' " . (isset($flags["flkurier"]) ? " and flkurier=" . $flags['flkurier'] : "");
        }
        echo "</br>" . $sql_id . "";

        $sql_id = mysqli_query($this->connection, $sql_id);


        //если нет записи, то создаем новую
        if (mysqli_num_rows($sql_id) == 0) {
            $sql = "insert into `post` (`flupd`,  `store1_id`, `city_id`, `posttype_id`, `code`, `name`,`street`, `house`, `x`, `y`,`typeoffice`,`tariff`,`dataupd` " . $flagslocal[0] . ") values (1, 1, '" . intval($city_id) . "'," . $this->posttype_id . " , '" . $terminal_id . "', '" . $terminal_name . "','" . str_replace(",", " ", $terminal_street) . "', '" . str_replace(",", " ", $terminal_house) . "', '" . $terminal_x . "','" . $terminal_y . "' ,0,0,NOW()" . $flagslocal[1] . ")";
            echo "</br>" . $sql . "";
            mysqli_query($this->connection, $sql);
            $this->printlog("Добавить  $sql", 0);
            if (mysqli_error($this->connection)) {
                echo mysqli_error($this->connection) . "</br>" . $sql . "</br></br></br>";;
            } else {
                $this->addcounter++;
            }
        }



        // если есть запись, то обновляем существующую запись 
        else {
            $sql_id = mysqli_fetch_row($sql_id);
            $id = $sql_id[0];
            $sql = "update `post` set `flupd`=1,  `del`=0, `city_id`=" . intval($city_id) . ", `posttype_id`=" . $this->posttype_id . " , `code`='" . $terminal_id . "', `name`='" . $terminal_name . "',`street`='" . $terminal_street . "', `house`='" . $terminal_house . "', `x`='" . $terminal_x . "', `y`='" . $terminal_y . "' ,`typeoffice`=0,`tariff`=0,`dataupd`=NOW()" . $flagslocal[2] . " where `row_id`=" . $id;
            echo $sql;
            mysqli_query($this->connection, $sql);
            // echo  "</br>Запрос ".mb_convert_encoding( $sql,  'utf-8','windows-1251');
            // echo "</br>Затронуто строк: ".mysqli_affected_rows($this->connection)."</br>";
            $this->printlog("Редактировать $sql", 0);
            if (mysqli_error($this->connection)) {
                echo mysqli_error($this->connection) . "</br>" . $sql . "</br></br></br>";;
            } else {

                $this->updatecounter++;
            }
        }

        if ($id == 0) {
            $id = mysqli_insert_id($this->connection);
        }




        $this->affectedPostIds[] = $id;
        return $id;
    }
    //--------------------------------------------------------------------------
    //Отмечает записи на удаление у post
    public function get_current_date()
    {
        $res = mysqli_query($this->connection, "select now() as cd");
        $row = mysqli_fetch_array($res);
        return $row[0];
    }
    //--------------------------------------------------------------------------
    //Отмечает записи на удаление у post
    public function set_DeleteFlags($cityid = null)
    {
        $dop = "";
        if ($cityid > 0)  $dop = " and post.city_id=" . intval($cityid);
        $sql = mysqli_query($this->connection, "update post set del=1 where flupd=0 and posttype_id=" . $this->posttype_id . " and post.flkurier<>2" . $dop);
        $basedata = mysqli_query($this->connection, $sql);

        $sql = mysqli_query($this->connection, "update post, postwork set postwork.del=1 where postwork.flupd=0 and post.posttype_id=" . $this->posttype_id . " and postwork.post_id=post.row_id and post.flkurier<>2" . $dop);
        $basedata = mysqli_query($this->connection, $sql);
    }

    //---------------------------------------------------------------------------------
    public function set_PriceDeleteFlags($date = null)
    {
        if ($date) {
            //$sql = mysqli_query($this->connection, "update post, postcalc set postcalc.del=0 where post.posttype_id=" . $this->posttype_id . " and postcalc.post_id=post.row_id and post.flkurier<>2");
            $sql = mysqli_query($this->connection, "update post, postcalc set postcalc.del=1 where postcalc.dataupd<'" . $date . "' and post.posttype_id=" . $this->posttype_id . " and postcalc.post_id=post.row_id and post.flkurier<>2");
            $basedata = mysqli_query($this->connection, $sql);
        }
    }
    //-----------------------------------------------------------------------
    public function set_PriceDeleteFlagsPost($postid, $date = null)
    {
        if ($date) {
            $sql = "update post set dataupdcalc=NOW() where post.row_id=" . $postid;
            mysqli_query($this->connection, $sql);

            $sql = "update post, postcalc set postcalc.del=1 where postcalc.dataupd<'" . $date . "' and post.row_id=" . $postid . " and post.posttype_id=" . $this->posttype_id . " and postcalc.post_id=post.row_id";
            echo $sql;
            $sql = mysqli_query($this->connection, $sql);
            $basedata = mysqli_query($this->connection, $sql);
        }
        //----Удаляем все цены, нет обновления
        else {
            $sql = mysqli_query($this->connection, "update post, postcalc set postcalc.del=1 where post.row_id=" . $postid . " and post.posttype_id=" . $this->posttype_id . " and postcalc.post_id=post.row_id");
            $basedata = mysqli_query($this->connection, $sql);
        }
    }

    //-----------------------------------------------------------------------



    //Выбирает FIAS для обновления
    public function get_FIAStoUpdatePrices($city_id = null)
    {

        $sql = "select post.row_id postid, city.row_id, city.dpdcityid, post.name, city.city as city, post.flkurier as flkurier
        ,(select min(postcalc.dataupd) from postcalc where postcalc.post_id=post.row_id) as ord,cityfias.city_id,cityfias.fias as fias
        from post, city,cityfias
        where post.del=0 and post.city_id=city.row_id and post.posttype_id=" . $this->posttype_id . " and post.flkurier<>2 and post.city_id=cityfias.city_id" . ($city_id != null ? " and post.city_id=$city_id" : "") . "  order by ord";


        $res = mysqli_query($this->connection, $sql);
        if (mysqli_error($this->connection)) {
            echo mysqli_error($this->connection);
        }
        $data = [];
        while ($row = mysqli_fetch_array($res)) {
            $terminal = new TeminalData();
            $terminal->FIAS = $row['fias'];
            $terminal->post_id = $row['postid'];
            $terminal->city = $row['city'];
            $terminal->flkurier = $row['flkurier'];
            $data[] = $terminal;
        }
        return $data;
    }



    //Выбирает город для обновления
    public function get_CitytoUpdatePrices($city_id = null)
    {

        //,(select min(postcalc.dataupd) from postcalc where postcalc.post_id=post.row_id) as ord

        echo   $sql = "select post.row_id postid, post.code as code,city.row_id as cityid, city.kladr as kladr,city.dpdcityid as dpdid, city.dpdcode as dpdcode, city.boxberrycode as boxberrycode, post.name, city.city as city, post.flkurier as flkurier 
    , post.dataupdcalc as ord
    from post, city
    where post.del=0 and post.city_id=city.row_id and post.posttype_id=" . $this->posttype_id . " and city.biggest_city=1 and post.flkurier<>2" . ($city_id != null ? " and post.city_id=$city_id" : "") . " order by ord";




        $res = mysqli_query($this->connection, $sql);
        if (mysqli_error($this->connection)) {
            echo mysqli_error($this->connection);
        }
        $data = [];
        while ($row = mysqli_fetch_array($res)) {
            $terminal = new TeminalData();

            $terminal->post_id = $row['postid'];
            $terminal->city = $row['city'];
            $terminal->cityid = $row['cityid'];
            $terminal->dpdid = $row['dpdid'];
            $terminal->boxberrycode = $row['boxberrycode'];
            $terminal->code = $row['code'];
            $terminal->kladr = $row['kladr'];
            $terminal->flkurier = $row['flkurier'];
            $terminal->date = $row['ord'];
            $data[] = $terminal;
        }
        return $data;
    }
    //выводит лог
    public function printlog($text, $flags = array())
    {
        if (is_array($flags)) {
            $style = "";

            foreach ($flags as $key => $flag) {
                $style .= $key . ":" . $flag . ";";
            }

            echo  "<div style=\"width:auto;margin:50px 50px;margin-bottom:15px;margin-left:200px;font-family:tahoma;color:black;font-size:18px;$style\">" . $text . " </div>";
        }
    }


    //сохраняет одну цену доставки
    public  function save_pricedata($postid, $flkurier, $price, $weight, $volume, $days, $flags = array())
    {
        // $this->printlog($postid);
        echo "<br><br><br>=====================<br>";
        echo "Схраняем Цену";

        echo "ИД объекта              : $postid<br>";
        echo "Флаг курьерской доставки: $flkurier<br>";
        echo "Цена                    : $price<br>";
        echo "Вес                     : $weight<br>";
        echo "Объем                   : $weight<br>";
        echo "Дни                     : $days<br>";
        echo "Флаги                   : ";
        print_r($flags);
        echo "<br>";






        $this->checkstopAll();

        $flags = $this->prepareFlags($flags);

        //$sql = mysqli_query($this->connection, "update post set flupd=0 where posttype_id=" . $this->posttype_id . " and post.flkurier=$flkurier");

        $this->Priceatemptcounter++;


        //  if ($price > 0) {


        $sql = "select count(*) as quantity, `row_id` from `postcalc` where `post_id`=" . $postid . " and `weight`='" . $weight . "' and `volume`='" . $volume . "' and `flkurier`=" . $flkurier . (isset($flags['postservice_id']) ? " and `postservice_id`=" . $flags['postservice_id'] : "");

        // echo "</br></br></br>";
        $sql2 = "";
        $res = mysqli_query($this->connection, $sql);
        if (mysqli_error($this->connection)) {
            echo mysqli_error($this->connection);
        } else {
            $this->Priceaddcounter++;
        }

        $res2 = mysqli_fetch_array($res);
        //  echo $res2['quantity'] ;
        if ($res2['quantity'] == 0) {
            $sql2 = "insert into `postcalc` (`post_id`, `weight`, `price`, `days`,`flkurier`, `postservice_id`, `dataupd`,`volume`,`pref`) values (" . $postid . ", '" . $weight . "', '" . $price . "', '" . $days . "'," . $flkurier . "," . (isset($flags['postservice_id']) ? "and `postservice_id`=" . $flags['postservice_id'] : '0') . ", '" . date("Y-m-d H:i:s") . "'," . $volume . ",'' )";

            //echo $sql2."</br></br></br>";

            echo "Действие                   : добавлена";
        } else {

            $id2 = $res2['row_id'];
            $sql2 = "update `postcalc` set `post_id`=" . $postid . ", del=0, `weight`='" . $weight . "', `price`='" . $price . "', `days`='" . $days . "', `postservice_id`=" . (isset($flags['postservice_id']) ? "and `postservice_id`=" . $flags['postservice_id'] : '0') . ", `dataupd`= '" . date("Y-m-d H:i:s") . "' where `row_id`=" . $id2;

            //  echo $sql2."</br></br></br>";
            echo "Действие                   : изменена";
        }
        try {
            mysqli_query($this->connection, $sql2);
        } catch (Exception $e) {
            $this->reconnectDB();
        }


        echo "=====================<br><br><br><br><br>";

        if (mysqli_error($this->connection)) {

            //  $this->messagelog->addLog(4, $this->postid, "Ошибка БД",  "Ошибка при попытке выполнить запрос: $sql2", 0);
            echo "Ошибка при попытке выполнить запрос: $sql2";
            //return "Ошибка при попытке выполнить запрос: $sql2";

            $this->reconnectDB();

            mysqli_error($this->connection) . "</br>";
        } else {
            //echo "Запрос выполнен без ошибок: $sql2";
            // return "Запрос выполнен без ошибок: $sql2";

        }
        return $sql2;
        //}
    }
    //Подготовка флагов для добавления в бд
    public function prepareFlags($flags)
    {
        $result = array(0 => '', 1 => '', 2 => '');




        $fields = array_keys($flags);
        $data = array_values($flags);
        //print_r($fields);
        //print_r($data);
        foreach ($fields as $key => $field) {
            if (!in_array($field, $this->usedflags)) {
                $this->usedflags[] = $field;
            }
            $result[2] .= ",`" . $field . "`='" . $data[$key] . "'";
        }

        if (count($data) > 0) {
            $result[0] .= implode("`,`", $fields);
            $result[1] .= implode("','", $data);
            $result[0] = ",`" . $result[0] . "`";
            $result[1] = ",'" . $result[1] . "'";
        }

        return $result;
    }

    public function checkFlags()
    {
        $result = '';
        foreach ($this->flagstofill as $flagkey => $flag) {
            $result .= $flag . ": " . (in_array($flagkey, $this->usedflags) ? "есть" : "нет") . ", ";
        }

        return $result;
    }


    public  function check_TerminalChanges()
    {
        $sql = "select count(*) as rows_affected from `post` where posttype_id=" . $this->posttype_id . " and flupd=1";
        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        if (mysqli_error($this->connection)) {
            echo mysqli_error($this->connection) . "   " . $sql . "</br>";
        }
        $this->printlog("<strong>Попыток записи: </strong>" . $this->atemptscounter . "    <strong>Ошибок:</strong> " . ($this->atemptscounter - $this->addcounter - $this->updatecounter) . "    <strong>Затронутых строк: </strong>" . ($this->addcounter + $this->updatecounter) . "<strong> Добавленных строк: </strong>" . $this->addcounter . "    <strong>Измененных строк:</strong> " . $this->updatecounter, 0);
    }




    public  function check_TerminalSheduleChanges()
    {
        $this->printlog("Попыток записи: " . $this->Scheduleatemptscounter . "    Ошибок: " . ($this->Scheduleatemptscounter - $this->Scheduleaddcounter) . "    Добавленых строк: " . ($this->Scheduleaddcounter) . "    Пустых расписаний: " . ($this->Scheduleemptycounter), 0);
    }

    public  function check_TerminalPricesChanges()
    {
        $this->printlog("Попыток записи: " . $this->Priceatemptcounter . "    Ошибок: " . ($this->Priceatemptcounter - $this->Priceaddcounter - $this->Priceeditcounter) . "    Добавленых строк: " . ($this->Priceaddcounter) . "    Ошибок получения:" . ($this->PricegetErrors), 0);
    }


    public function checkPricesintegrity()
    {


        $sql = "select sum(binded) res from (select count( distinct(post.row_id)) as binded from `post` left join postcalc as pc on post.row_id=pc.post_id where post.posttype_id='$this->posttype_id' and pc.post_id<>0 group by pc.post_id) as t";

        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        $binded = $sql_row['res'];

        $sql = "select count(*)  as total from `post` where post.posttype_id='$this->posttype_id' ";
        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        $total = $sql_row['total'];

        $unbinded = $total - $binded;
        echo $this->printlog("Проверка целостности базы Стоимость доставки");
        echo $this->printlog("Всего терминалов <span style=\"color:navy\">$total</span> Терминалов без данных <span style=\"color:red\">$unbinded </span>Терминалов с данными <span style=\"color:forestgreen\">$binded</span>");
        return array('binded' => $binded, 'unbinded' => $unbinded, 'total' => $total);
    }



    public function checkPricesDate($daysOld)
    {
        $this->checkDataDate("postcalc", $daysOld);
    }



    public function checkScheduleDate($daysOld)
    {
        $this->checkDataDate("postwork", $daysOld);
    }

    public function checkDataDate($Table, $daysOld)
    {


        $date = date("Y-m-d 00:00:00", mktime(0, 0, 0, date('m'), date('d') - 3, date('Y')));
        $sql = "select count(*) as total from `post` left join $Table as pc on post.row_id=pc.post_id where post.posttype_id='$this->posttype_id' and pc.dataupd<='$date'";

        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        $result = $sql_row['total'];


        echo $this->printlog("Проверка данных базы Стоимость доставки позднне чем $daysOld д.");
        echo $this->printlog("Всего устаревших записей <span style=\"color:navy\">$result</span>");
        return $result;
    }



    public function checkSheduleintegrity()
    {



        $sql = "select sum(binded) res from (select count( distinct(post.row_id)) as binded from `post` left join postwork as pw on post.row_id=pw.post_id where post.posttype_id='$this->posttype_id' and pw.post_id<>0 group by pw.post_id) as t";

        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        $binded = $sql_row['res'];

        $sql = "select count(*)  as tl from `post` where post.posttype_id='$this->posttype_id' ";
        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);
        $total = $sql_row['tl'];

        $unbinded = $total - $binded;
        echo $this->printlog("Проверка целостности базы Режим работы");
        echo $this->printlog("Всего терминалов <span style=\"color:navy\">$total</span> Терминалов без данных <span style=\"color:red\">$unbinded </span>Терминалов с данными <span style=\"color:forestgreen\">$binded</span>");
        return array('binded' => $binded, 'unbinded' => $unbinded, 'total' => $total);
    }





    public  function clear_Schedule($postid)
    {
        $sql = mysqli_query($this->connection, "delete FROM postwork where post_id=" . $postid);
        $this->printlog("<strong>Очистка id# " . $postid . "</strong>", 1);
    }


    public  function save_Schedule($postid, $schedule)
    {

        echo "<br><br>=============Shedule====<br><br><br>";
        print_r($postid);
        echo "================<br><br><br>";
        print_r($schedule);
        if (is_array($schedule)) {
            if (count($schedule) != 0) {

                $this->clear_Schedule($postid);

                foreach ($schedule as $scheduleitem) {
                    $this->Scheduleatemptscounter++;
                    $time = mb_convert_encoding(
                        $scheduleitem['time'],
                        'windows-1251',
                        'utf-8'
                    );

                    $sql =  "replace into postwork set `flupd`=1,`post_id`='" . $postid . "',`postoperation_id`=2,`day`='" . $scheduleitem['days'] . "',`time`='" . $time . "',`per`='' ";
                    //echo $sql;
                    $this->printlog($sql, 1);
                    mysqli_query($this->connection, $sql);

                    if (mysqli_error($this->connection)) {
                        $this->printlog(mysqli_error($this->connection) . "</br>" . $sql, 1);
                    } else {
                        $this->Scheduleaddcounter++;
                    }
                }
            } else {
                $this->printlog("Пустое расписание", 1);
                $this->Scheduleemptycounter++;
            }
        }
    }

    public  function getPostbyId($postid)
    {

        $sql = 'select * from `posttype` where row_id="' . $postid . '"';
        $sql_result = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);

        return $sql_row;
    }
    //--------------------------
    public function checkstop($posttype_id)
    {
        if (file_exists('stopall.txt')) {
            $fp = fopen('stopall.txt', 'rt');
            $stopstring = fgets($fp);


            $stops = explode(',', $stopstring);
            $stopflag = false;


            foreach ($stops as $stop) {
                if ($stop == $posttype_id) {
                    $stopflag = true;
                }
            }
            fclose($fp);
            return $stopflag;
        }
    }



    ///////статитстические функции



    public function priceupdatestatistics($company)
    {

        $res = [];

        $sql = "SELECT LEFT(pc.dataupd,10),count(*) FROM postcalc pc, post p where p.row_id=pc.post_id and p.posttype_id=$company group by LEFT(pc.dataupd,10) ";

        return $this->SQLtoJSON($sql);
    }


    public function SQLtoJSON($sql)
    {
        $res = [];
        $sqlresult = mysqli_query($this->connection, $sql);


        while ($temp = mysqli_fetch_assoc($sqlresult)) {
            $res[] = $temp;
        }
        return $res;
    }


    public function setActual($companyid, $date, $updatetype, $city = false)
    {
        $sql = "";
        switch ($updatetype) {

            case 1:
                $sql = " update post set post.del=0 where post.del=1 and 
                post.postid=$companyid and post.dataupd>$date" . ($city ? " and cityid=$city" : "") . ";";
                break;
            case 2:
                $sql = " update postcalc set postcalc.del=0 where  postcalc.post_id in (select post.row_id postid from post, city where post.del=0 and 
                post.city_id=city.row_id and post.posttype_id=$companyid " . ($city ? " and post.city_id=$city" : "") . ") and  postcalc.dataupd>$date;";
                break;

            case 3:
                $sql = " update postwork set postwork.del=0 where postwork.post_id in (select post.row_id postid from post, city where post.del=0 and 
                    post.city_id=city.row_id and post.posttype_id=$companyid" . ($city ? " and post.city_id=$city" : "") . ") and postwork.dataupd>$date;";
                break;
            case 4:
                $sql = " update post set post.del=0 where post.del=1 and 
                post.postid=$companyid and post.dataupd>$date" . ($city ? " and cityid=$city" : "") . ";";

                $sql .= " update postcalc set postcalc.del=0 where  postcalc.post_id in (select post.row_id postid from post, city where post.del=0 and 
                post.city_id=city.row_id and post.posttype_id=$companyid " . ($city ? " and post.city_id=$city" : "") . ") and  postcalc.dataupd>$date;";

                $sql .= " update postwork set postwork.del=0 where postwork.post_id in (select post.row_id postid from post, city where post.del=0 and 
                    post.city_id=city.row_id and post.posttype_id=$companyid" . ($city ? " and post.city_id=$city" : "") . ") and postwork.dataupd>$date;";
        }

        echo $sql;
        mysqli_query($this->connection, $sql);
    }
}
