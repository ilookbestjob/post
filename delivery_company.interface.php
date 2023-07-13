<?php

interface delivery_company_template
{

    public function save_terminalsdata($city_id = null);
    public function save_deliveryprices($city_id = null);
    public function save_terminalsdata_try($city_id = null);
    public function save_deliveryprices_try($city_id = null);
    public function save_city_try();




    public function __construct(array $weights, array $volumes, $postid, $priceslimit, $logdetalization, array $logflags,  $logo = null, $graph = true);
}

class delivery_company implements delivery_company_template
{
    private $dpd;
    private $messagelog;
    private $detalization;
    private $postid;
    private $weights;
    private $delivery = "Подключение не задано";
    private $priceslimit;
    private $volumes;
    private $name;
    
    private $starttime;
    private $statistics;
    private $action=0;

    public function __construct(array $weights, array $volumes, $postid, $priceslimit,  $logdetalization, array $logflags, $logo = null, $graph = true)
    {
        echo "<br><br>Экземпляр класса " . __METHOD__ . "/" . __FILE__ . "/" . __CLASS__ . "/" . __TRAIT__ . "<br>";
        $this->postid = $postid;
        $this->detalization = $logdetalization;
        $this->priceslimit = $priceslimit;
        $this->weights = $weights;
        $this->volumes = $volumes;

        $this->dpd = new DPD_service();
        $this->messagelog = new messagelog($this->postid, "", $logflags[0], $logflags[1], $logflags[2], $this->detalization);
        $this->delivery = new Delivery($this->weights, $this->postid, $this->detalization);
    }
public function __destruct()
{

  // if ($this->action!=0) $this->messagelog->endBaseSession($this->postid,$this->starttime,$this->statistics) ;
}

    public function save_terminalsdata($city_id = null)
    {
        // $this->delivery->printlog("Функионал получения терминалов не рализован");
        echo "Функионал получения стоимости доставки не рализован";
    }
    public function save_deliveryprices($city_id = null)
    {
        // $this->delivery->printlog("Функионал получения стоимости доставки не рализован");
        echo "Функионал получения стоимости доставки не рализован";
    }






    public function save_city_try()
    {

        try {
            echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка данных городов</div>";
            $this->save_city();
            echo "</div>";
        } catch (Exception $e) {
            $this->delivery->printlog("Ошибка получения городов: $e");
            echo "err";
            echo "</div>";
        }
    }
    public function save_terminalsdata_try($city_id = null)
    {
        $this->action=2;
        $this->starttime=date("Y-m-d");

        try {

            echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка данных терминалов</div>";
            $this->save_terminalsdata($city_id);
            echo "</div>";
        } catch (Exception $e) {
            $this->delivery->printlog("Ошибка получения терминалов: $e");
            echo "err";
            echo "</div>";
        }
    }
    public function save_deliveryprices_try($city_id = null)
    {
        $this->action=3;
        $this->starttime=date("Y-m-d");
        try {

            echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка стоимости доставки до пункта выдачи </div>";
            $this->save_deliveryprices($city_id);
            echo "</div>";
        } catch (Exception $e) {
            echo "Вывалился на ценах в классе " . __CLASS__;
            $this->delivery->printlog("Ошибка получения стоимости достваки: $e");
            echo "</div>";
        }
    }

    public function save_all()
    {

        $this->save_terminalsdata();
        $this->save_deliveryprices();
    }

    public function save_all_try()
    {
        $this->action=1;
        $this->starttime=date("Y-m-d");
        $this->save_terminalsdata_try();
        $this->save_deliveryprices_try();
    }



    public function log()
    {
        $this->messagelog->totalLog();
    }


    public function lockPost($postid,$action=1)
    {
        $fp = fopen("id_" . $postid . ".txt", 'w+');

      fwrite($fp,$action);
      $this->clearStop($postid);

        if (!$fp) {
            echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Невозможно создать файл блокировки!!!</div>";
        } else {
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка возможна</div>";
                return $fp;
            } else {
                echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Загрузка невозможна, работает другой скрипт</div>";

                exit;
            }
        }

        $setup=json_decode(file_get_contents("post_".$postid.".setup"));
        
