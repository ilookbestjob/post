<?php header("Access-Control-Allow-Origin:*");
?>
<html>

<head>
	<?

set_time_limit (0);
ini_set('max_execution_time', 0);
ignore_user_abort(true);
ini_set('memory_limit', '2200M');

$server = 'localhost';
$base = 'nordcom';
$user = 'root';
$bdpassword = 'pr04ptz3'; 

$conn=mysqli_connect($server, $user, $bdpassword,$base);
mysqli_query($conn,"set names cp1251");


?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<style type="text/css">
		body {
			font-family: Verdana, Arial;
			font-size: 12px;
			margin: 0px;
			padding: 4px;
		}

		table {
			font-size: 12px;
			border-spacing: 0;
			border-bottom: 1px #ccc solid;
			margin-top: 10px;

		}

		td {

			border-top: 1px #ccc solid;

			padding: 4px;
			vertical-align: center;

		}

		tr:nth-child(2n) {
			border-top: 1px #000 solid;

			background: #f9f9f9;

			padding: 4px;
		}

		.trheader {

			background: #eee linear-gradient(#efefef, #ddd);
		}

		tr:hover {
			background: #f0f0f0;
		}

		tr:hover .spinner::after {
			content: '';
			width: 15px;
			height: 15px;
			background-color: #f0f0f0;
			;
			position: relative;
			display: block;
			left: -5px;
			top: -5px;



		}



		.title {

			margin-top: 20px;
			margin-bottom: 10px;

		}

		.refresh {
			display: inline-block;
			float: right;

			background: forestgreen linear-gradient(rgba(255, 255, 255, 0.3), forestgreen);
			color: #fff;
			font-family: tahoma;
			margin: 3px;
			padding: 5px;
			border-radius: 4px;
			cursor: pointer;

		}

		@keyframes myanimation {
			0% {
				transform: rotate(0deg);
			}



			100% {
				transform: rotate(360deg);

			}
		}


		.spinner {
			border-radius: 50%;
			width: 15px;
			height: 15px;
			border: forestgreen 5px solid;
			display: inline-block;
			animation: myanimation 1s 0s infinite;
			animation-delay: 0s;
			animation-timing-function: linear;

		}

		.spinner::after {
			content: '';
			width: 15px;
			height: 15px;
			background-color: #fff;
			position: relative;
			display: block;
			left: -5px;
			top: -5px;



		}

		.spinnertext {


			display: inline-block;
			margin-left: 10px;

		}

		.datacontainer {
			display: flex;

			align-items: center;
			justify-content: flex-end;
			height: 100%;
			width: 100%;

		}

		.price {
			font-size: 16px;
			font-family: 'Tahoma';

		}

		.price ::after {}

		.rub {
			font-family: arial;
			vertical-align: middle;
			border-bottom: 1px solid;
			line-height: 5px;
			display: inline-block;
			width: 0.4em;
			margin-left: 6px;
			color: #ccc;
		}

		.d {
			font-family: arial;
			vertical-align: middle;
			border-bottom: 1px solid;
			line-height: 5px;
			display: inline-block;
			width: 0.4em;
			margin-left: 6px;
			color: #ccc;
		}

		.info {
			position: fixed;
			left: 0;
			top: 0;
			right: 0;
			background-color: #0568a5;
			height: 60;
			padding: 10px;
			color: #fff;
		}

		.city {
			margin-left: 10px;
			margin-right: 10px;
			margin-bottom: 6px;
			display: inline-block;
		}

		.deliveryinfo {
			display: flex;
			justify-content: flex-end;
		}

		.bestprice {
			background-color: yellowgreen;
			color: #fff;
		}

		.bestprice .spinner::after {
			content: '';
			width: 15px;
			height: 15px;
			background-color: yellowgreen;
			;
			position: relative;
			display: block;
			left: -5px;
			top: -5px;



		}

		.bestprice .rub {

			color: #fff;
		}

		.bestdays {
			background-color: orange;
			color: #fff;
		}

		.bestdays .rub {

			color: #fff;
		}

		.bestdays .spinner::after {
			content: '';
			width: 15px;
			height: 15px;
			background-color: orange;
			;
			position: relative;
			display: block;
			left: -5px;
			top: -5px;



		}

		.legend {
			margin-top: 90px;
			display: flex;
			width: 100%;
			align-items:center;

		}

		.legend__bestpricemapping {
			width: 15px;
			height: 15px;
			background-color: yellowgreen;
			display: inline-block;
		}

		.legend__pestpricetext {
			display: inline-block;
			margin-left: 6px;
		}

		.legend__bestdaysmapping {
			margin-left:10px;
			width: 15px;
			height: 15px;
			background-color: orange;
			display: inline-block;
		}

		.legend__pestdaystext {
			display: inline-block;
			margin-left: 6px;
		}
	</style>

