<?php
header("Access-Control-Allow-Origin:*");


if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case "getcopmanies":
            $connection = mysqli_connect("localhost", "root", "pr04ptz3", "nordcom");

            if ($connection) {
                mysqli_query($connection, "set names cp1251");
                $sql = "select `name`,`row_id` from posttype where flauto=1 and name<>\"\"";

                $sql_result = mysqli_query($connection, $sql);
                $res = [];
                if ($sql_result) {
                    while ($sql_row = mysqli_fetch_assoc($sql_result)) {
                        $sql_row['name'] = mb_convert_encoding(
                            $sql_row['name'],
                            'utf-8',
                            'windows-1251'

                        );

                        $res[] = $sql_row;
                    }

                    echo json_encode($res);
                }
            }
            break;
        case "getsessions":


            $connection = mysqli_connect("localhost", "root", "pr04ptz3", "nordcom");

            if ($connection) {
                mysqli_query($connection, "set names cp1251");
                $sql = "select * from postapi_sessions where postid=" . $_GET['company'] . ' order by sessiondate desc limit 20';
                //echo $sql;
                $sql_result = mysqli_query($connection, $sql);
                $res = [];
                if ($sql_result) {
                    while ($sql_row = mysqli_fetch_assoc($sql_result)) {
                        $sql_row['name'] = mb_convert_encoding(
                            $sql_row['name'],
                            'utf-8',
                            'windows-1251'

                        );

                        $res[] = $sql_row;
                    }

                    echo json_encode($res);
                }
            }

            break;

        case "gettags":


            $connection = mysqli_connect("localhost", "root", "pr04ptz3", "nordcom");

            if ($connection) {
                mysqli_query($connection, "set names cp1251");
                $sql = "SELECT distinct pc.context as context, pc.id as id  FROM nordcom.postapi_log as pl inner join postapi_context as pc on pl.logcontext=pc.id";

                $sql = "select count(*) as errcont ,ct.context, ct.id, pl.logsession  from postapi_log as pl inner join (SELECT distinct pc.context as context, pc.id as id  FROM nordcom.postapi_log as pl inner join postapi_context as pc on pl.logcontext=pc.id) as ct on ct.id=pl.logcontext where logsession=" . $_GET['session'] . " group by ct.id";
               // echo $sql;
                $sql_result = mysqli_query($connection, $sql);
                $res = [];
                if ($sql_result) {
                    while ($sql_row = mysqli_fetch_assoc($sql_result)) {
                        $sql_row['context'] = mb_convert_encoding(
                            $sql_row['context'],
                            'utf-8',
                            'windows-1251'

                        );

                        $res[] = $sql_row;
                    }

                    echo json_encode($res);
                }
            }

            break;
        case "getlogs":


            $connection = mysqli_connect("localhost", "root", "pr04ptz3", "nordcom");

            if ($connection) {
                mysqli_query($connection, "set names cp1251");


                $sql = "SELECT * FROM nordcom.postapi_log where logsession=\""   . $_GET['session'] .   "\""  . ((isset($_GET['tag']) && ($_GET['tag'] != 0)) ? "  and logcontext=\"" . $_GET['tag'] . "\"" : "");


                //  echo $sql;
                $sql_result = mysqli_query($connection, $sql);
                $res = [];
                if ($sql_result) {
                    while ($sql_row = mysqli_fetch_assoc($sql_result)) {
                        $sql_row['logtext'] = mb_convert_encoding(
                            $sql_row['logtext'],
                            'utf-8',
                            'windows-1251'

                        );

                        $res[] = $sql_row;
                    }

                    echo json_encode($res);
                }
            }

            break;
    }
}