         if (!$setup) exit;
         if (isset($setup->loсkauto)){
       if (!$setup->loсkauto){}
       
       exit;
         }


    }

    public function trylockPost($postid)
    {
        $fp = fopen("id_" . $postid . ".txt", 'w+');

        if (!$fp) {
            echo "<div style=\"width:auto;margin:0 150px;margin-bottom:15px;margin-left:20px;font-family:tahoma;color:#aaa;font-size:18px;\">Невозможно создать файл блокировки!!!</div>";
        } else {
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                fclose($fp);
                unlink("id_" . $postid . ".txt");
                return true;
            } else {
                fclose($fp);
                return false;
            }
        }
    }

    public function unlockPost($fp, $postid)
    {
        include('../conf.php');
        if (strpos($sServer, ":") !== false) $sServer = substr($sServer, 0, strpos($sServer, ":"));
        $server = $sServer;
        $base = $stDB;
        $user = $sUser;
        $bdpassword = $sPass;
        $connection = mysqli_connect($server, $user, $bdpassword, $base, $sPort);
        echo "соединение с БД:" . __CLASS__ . "(" . __FILE__ . ")";
        //if ($connection) {
        $sql = "update `const` set `data`='" . date("Y-m-d H:i:s") . "' where `row_id` in(select const_id from posttype where row_id=" . $postid . ")";
        mysqli_query($connection, $sql);
        mysqli_close($connection);
        //}
        flock($fp, LOCK_UN);
        fclose($fp);
        unlink("id_" . $postid . ".txt");
        $this->clearStop($postid);
    }

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


    public function toggleStop($posttype_id)
    {

        //echo $this->checkstop($posttype_id);
      //  if ($this->checkstop($posttype_id)) {

           // if (file_exists('stopall.txt')) {
          /*      $fp = fopen('stopall.txt', 'w+');
                $stopstring = fgets($fp);


                $stops = explode(',', $stopstring);
                $newstops = [];
                foreach ($stops as $stop) {
                    if ($stop != $posttype_id) {
                        $newstops[] = $stop;
                    }
                }

                fwrite($fp, implode(",", $newstops));

                fclose($fp);*/
          //  }
       // } else {

           // if (file_exists('stopall.txt')) {
                $fp = fopen('stopall.txt', 'w+');
                $stopstring = fgets($fp);
                $stopstring .= ",$posttype_id";

                fwrite($fp, $stopstring);
              
                fclose($fp);
                unlink("id_" . $posttype_id . ".txt");
           // }
      //  }
    
    }


    function clearStop($posttype_id){

        $fp = fopen('stopall.txt', 'w+');
        $stopstring = fgets($fp);


        $stops = explode(',', $stopstring);
        $newstops = [];
        foreach ($stops as $stop) {
            if ($stop != $posttype_id) {
                $newstops[] = $stop;
            }
        }

        fwrite($fp, implode(",", $newstops));

        fclose($fp);

    }

    public function buildStatistics($connection, $postid)
    {

      

      $sql = "Select left(pc.dataupd,10) dt,count(*) qt,pc.del FROM nordcom.postcalc pc,nordcom.post p where pc.del=0 and p.row_id=pc.post_id and p.posttype_id=" . $postid . "  group by left(pc.dataupd,10),pc.del order by left(pc.dataupd,10)";



      $sql = 'Select "актуальные" as status,left(pc.dataupd,10) dt,count(*) qt,pc.del 
      FROM nordcom.postcalc pc,
      nordcom.post p where pc.del=0 and p.del=0 and 
      p.row_id=pc.post_id and pc.flkurier<>2 and p.flkurier<>2 and pc.dataupd>CURDATE() - INTERVAL 7 DAY and
      p.posttype_id=' . $postid . ' group by pc.del 
      union
      Select "устаревающие" as status,left(pc.dataupd,10) dt,count(*) qt,pc.del 
      FROM nordcom.postcalc pc,
      nordcom.post p where pc.del=0 and p.del=0 and 
      p.row_id=pc.post_id and pc.flkurier<>2 and p.flkurier<>2 and pc.dataupd>CURDATE() - INTERVAL 31 DAY and pc.dataupd<CURDATE() - INTERVAL 7 DAY and
      p.posttype_id=' . $postid . ' group by pc.del 
      union
      Select "устаревшие" as status,left(pc.dataupd,10) dt,count(*) qt,pc.del 
      FROM nordcom.postcalc pc,
      nordcom.post p where pc.del=0 and p.del=0 and 
      p.row_id=pc.post_id and pc.flkurier<>2 and p.flkurier<>2 and pc.dataupd<=CURDATE() - INTERVAL 31 DAY and
      p.posttype_id=' . $postid . ' group by pc.del';


        $sql_result = mysqli_query($connection, $sql);

        if ($sql_result) {
            $diagdata = "";
            while ($sql_row = mysqli_fetch_array($sql_result)) {

                $diagdata .= "{
                    name: '" . $sql_row['status'] . "',
                    y: " . $sql_row['qt'] . "
                 
                  },";
            }




            $sql = "Select left(pc.dataupd,10) dt,count(*) qt,pc.del FROM nordcom.postcalc pc,nordcom.post p where  p.row_id=pc.post_id and p.posttype_id=" . $postid . "  group by pc.del ";



            $sql_result = mysqli_query($connection, $sql);

            if ($sql_result) {
                $diagdata2 = "";
                while ($sql_row = mysqli_fetch_array($sql_result)) {

                    $diagdata2 .= "{
                        name: '" . ($sql_row['del']==1?"Удалено":"Активные" ). "',
                        y: " . $sql_row['qt'] . "
                     
                      },";
                }
            }




            echo "<div class=\"subheader\">Структура данных</div><div style=\"display:grid;grid-template-columns:1fr 1fr;\"><div class=\"diagcontainer\" id=\"container$postid\"></div><div class=\"diagcontainer\" id=\"container2_$postid\"></div></div>
