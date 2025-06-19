<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit; // Yönlendirme sonrası kodun devam etmemesi için exit kullanılır
}


$database = ""; 
$host = "localhost";
$username = ""; 
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    if ($id === null) {
        echo "ID belirtilmedi";
        exit();
    }
} else {
    echo "Form post edilmedi";
    exit();
}


try {
    $sql = "SELECT DISTINCT yetki FROM user";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sirketler = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

$sql = "SELECT * FROM user WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$veri = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sirket = $_POST['username'];
    $konu = $_POST['email'];
    $mesaj = $_POST['password'];
    $date = $_POST['yetki']; 
    
    $sql_update = "UPDATE user SET username = :username, email = :email, password = :password, yetki = :yetki  WHERE id = :id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':username', $sirket); // Değişiklik burada, :sirket -> :username
    $stmt_update->bindParam(':email', $konu);
    $stmt_update->bindParam(':password', $mesaj);
    $stmt_update->bindParam(':yetki', $date); // Değişiklik burada, :datetime -> :date
    $stmt_update->bindParam(':id', $id);
    $stmt_update->execute();

    header("Location: user_mang.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Veri Düzenle</title>
</head>
<body>
    <h2>Veri Düzenle</h2>
    <div style="float: left; width: 200px; padding-right: 20px;">
        <h3>Kontrol Paneli</h3>
        <ul>
            <li><a href="home.php">Eklenen Veriler (aktif)</a></li>
            <li><a href="stok_takibi.php">Stok Takibi (pasif)</a></li>
            <li><a href="user_mang.php">Kullanıcı Denetimi (pasif)</a></li>
            <li><a href="finance.php">Girdi Çıktı (pasif)</a></li>
            <li><a href="feedback.php">Feedback (aktif)</a></li>
        </ul>
    </div>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <input type="hidden" name="id" value="<?php echo $veri['id']; ?>">
    <label for="username">Şirket:</label>
    <input type="text" id="username" name="username" value="<?php echo $veri['username']; ?>"><br><br>
    <label for="email">email:</label>
    <input type="text" id="email" name="email" value="<?php echo $veri['email']; ?>"><br><br>
    <label for="password">Şifre:</label><br>
    <textarea id="password" name="password"><?php echo $veri['password']; ?></textarea><br><br>
    <label for="yetki">yetki:</label>
    <select id="yetki" name="yetki" required onchange="showOther()">
        <?php foreach ($sirketler as $sirket): ?>
            <option value="<?php echo $sirket; ?>" <?php if($sirket == $veri['yetki']) echo "selected"; ?>><?php echo $sirket; ?></option>
        <?php endforeach; ?>
    </select><br><br>
    <input type="submit" value="Kaydet">
</form>

</body>
</html>
