<?php
session_start();

// Oturum kontrolü yapılır
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit; // Yönlendirme sonrası kodun devam etmemesi için exit kullanılır
}

$username = $_SESSION['username'];
$database = "";
$host = "localhost";
$usernamee = "";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $usernamee, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

try {
    $sql = "SELECT DISTINCT sirket FROM form";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sirketler = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datetime = $_POST['date'];
    $sirket = $_POST['sirket'];
    if ($sirket == "other") {
        $sirket = $_POST['otherSirket'];
    }
    $konu = $_POST['konu'];
    $mesaj = $_POST['mesaj'];

    // Kullanıcı adını da form verisi olarak ekliyoruz
    $sql = "INSERT INTO form (date, sirket, konu, mesaj, user_id) VALUES (:date, :sirket, :konu, :mesaj, :user_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':date', $datetime, PDO::PARAM_STR);
    $stmt->bindParam(':sirket', $sirket, PDO::PARAM_STR);
    $stmt->bindParam(':konu', $konu, PDO::PARAM_STR);
    $stmt->bindParam(':mesaj', $mesaj, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $username, PDO::PARAM_STR);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Import Sayfası</title>
</head>
<body>
    <h2>Import Sayfası</h2>
    <p>Giriş yapan kullanıcı: <?php echo htmlspecialchars($username); ?></p>
    
    <div style="float: left; width: 200px; padding-right: 20px;">
        <h3>Yan Menü</h3>
        <ul>
            <li><a href="home.php">Eklenen Veriler (aktif)</a></li>
            <li><a href="stok_takibi.php">Stok Takibi (pasif)</a></li>
            <li><a href="user_mang.php">Kullanıcı Denetimi (pasif)</a></li>
            <li><a href="finance.php">Girdi Çıktı (pasif)</a></li>
            <li><a href="feedback.php">Feedback (aktif)</a></li>
        </ul>
    </div>

    <div style="margin-left: 220px;">
        <h2>Form</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="datetime">Tarih ve Saat:</label><br>
            <input type="datetime-local" id="date" name="date" value="<?php echo date('Y-m-d\TH:i'); ?>" required><br>
            <label for="sirket">Şirket:</label><br>
            <select id="sirket" name="sirket" required onchange="showOther()">
                <?php foreach ($sirketler as $sirket): ?>
                    <option value="<?php echo htmlspecialchars($sirket); ?>"><?php echo htmlspecialchars($sirket); ?></option>
                <?php endforeach; ?>
                <option value="other">Diğer</option>
            </select><br>
            <div id="otherDiv" style="display: none;">
                <label for="otherSirket">Şirket Adı:</label><br>
                <input type="text" id="otherSirket" name="otherSirket"><br>
            </div>
            <label for="konu">Konu:</label><br>
            <input type="text" id="konu" name="konu" required><br>
            <label for="mesaj">Mesaj:</label><br>
            <textarea id="mesaj" name="mesaj" rows="4" cols="50" required></textarea><br><br>
            <input type="submit" value="Gönder">
        </form>
    </div>

    <script type="text/javascript">
        function showOther() {
            var sirketSelect = document.getElementById("sirket");
            var otherDiv = document.getElementById("otherDiv");
            var otherSirketInput = document.getElementById("otherSirket");

            if (sirketSelect.value === "other") {
                otherDiv.style.display = "block";
                otherSirketInput.setAttribute("required", true);
            } else {
                otherDiv.style.display = "none";
                otherSirketInput.removeAttribute("required");
            }
        }
    </script>
</body>
</html>
