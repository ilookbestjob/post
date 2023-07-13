<?php

include('../conf.php');

$connection = mysqli_connect($sServer, $sUser, $sPass, $stDB, $sPort);

$sql = "select * from postapi_sessions where postid=" . $_GET['postid'] . (isset($_GET["hideblocked"])?" and status<>4":""). " and startdate>CURDATE() - INTERVAL 31 DAY order by startdate desc" ;

if ($connection) {

    mysqli_query($connection,"set names 'cp1251'");   
    $sqlresult = mysqli_query($connection, $sql);
    
    $result = [];
    if ($sqlresult) {
        while ($row=mysqli_fetch_assoc($sqlresult))
       // echo $sql."<br><br><br>" ;
        $result[]=$row;
    }
    else {
        echo '{"err":"Ошибка или пустой результат SQL: $sql"}';
    }
    //print_r($result);
    echo json_encode($result);
}
else{
    echo '{"err":"Ошибка подключения"}';
}
