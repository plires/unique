<?php 
//Confeccionamos el HTML con los datos del usuario
$body='
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="es_ar">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title>Unique</title>

  <style type="text/css">
  </style>    
</head>
<body style="margin:0; padding:0; background-color:#fff;">
  <center>
    <table bgcolor="#fff" width="95%" border="0" cellpadding="0" cellspacing="0">
      <tr>
           <td height="40" style="font-size:10px; line-height:20px;">&nbsp;</td>
       </tr>
      <tr>
      <tr>
          <td align="center" valign="top">
            <img src="http://unqtalent.com/img/logo-unique.png" style="margin:0; padding:0; border:none; display:block;" border="0" alt="logo" /> 
          </td>
      </tr>
       <tr>
           <td height="40" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>
      <tr>
          <td align="center" valign="top" style="font-size:25px; line-height:35px;"><strong>Unique - '.$post['origin'].'</strong></td>
      </tr>
      <tr>
           <td height="10" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>

      <tr>
          <td align="center" valign="top"><p><strong>Nombre: </strong>'.$post['name'].'</p></td>
      </tr>

      <tr>
           <td height="10" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>

      <tr>
          <td align="center" valign="top"><p><strong>Email: </strong>'.$post['email'].'</p></td>
      </tr>

      <tr>
           <td height="10" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>

      <tr>
          <td align="center" valign="top"><p><strong>Comentarios: </strong>'.$post['comments'].'</p></td>
      </tr>

      <tr>
           <td height="10" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>
      
      <tr>
          <td align="center" valign="top" style="font-size:16px; line-height:20px;"><p><strong>Fecha: </strong>'.date("F j, Y, g:i a").'</p></td>
      </tr>

      <tr>
           <td height="40" style="font-size:10px; line-height:20px;">&nbsp;</td>
      </tr>

    </table>
  </center>
</body>
</html>
';
?>