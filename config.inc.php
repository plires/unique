<?php

////////////////////////
///Valores de DB Local
////////////////////////
// define('DSN', 'mysql:host=localhost;dbname=lc_unique;charset=utf8;port:3306');
// define('DB_USER', 'root');
// define('DB_PASS', 'root');

////////////////////////
///Valores de DB Remoto
////////////////////////
define('DSN', 'mysql:host=localhost;dbname=lc_unique;charset=utf8;port:3306');
define('DB_USER', 'plires');
define('DB_PASS', 'Perezzs$7478');

//////////////////////////////
///Valores de Envio de emails
//////////////////////////////
define('SMTP', 'smtp.gmail.com'); // nuevo Valor para gmail
define('EMAIL_SENDER', 'info@unqtalent.com');
define('EMAIL_SENDER_SHOW', 'info@unqtalent.com');
define('NAME_SENDER_SHOW', 'Unique - Talent Solutions');

define('EMAIL_RECIPIENT', 'info@unqtalent.com');
define('EMAIL_BCC', '');
define('EMAIL_PASS', 'etpsfbxmwzprazzl'); // contraseña nueva desde GSUIT : etps fbxm wzpr azzl

define('EMAIL_PORT', 587);
define('EMAIL_NAME', 'Unique - Talent Solutions');
define('EMAIL_SUBJECT', 'Gracias por tu contacto');
define('EMAIL_CHARSET', 'utf-8');
define('EMAIL_ENCODING', 'quoted­printable');

////////////////////////
///API KEY RECAPTCHA
////////////////////////
define("RECAPTCHA_PUBLIC_KEY", "6LcAMBgaAAAAAEdpaJsho-nnUmGH-jA_rAiKFAnQ");
define("RECAPTCHA_SECRET_KEY", "6LcAMBgaAAAAAGajf3wkmqEiyA6DMq_gS-rXDT6x");
