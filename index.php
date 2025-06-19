<?php
session_start(); // Start the session

require __DIR__ . '/src/PHPMailer.php';
require __DIR__ . '/src/SMTP.php';
require __DIR__ . '/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = ""; //Database ismi
$host = "localhost";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", "", ""); //db ismi ve şifreisni giriniz iki tırnak arasına
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to get user IP address
    function getUserIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    // Function to add log
    function addLog($conn, $username, $action, $ip_address, $user_agent) {
        $stmt = $conn->prepare("INSERT INTO logs (username, action, ip_address, user_agent) VALUES (:username, :action, :ip_address, :user_agent)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
        $stmt->execute();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['login'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $ip_address = getUserIpAddr();
            $user_agent = $_SERVER['HTTP_USER_AGENT'];

            $sql = "SELECT id, email FROM user WHERE username = :username AND password = :password";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $user['email'];

                addLog($conn, $username, "login_success", $ip_address, $user_agent);

                $verificationCode = rand(100000, 999999);
                $_SESSION['verification_code'] = $verificationCode;
                $_SESSION['username'] = $username; // Store username in session

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = ''; //mail sunucusu
                $mail->SMTPAuth = true;
                $mail->Username = ''; //mail
                $mail->Password = ''; //mail şifresi
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false; 
                $mail->Port = 587; //portu ssl tsl mevcutsa değiştiriniz

                try {
                    $mail->setFrom('', ''); //Mail adresi kullanıcı adı , Mail ismi
                    $mail->addAddress($email);
                    $mail->Subject = 'Doğrulama Kodu';
                    $mail->Body = "Giriş yapmak için doğrulama kodunuz: $verificationCode"; //Doğrulama kodu text i

                    $mail->send();
                    echo 'Doğrulama kodu e-posta ile başarıyla gönderildi!';
                } catch (Exception $e) {
                    echo 'E-posta gönderimi başarısız: ' . $mail->ErrorInfo;
                }
            } else {
                addLog($conn, $username, "login_failed", $ip_address, $user_agent);
                echo "Geçersiz kullanıcı adı veya şifre.";
            }
        } elseif (isset($_POST['verify'])) {
            $verificationCode = $_POST['verification_code'];

            if (isset($_SESSION['verification_code']) && $_SESSION['verification_code'] == $verificationCode) {
                header("Location: import.php");
                exit();
            } else {
                echo "Geçersiz doğrulama kodu.";
            }
        }
    }
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Giriş Formu</title>
</head>
<body>
    <h2>Giriş Yap</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="username">Kullanıcı Adı:</label><br>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Şifre:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Giriş Yap" name="login">
    </form>

    <?php if(isset($_SESSION['verification_code'])) { ?>
        <h2>Doğrulama Kodu</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="verification_code">Doğrulama Kodu:</label><br>
            <input type="text" id="verification_code" name="verification_code" required><br><br>
            <input type="submit" value="Doğrula" name="verify">
        </form>
    <?php } ?>
</body>
</html>
