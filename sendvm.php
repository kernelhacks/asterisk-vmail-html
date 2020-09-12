#!/usr/bin/php -q
<?php
// use PHPMailer\PHPMailer\PHPMailer;
date_default_timezone_set('America/New_York');
require '/usr/local/lib/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
// require("/home/site/libs/PHPMailer-master/src/PHPMailer.php");
// require("/home/site/libs/PHPMailer-master/src/SMTP.php");
//************** obtenemos el stdin del asterisk *************************************/
$in = fopen('php://stdin', 'r');
while(!feof($in)){
 $text = $text . fgets($in, 4096);
}

//************* guardamos la salida a un archivo temporal ****************************/
$time=time();
$fp = fopen("/etc/asterisk/data_" .$time .".txt", "w");
fwrite($fp, $text);
fclose($fp);

//************ Obtenemos las Lineas que contienen los datos del Buzón
$path = "/etc/asterisk/data_" .$time. ".txt";
$handle = fopen($path, "r");
$lines = file($path); 

//*********** las lineas que usaremos son:
$linenum = 2; //email
$linenum2 = 17; // variables vm
$linenum3 = 19; // numero de mensaje

$toemail=get_between($lines[$linenum],"<",">"); //********* el email
$msg=str_replace('"',"",$lines[$linenum3]); //********* Quitamos las comillas
$msg2=get_between($msg,"=",".wav"); //********* el numero de mensaje
$args=$lines[$linenum2]; //********* argumentos del vm(nombre, buzon, CID etc) 

/*********** Separamos los Valores de Nombre, buzon, Fecha, CID y Duracion *************************/
$vmargs=explode("|",$args);
$name=$vmargs[0];
$mailbox=$vmargs[1];
$dur=$vmargs[2];
$cid=$vmargs[3];
$date=$vmargs[4];


/********** Construimos las Variables a usar para enviar el email **********************************/

// $mail_lib_path = "/usr/local/lib/vendor/phpmailer/phpmailer/"; 
// $from = "micorreo@midominio.com";  /* correo */
// $fromName = "QuienEnvia";                  /* quien lo envia */
// $host = "ssl://smtp.gmail.com";              /* servidor smpt */
// $username = "micorreo@midominio";  /* usuario  */
// $password = "mipassword";                   /* password */
// $port = "465";                                            /* puerto   */
$subject = "[VM]: New Voicemail Message";   /* titulo del correo */


/***************** Este es el Cuerpo HTML,  editar las URLS **************************/
ob_start();
include('index.php');
$body=ob_get_contents();
ob_get_clean();

/*************** Asignamos las varibales al constructor email ******************/
// require($mail_lib_path . "class.phpmailer.php"); 
$mail = new PHPMailer(true);
$mail->isSendmail();
$mail -> IsHTML (true);
// $mail->IsSMTP();
//$mail->SMTPDebug = 1; // Habilita información SMTP (opcional para pruebas)
 // 1 = errores y mensajes
 // 2 = solo mensajes
// $mail->SMTPAuth = false; // Habilita la autenticación SMTP
$mail->setFrom('info@nibbletec.com');
$mail->Subject = $subject;
$mail->Body = $body;
$mail->AddAddress($toemail,$name);
// $mail->FromName = $fromName;
// $mail->Host = $host; 
// $mail->Mailer = "smtp"; 
// $mail->SMTPAuth = true;
// $mail->Username = $username;
// $mail->Password = $password;
// $mail->Port = $port; 
// $headers = "MIME-Version: 1.0" . "\r\n";
// $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// $headers .= 'From: <info@example.com>' . "\r\n";

$mail->AddAttachment("/var/spool/asterisk/voicemail/default/".$mailbox."/INBOX/".$msg2.".wav");



/************* enviar email ******************************/
if($mail->Send())
{
 echo "\r\nSent Ok! \r\n";
} else {
 echo "\r\nSend Failed... \r\n";
 echo $mail->ErrorInfo;
}

/**************** eliminamos el archivo temporal ******************/
unlink("/etc/asterisk/data_" .$time .".txt");

/****************** funcion para obtener una cadena entre dos palabras ****************/
function get_between ($text, $s1, $s2) {
$mid_url = "";
$pos_s = strpos($text,$s1);
$pos_e = strpos($text,$s2);
for ( $i=$pos_s+strlen($s1) ; (( $i<($pos_e)) && $i < strlen($text)) ; $i++ ) {
$mid_url .= $text[$i];
}
return $mid_url;
}

?>