<body>
	<?
$city1=$_GET['city1'];
if ($city1!=112) {echo "Пока не работает.";exit;}
$city2=$_GET['city2'];
$adr1=$_GET['adr1'];
$adr2=$_GET['adr2'];
$ves1=$_GET['ves1'];
$ves2=$_GET['ves2'];
$ves=round($ves1);
if ($ves2>$ves1)$ves=$ves2;
$vol1=$_GET['vol'];
$vol2=$_GET['vol2'];
$vol=$vol1;
if ($vol2>$vol1)$vol=$vol2;
if ($vol==0)$vol="0.1";

$mest1=$_GET['mest'];
$mest2=$_GET['mest2'];
$mest=$mest1;
if ($mest2>$mest1)$mest=$mest2;
if ($mest==0) $mest=1;

$adr1t=$adr1;
$adr2t=$adr2;
$adr1=mb_convert_encoding($adr1,'windows-1251','utf-8');
$adr2=mb_convert_encoding($adr2,'windows-1251','utf-8');

$sql="select * from city where row_id=".intval($city1);
$sql=mysqli_query($conn,$sql);
$sqlg=mysqli_fetch_assoc($sql);


$sql="select * from city where row_id=".intval($city2);
$sql=mysqli_query($conn,$sql);
$g2=mysqli_fetch_assoc($sql);





echo "<script/>";

echo "////Город отправления \r\n"; 

foreach($sqlg as $key=>$var){
echo "const ".$key."_from='".mb_convert_encoding($var,'utf-8','windows-1251')."';\r\n"; 
}


echo "////Город назначения \r\n"; 
foreach($g2 as $key=>$var){
	echo "const ".$key."_to='".mb_convert_encoding($var,'utf-8','windows-1251')."';\r\n"; 
	}

	echo "////Запрос \r\n"; 

	echo "const city='".$_GET['city2']."';\r\n";
	echo "const weight='".$_GET['ves1']."';\r\n";
	echo "const volume='".$_GET['vol']."';\r\n";
	echo "const adr2='".$_GET['adr2']."';\r\n";

echo "</script/>";