<script>
Highcharts.chart('container$postid', {
    chart: {
        backgroundColor: null,
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie'
    },
    title: {
      text: '',
      align: 'left'
    },
    tooltip: {
      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
      point: {
        valueSuffix: '%'
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          color:'#000',
          format: '<b>{point.name}</b>: {point.percentage:.1f} %'
        }
      }
    },
    series:[ {
        name: 'Brands',
        colorByPoint: true,
        data: [$diagdata]
    }],
    navigation: {
        buttonOptions: {
          enabled: false
          }
         },credits: {
            enabled: false
        }
  });
  </script>

  <script>
Highcharts.chart('container2_$postid', {
    chart: {
        backgroundColor: null,
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false,
      type: 'pie'
    },
    title: {
      text: '',
      align: 'left'
    },
    tooltip: {
      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
      point: {
        valueSuffix: '%'
      }
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          color:'#000',
          format: '<b>{point.name}</b>: {point.percentage:.1f} %'
        }
      }
    },
    series:[ {
        name: 'Brands',
        colorByPoint: true,
        data: [$diagdata2]
    }]
    ,
    navigation: {
        buttonOptions: {
          enabled: false
          }
         },
         credits: {
            enabled: false
        }
  });
  </script>
  ";
        }
    }


    function buildbraph($connection,$postid,$logo=""){


      echo "<script>checkstates($postid)</script>";

      
        echo "
        <div style=\"width:auto;border:solid 1px #eee;background:#fafafa;border-radius:4px;margin:0 150px;margin-bottom:15px;display:grid;grid-template-columns:200px,1fr;margin-bottom:60px;padding:20px \">
        
        <img src=\"" . $logo . "\" style=\"display:block;height:30px;width:auto;margin-bottom:35px\">";
      
        
        $this->buildStatistics($connection,$postid);
        $this->buildhistory($connection,$postid);
        
        

        echo "<div id=\"buttons_$postid\" style=\"display:flex;align-items:flex-end;justify-content:flex-end;margin-top:40px\">
       ";
      
        if (!file_exists("id_" . $postid . ".txt")){
        echo "<div class=\"button\" onclick=\"sendAction(" . $postid . ", 1);\">Получить все</div>
        <div class=\"button\" onclick=\"sendAction(" . $postid . ", 2);\">Получить терминалы</div>
        <div class=\"button\" onclick=\"sendAction(" . $postid . ", 3);\">Получить стоимость</div>";
        }
        else{
            echo "<div class=\"button\" onclick=\"sendAction(" . $postid . ", 6);\">Остановить</div>";
        }



        echo "</div>
        <div class=\"footer\">
        <div class=\"status\">
        <div class=\"status_header\">Статус</div>
        <div class=\"status_text\" id=\"statustext_$postid\"></div>
        </div>
        <div class=\"current\">
        <div class=\"current_header\">Текущая операция</div>
        <div class=\"current_text\" id=\"curenttext_$postid\"></div>
        </div>
        </div>
        </div>";
       
    }

    

    function buildhistory($connection,$postid,$logo=""){


        echo "<div class=\"subheader\">История обновлений</div><div class=\"container2\" id=\"historycontainer$postid\"></div><div class=\"historyfooter\" id=\"historyfooter_$postid\"><div><input type=\"checkbox\" id=\"showblocked$postid\">скрывать заблокированные сессии</div></div>";

      
       
    }
}
