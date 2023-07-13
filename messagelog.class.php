<?php
include "message.class.php";

class messagelog
{
    private $log = [];
    private $message_types = ['Уведомление', "Предупреждение", "Ошибка", "Критическая ошибка"];
    private $postName, $postId, $displayLog, $txtLog, $baseLog, $level;

    private $LogSession;


    public $lastresponse;
    public $lastcomand;
    public $starttime;


    //Подключение БД

    private $server;
    private $base;
    private $user;
    private $port;
    private $bdpassword;
    public $connection;
    public $statistics;
    public $status;

    public $fileLink = "";

    public function __construct($postId, $postName, $displayLog, $txtLog, $baseLog, $level)
    {
        include('../conf.php');
        if (strpos($sServer, ":") !== false) $sServer = substr($sServer, 0, strpos($sServer, ":"));
        $this->server = $sServer;
        $this->base = $stDB;
        $this->user = $sUser;
        $this->bdpassword = $sPass;
        $this->port = $sPort;

        $this->postName = $postName;
        $this->postId = $postId;
        $this->displayLog = $displayLog;
        $this->txtLog = $txtLog;
        $this->baseLog = $baseLog;
        $this->level = $level;
        $this->starttime = microtime(true);
        $this->status=777;

        if ($baseLog) {
            //  $this->createLogBase($this->checkLogBase());

        }

        $this->LogSession = $this->startBaseSession($postId);
        $this->connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);
    }


    public function __destruct()
    {

        $this->endBaseSession($this->postid, $this->starttime, $this->statistics);
    }




    public function addLog($message_type, $message_postid, $message_context, $message_text, $message_level)
    {
        $message = new message();
        $message->message_date = Date('Y-m-d H:i:s');
        $message->message_type = $message_type;
        $message->message_postid = $message_postid;
        $message->message_context = $message_context;
        $message->message_text = $message_text;
        $message->message_level = $message_level;
        $this->log[] = $message;

        if ($this->baseLog) {
            $this->addLogtoBase($message_type, $message_context, $message_text, $message_level);
        }

        if ($this->txtLog) {

            $this->writeLog("Log.txt", 0);
        }
        $this->writeCurrent($message_text);
    }


    public function  writeCurrent($message_text)
    {

        $fileLink = fopen("current_" . $this->postId . ".txt", 'w+');


        fwrite($fileLink, round((microtime(true) - $this->starttime)) . " сек. " . $message_text . PHP_EOL);

        fclose($fileLink);
    }


    public function displayLog($level)
    {
        $ctr = 0;

        foreach ($this->log as $logItem) {

            if ($logItem->message_level == $level) {
                $ctr++;
                echo  $ctr . " |  "  . $logItem->message_date . " |  "  . $this->message_types[$logItem->message_type] . " |  "  . $logItem->message_context . " |  "  . $logItem->message_text .   "</br></br>";
            }
        }
    }



    //---------------------------------------
    public function writeLog($file, $level)
    {
        /*
        $ctr = 0;

        $this->fileLink = $this->fileLink==""?fopen($file, 'wt'):$this->fileLink;

        foreach ($this->log as $logItem) {

            if ($logItem->message_level >= $level) {
                $ctr++;

               fwrite($this->fileLink, $ctr . " |  "  . $logItem->message_date . " |  "  . $this->message_types[$logItem->message_type] . " |  "  . $logItem->message_context . " |  "  . $logItem->message_text . PHP_EOL);
            }
        };
        fclose($this->fileLink);*/
    }
    //---------------------------------------
    public function totalLog()
    {
        if ($this->displayLog) {
            $this->displayLog($this->level);
        }
        if ($this->txtLog) {
            $this->writeLog("LOG_" . Date('Y-m-d H-i-s') . "_" . $this->postName . ".txt", $this->level); // echo "LOG_".Date('Y-m-d H:i:s')."_".$this->postName;
        }
    }


    //---------------------------------------
    public function checkLogBase()
    {
        $BaseErrors = [];
        //echo "conn_server: ".$this->server." conn_user: ".$this->user." conn_pass: ".$this->bdpassword." conn_base: ".$this->base." conn_port: ".$this->port;
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);

        $sql_result = mysqli_query($connection, "SHOW TABLES FROM `" . $this->base . "` like 'postapi_log';");

        if (mysqli_num_rows($sql_result) == 0) {
            $BaseErrors[] = 1;
        }

        $sql_result = mysqli_query($connection, "SHOW TABLES FROM `" . $this->base . "` like 'postapi_context';");

        if (mysqli_num_rows($sql_result) == 0) {
            $BaseErrors[] = 2;
        }

        $sql_result = mysqli_query($connection, "SHOW TABLES FROM `" . $this->base . "` like 'postapi_sessions';");

        if (mysqli_num_rows($sql_result) == 0) {
            $BaseErrors[] = 3;
        }

        $sql_result = mysqli_query($connection, "SHOW TABLES FROM `" . $this->base . "` like 'postapi_response';");

        if (mysqli_num_rows($sql_result) == 0) {
            $BaseErrors[] = 4;
        }

        return $BaseErrors;
    }


    public function startBaseSession($PostId, $SessionName = "", $starttype = 3)
    {

        if ($PostId == $_GET['companyid'] && $_GET['action'] != 6) {
            $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);


            $starttype = $_GET["starttype"];

            mysqli_query($connection, "SET NAMES cp1251");

            $SessionName = mb_convert_encoding($SessionName, 'windows-1251', 'utf-8');


            $sql = "INSERT INTO `postapi_sessions` (`postid`, `sessionname`, startdate,starttype, status) VALUES ('" . $PostId . "', '" . $SessionName .  "', '" . date("Y-m-d H:i:s") . "','$starttype',1)";
            $sql_result = mysqli_query($connection, $sql);

            return mysqli_insert_id($connection);
        }
        return 0;
    }


    public function endBaseSession($PostId, $Start = "", $statistics = "")
    {
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);

        mysqli_query($connection, "SET NAMES cp1251");



        echo $sql = "update`postapi_sessions` set  enddate='" . date("Y-m-d H:i:s") . "',status=$this->status, statistics=\"$statistics\" where id=$this->LogSession";


        $sql_result = mysqli_query($connection, $sql);

        return mysqli_insert_id($connection);
    }


    public function addLogtoBase($message_type, $message_context, $message_text, $message_level)
    {





        mysqli_query($this->connection, "SET NAMES cp1251");
        if ($this->connection) {

            $message_text = mb_convert_encoding($message_text, 'windows-1251', 'utf-8');


            $sql = "INSERT INTO `postapi_log` (`logsession`, `logtype`, `logcontext`, `logtext`, `loglevel`) VALUES ('" . ($this->LogSession == "" ? "0" : $this->LogSession) . "', '" . $message_type . "', '" . ($this->getContextId($message_context) == "" ? "0" : $this->getContextId($message_context)) . "', '" . $message_text . "', '" . $message_level . "')";
            $sql_result = mysqli_query($this->connection, $sql);
            return $sql_result;
        } else {
            $this->connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);
            return 0;
        }
    }

    public function getContextId($context)
    {
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);
        if ($connection) {
            mysqli_query($connection, "SET NAMES cp1251");

            $context = mb_convert_encoding($context, 'windows-1251', 'utf-8');

            $sql = "select * from `postapi_context` where context='" . $context . "'";
            $sql_result = mysqli_query($connection, $sql);

            if (mysqli_num_rows($sql_result) == 0) {


                $sql = "insert into `postapi_context` (`context`) VALUES ('" . $context . "')";
                $sql_result = mysqli_query($connection, $sql);
                echo mysqli_error($connection);
                return mysqli_insert_id($connection);
            } else {
                $sql_row = mysqli_fetch_array($sql_result);
                return $sql_row['id'];
            }
        }
    }


    public function createLogBase($BaseErrors)
    {
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);
        if ($connection) {
            $sql_result = "";
            foreach ($BaseErrors as $BaseError) {
                switch ($BaseError) {
                    case 1:

                        $sql = "CREATE TABLE `nordcom`.`postapi_log` ( `id` INT NOT NULL AUTO_INCREMENT , `logsession` INT NOT NULL DEFAULT '0',  `logtype` INT NOT NULL DEFAULT '0' , `logcontext` VARCHAR(255) NOT NULL DEFAULT '' , `logtext` VARCHAR(512) NOT NULL DEFAULT '' , `loglevel` INT NOT NULL DEFAULT '0' , PRIMARY KEY (`id`))";
                        $sql_result = mysqli_query($connection, $sql);
                        break;
                    case 2:

                        $sql = "CREATE TABLE `nordcom`.`postapi_context` ( `id` INT NOT NULL AUTO_INCREMENT , `context` VARCHAR(255) NOT NULL DEFAULT '' , PRIMARY KEY (`id`))";
                        $sql_result = mysqli_query($connection, $sql);
                        break;
                    case 3:

                        $sql = "CREATE TABLE `nordcom`.`postapi_sessions` ( `id` INT NOT NULL AUTO_INCREMENT , `sessiondate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,`postid` INT NOT NULL DEFAULT '0',   `sessionname` VARCHAR(255) NOT NULL DEFAULT '' , PRIMARY KEY (`id`))";
                        $sql_result = mysqli_query($connection, $sql);
                        break;

                    case 4:

                        $sql = "CREATE TABLE `nordcom`.`postapi_response` ( `id` INT NOT NULL AUTO_INCREMENT ,  `logsession` INT NOT NULL DEFAULT '0',  `command` VARCHAR(512) NOT NULL DEFAULT '' ,  `response` VARCHAR(512) NOT NULL DEFAULT '',  PRIMARY KEY (`id`))";
                        $sql_result = mysqli_query($connection, $sql);
                        break;
                }
            }
        }

        return $sql_result;
    }


    public function getLastAPIstruct()
    {
        $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);

        $sql = "select presponse.logsesion, presponse.command, presponse.commans, session.postid, session.id, presponse.id as pid from `nordcom`.`postapi_response` as presponse,  `nordcom`.`postapi_sessions` as session where session.id=presponse.logsession and session.postid=" . $this->postid . "order by pid desc limit 1";
        $sql_result = mysqli_query($connection, $sql);
        $sql_row = mysqli_fetch_array($sql_result);

        return  $sql_row;
    }

    public function analyzeAPIstruct()
    {
    }

    public function addAPIstruct($command, $newAPI)
    {

        $lastAPIstruct = $this->getLastAPIstruct();


        if ($this->lastresponse != $newAPI || $this->lastcomand != $command) {


            $connection = mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base, $this->port);


            $sql = "insert into `postapi_response` (`logsession`,`command`,`response`) VALUES ('" . $this->logsession . "','" . $command . "','" . APItools::prepareResponseStructure($newAPI) . "')";
            $sql_result = mysqli_query($connection, $sql);

            $this->lastresponse = $newAPI;
            $this->lastcomand = $command;
        }
    }


    function getStarttype()
    {
       // if (php_sapi_name() == 'cli') {
            if (isset($_SERVER['TERM'])) {
                //echo "The script was run from a manual invocation on a shell";   
                return 1;
            } else {
                //echo "The script was run from the crontab entry";   
                return 2;
            }
        //} else {
            return php_sapi_name();
            //echo "The script was run from a webserver, or something else";   
        //}
    }
}