?>

	<script>
		let deliveries = [{

				data: {},
				name: "Байкал-Сервис",
				Objname: "BL",
				action: 'getBaikal()'


			}, {

				data: {},
				name: "DPD",
				Objname: "DPD",
				action: 'getDPD()'

			}, {

				data: {},
				name: "Деловые линии",
				Objname: "DL",
				action: 'getDL()'

			}

			, {

				data: {},
				name: "Boxberry",
				Objname: "BB",
				action: 'getBB()'

			}

		];
		/*
				function getBaikal() {

					document.querySelector("#"+deliveries[0].name+'_deliveryprice').innerHTML="<div class=\"spinner\"> Получение...";
					fetch("http://725522.ru/post2/post_api.php?action=1001&companyid=24&city=" + city + "&weight=" + weight + "&volume=" + volume + "&places=0&adr2=" + adr2)
						.then(function(res) { res.json()})
						.then(
							function(result) {
								deliveries[0].data = result;
								update();
							},

							function(error) {
								console.log('er')
							}
						);

				}*/

		function getBaikal() {
			let i = setInterval("timer(deliveries[0]," + (new Date().getTime()) + ")", 100);

			$("#" + deliveries[0].Objname + '_deliverybuttom').html("<div class=\"datacontainer\"><div class=\"spinner\"> </div><div id=\"" + deliveries[0].Objname + "spinnertext\" class=\"spinnertext\"></div></div>");



			$.getJSON("http://725522.ru/post2/post_api.php?action=1001&companyid=24&city=" + city + "&weight=" + weight + "&volume=" + volume + "&places=0&adr2=" + adr2, {},
				function(result) {
					deliveries[0].data = result;

					setTimeout("update(deliveries[0])", 2000);
				}
			);

		}


		function getDPD() {

			let i = setInterval("timer(deliveries[1]," + (new Date().getTime()) + ")", 100);

			$("#" + deliveries[1].Objname + '_deliverybuttom').html("<div class=\"datacontainer\"><div class=\"spinner\"> </div><div id=\"" + deliveries[1].Objname + "spinnertext\" class=\"spinnertext\"></div></div>");

			$.getJSON("http://725522.ru/post2/post_api.php?action=1001&companyid=1&city=" + city + "&weight=" + weight + "&volume=" + volume + "&places=0&adr2=" + adr2, {},
				function(result) {
					deliveries[1].data = result;

					setTimeout("update(deliveries[1])", 2000);
				}
			);

		}


		function getDL() {


			let i = setInterval("timer(deliveries[2]," + (new Date().getTime()) + ")", 100);
			$("#" + deliveries[2].Objname + '_deliverybuttom').html("<div class=\"datacontainer\"><div class=\"spinner\"> </div><div id=\"" + deliveries[2].Objname + "spinnertext\" class=\"spinnertext\"></div></div>");


			$.getJSON("http://725522.ru/post2/post_api.php?action=1001&companyid=3&city=" + city + "&weight=" + weight + "&volume=" + volume + "&places=0&adr2=" + adr2, {},
				function(result) {
					deliveries[2].data = result;

					setTimeout("update(deliveries[2])", 2000);
					clearInterval(i);
				}
			);

		}

		function getBB() {

			let i = setInterval("timer(deliveries[3]," + (new Date().getTime()) + ")", 100);

			$("#" + deliveries[3].Objname + '_deliverybuttom').html("<div class=\"datacontainer\"><div class=\"spinner\"> </div><div id=\"" + deliveries[3].Objname + "spinnertext\" class=\"spinnertext\"></div></div>");

			$.getJSON("http://725522.ru/post2/post_api.php?action=1001&companyid=13&city=" + city + "&weight=" + weight + "&volume=" + volume + "&places=0&adr2=" + adr2, {},
				function(result) {
					deliveries[3].data = result;

					setTimeout("update(deliveries[3])", 2000);
				}
			);

		}

		function timer(obj, milliseconds) {
			let currentMilliseconds = new Date().getTime();
			$("#" + obj.Objname + "spinnertext").html(Math.trunc((currentMilliseconds - milliseconds) / 1000) + "s")
		}

		function build() {


			let table = '<table style="width:560px;" cellpadding="0" cellspacing="0"  valign="center"><tr class="trheader"><td>Перевозчик</td><td>До адреса</td><td></td><td width="20"></td><td>До терминала</td><td></td><td width="20"></td></tr>';

			//<tr class="trheader"><td></td><td>Цена</td><td>Срок</td><td width="20"></td><td>Цена</td><td>Срок</td><td width="20"></td></tr>

			deliveries.forEach(function(item) {
				table = table + '<tr id="' + item.Objname + '_deliverytr"><td id="' + item.Objname + '_deliveryname">' + item.name + '</td>	<td id="' + item.Objname + '_deliveryaddrprice">&nbsp;</td><td id="' + item.Objname + '_deliveryaddrdays"></td><td width="20"></td><td id="' + item.Objname + '_deliveryprice" valign="center">	<div class="datacontainer">' + (item.data.price ? item.data.price : (item.data.price != undefined ? item.data.price : "")) + '</div></td><td id="' + item.Objname + '_deliverydays"></td><td id="' + item.Objname + '_deliverybuttom"><div class="refresh" onclick="' + item.action + ';" >обновить</div></td></tr>'
			})


			document.querySelector("body").innerHTML = "<div class=\"info\"><div><strong>Откуда</strong><div class=\"city\">" + city_from + "</div></div> <div><strong>Куда</strong><div class=\"city\">" + city_to + "</div></div><div class=\"deliveryinfo\"><strong>Вес</strong><div class=\"city\">" + volume + "</div><strong>Объем</strong><div class=\"city\">" + weight + '</div></div></div><div class="legend"><div class="legend__bestprice">					<div class="legend__bestpricemapping"></div><div class="legend__pestpricetext">Лучшая цена</div></div><div class="legend__bestprice"><div class="legend__bestdaysmapping"></div><div class="legend__pestdaystext">Лучший срок</div></div></div> ' + table + '</table>';


		}



		function update(item) {




			$("#" + item.Objname + '_deliveryprice').html("<div class=\"datacontainer\">" + (item.data.price ? ("<div class=\"price\" >" + item.data.price + "<span class=\"rub\">Р</span></div>") : (item.data.price != undefined ? ("<div class=\"price\" >" + item.data.price + "<span class=\"rub\">Р</span></div>") : "")) + '</div>');


			$("#" + item.Objname + '_deliveryaddrprice').html("<div class=\"datacontainer\">" + (item.data.dprice ? ("<div class=\"price\" >" + item.data.dprice + "<span class=\"rub\">Р</span></div>") : (item.data.dprice != undefined ? ("<div class=\"price\" >" + item.data.dprice + "<span class=\"rub\">Р</span></div>") : "")) + '</div>');

			$("#" + item.Objname + '_deliveryaddrdays').html("<div class=\"datacontainer\">" + (item.data.ddays ? ("<div class=\"price\" >" + item.data.ddays + "<span class=\"rub\">д.</span></div>") : (item.data.ddays != undefined ? ("<div class=\"price\" >" + item.data.ddays + "<span class=\"rub\">д.</span></div>") : "")) + '</div>');

			$("#" + item.Objname + '_deliverydays').html("<div class=\"datacontainer\">" + (item.data.days ? ("<div class=\"price\" >" + item.data.days + "<span class=\"rub\">д.</span></div>") : (item.data.days != undefined ? ("<div class=\"price\" >" + item.data.days + "<span class=\"rub\">д.</span></div>") : "")) + '</div>');



			$("#" + item.Objname + '_deliverybuttom').html("<div class=\"refresh\" onclick=\"" + item.action + ";\" >обновить</div>");
			findbestprice();

		}


		function findbestprice() {
			let bestprice = 9999999999999999;
			let bestdays = 9999999999999999;
			let bestobjname = '';
			let bestobjdname = '';

			deliveries.forEach(function(item) {
				if (item.data.price < bestprice) {
					bestprice = item.data.price;
					bestobjname = item.Objname;

				}
				if (item.data.dprice < bestprice) {
					bestprice = item.data.dprice;
					bestobjname = item.Objname;

				}

				if (item.data.days < bestdays) {
					bestdays = item.data.days;
					bestobjdname = item.Objname;

				}
				if (item.data.ddays < bestdays) {
					bestdays = item.data.ddays;
					bestobjdname = item.Objname;

				}


				$("#" + item.Objname + '_deliverytr').removeClass('bestprice')
				$("#" + item.Objname + '_deliverytr').removeClass('bestdays')
			})

			$("#" + bestobjname + '_deliverytr').addClass('bestprice')
			$("#" + bestobjdname + '_deliverytr').addClass('bestdays')

		}

		build()
		getBaikal();
		getDPD();
		getDL();
		getBB();
	</script>
	</head>

	<body>
	</body>

</html>