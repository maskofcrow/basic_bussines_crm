<?php
session_start();

// Veritabanı bağlantısı
$database = "u7674962_estacrmm"; // Veritabanı adı
$host = "localhost";
$username = "u7674962_estabilisim"; // Veritabanı kullanıcı adı
$password = "+sJ&%a7v*=N{"; // Veritabanı şifresi

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

// ID'nin alınması
if (!isset($_GET['id'])) {
    echo "ID belirtilmedi";
    exit();
}

$id = $_GET['id'];

// Veritabanından verinin alınması
$sql = "SELECT * FROM form WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$veri = $stmt->fetch(PDO::FETCH_ASSOC);

// Form gönderildiğinde güncelleme işleminin yapılması
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Diğer alanlardan verilerin alınması
    $sirket = $_POST['sirket'];
    $konu = $_POST['konu'];
    $mesaj = $_POST['mesaj'];
    $date = $_POST['date']; // Yeni tarih
    $time = $_POST['time']; // Yeni saat

    // Tarih ve saat bilgisini birleştirme
    $datetime = $date . ' ' . $time;

    // Veritabanında güncelleme işlemi
    $sql_update = "UPDATE form SET sirket = :sirket, konu = :konu, mesaj = :mesaj, date = :datetime WHERE id = :id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':sirket', $sirket);
    $stmt_update->bindParam(':konu', $konu);
    $stmt_update->bindParam(':mesaj', $mesaj);
    $stmt_update->bindParam(':datetime', $datetime);
    $stmt_update->bindParam(':id', $id);
    $stmt_update->execute();

    // Ana sayfaya yönlendirme
    header("Location: home.php");
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
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $veri['id']; ?>">
        <label for="sirket">Şirket:</label>
        <input type="text" id="sirket" name="sirket" value="<?php echo $veri['sirket']; ?>"><br><br>
        <label for="konu">Konu:</label>
        <input type="text" id="konu" name="konu" value="<?php echo $veri['konu']; ?>"><br><br>
        <label for="mesaj">Mesaj:</label><br>
        <textarea id="mesaj" name="mesaj"><?php echo $veri['mesaj']; ?></textarea><br><br>
        <label for="date">Tarih:</label>
        <input type="date" id="date" name="date" value="<?php echo $veri['date']; ?>"><br><br>
        <label for="time">Saat:</label>
        <input type="time" id="time" name="time" value="<?php echo date('H:i', strtotime($veri['date'])); ?>"><br><br>
        <input type="submit" value="Kaydet">
    </form>
</body>
</html>
