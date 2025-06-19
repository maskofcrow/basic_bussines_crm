<?php
require __DIR__ . '/src/PHPMailer.php';
require __DIR__ . '/src/SMTP.php';
require __DIR__ . '/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $response = $_POST["response"];
    
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = ''; // SMTP sunucusunun adresi
    $mail->SMTPAuth = true;
    $mail->Username = ''; // SMTP sunucusuna erişim için kullanıcı adı
    $mail->Password = ''; // SMTP şifresi
    $mail->Port = 587;
    
    $mail->setFrom('', ''); //mail adresinin kullanıcı adı ve mail kullanıcısının ismi
    $mail->addAddress($email);
    $mail->Subject = 'Geribildiriminiz Hakkında';
    $mail->Body = $response;

    try {
        $mail->send();
        echo "Yanıtınız başarıyla gönderildi.";
    } catch (Exception $e) {
        echo "Yanıtınız gönderilemedi. Lütfen daha sonra tekrar deneyin.";
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
} else {
    // Eğer dosya doğrudan çağrılırsa bir hata mesajı gönder
    echo "Bu sayfa doğrudan erişilemez.";
}

$mail->SMTPDebug = SMTP::DEBUG_SERVER;

?>
