<?php 

//preparamos el mensaje de confirmacion que le enviaremos al remitente.
$body = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title>Pass Reset</title>

  <style type="text/css">
  </style>    
</head>
<body style="margin:0; padding:0; background-color:#fff;">
  <center>
    <table bgcolor="#fff" width="95%" border="0" cellpadding="0" cellspacing="0">
      <tr>
           <td height="40" style="font-size:10px; line-height:10px;">&nbsp;</td>
       </tr>
     
      </tr>
       <tr>
           <td height="40" style="font-size:10px; line-height:10px;">&nbsp;</td>
       </tr>
      <tr>
          <td align="center" valign="top">
            <h1>Hola, aca esta tu nueva contraseña para poder ingresar a tu backend!</h1> <br>
            <h2>
                Una vez ingresado podes cambiarla por la que vos quieras :)
            </h2>

            <h3>
                <strong>Nueva contraseña: </strong> '.$passNew.'
            </h3>

          </td>
      </tr>
      
      <tr>
           <td height="40" style="font-size:10px; line-height:10px;">&nbsp;</td>
       </tr>

    </table>
  </center>
</body>
</html>
';

?>