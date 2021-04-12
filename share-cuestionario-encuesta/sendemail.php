<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
        require 'Exception.php';
        require 'PHPMailer.php';
        require 'SMTP.php';
function sendemail($mail_username,$mail_userpassword,$mail_setFromEmail,$mail_setFromName,$mail_addAddress,$txt_message,$mail_subject, $template){

    $mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0; 
	$mail->isSMTP();                            // Establecer el correo electrónico para utilizar SMTP
	$mail->Host = 'smtp.live.com';             // Especificar el servidor de correo a utilizar  //smtp.live.com //smtp.gmail.com
	$mail->SMTPAuth = true;                     // Habilitar la autenticacion con SMTP
	$mail->Username = $mail_username;       // Correo electronico saliente ejemplo: tucorreo@gmail.com
	$mail->Password = $mail_userpassword;  	// Tu contraseña de gmail
	$mail->SMTPSecure = 'tls';                  // Habilitar encriptacion, `ssl` es aceptada
	$mail->Port = 587;                          // Puerto TCP  para conectarse 
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
	 //Recipients
     $mail->setFrom($mail_setFromEmail);
     $mail->addAddress($mail_addAddress);     //Add a recipient
	
	
	//Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->CharSet = 'UTF-8';
    $mail->Subject = $mail_subject; 
    $mail->Body    = $template;    

    $mail->send();
    echo "<p class='exito'><b>Tus respuestas han sido registradas</b>. Gracias por tu participación. 
    Te enviaremos los resultados al correo: ".$mail_addAddress."<p>";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
?>