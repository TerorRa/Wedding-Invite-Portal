
<!DOCTYPE html>
<html>
	<meta http-equiv="Refresh" charset ="UTF-8" /> 
	<title>Сторінка 404</title>
	<head>
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"> 
		<link rel="stylesheet" href="/css/style_kpp.css" type="text/css" >
	</head>	
	<body style = " background: url(/image/studio.jpg); 
					background-size: cover;       /* розтягує на весь екран */
					background-position: center;  /* центрує */
					background-repeat: no-repeat; /* без повтору */
					height: 100vh;
					margin: 0;">

		
		<div id="loginBox" >
			<p style="color: #00729E;font-size:16px;">Схоже, що запитана Вами сторінка - не знайдена, на сервері!</p>

			<form action="welcome.php" method="POST">
				<button  style="background: none; 
								border: none;
								color: blue;
								text-decoration: underline;
								cursor: pointer;
								padding: 0;" 
						type="submit"   name="do_welcome" >Перейти до головноЇ сторінки</button>
			</form>
		</div>


	</body>
	
<?php
$data_w= $_POST;
if( isset ($data_w["do_welcome"]))
    {	
	header('Location: /welcome.php');
	exit;
	}		
?>
	


