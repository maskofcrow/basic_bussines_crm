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
    echo "Veritabanına bağlanırken hata oluştu: " . $e->getMessage();
    exit();
}

?>

<html>
    <head>
        <title></title>
    </head>
<body>           
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

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>Mail</th>
            <th>Şifre</th>
            <th>Yetki</th>
            <th>Düzenle</th>
        </tr>
        <?php
          $sql = "SELECT * FROM user";
        if (!empty($whereClause) || !empty($dateClause)) {
            $sql .= " WHERE $whereClause $dateClause";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $veriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($veriler as $veri): ?>
            <tr>
                <td><?php echo $veri['id']; ?></td>
                <td><?php echo $veri['username']; ?></td>
                <td><?php echo $veri['email']; ?></td>
                <td><?php echo $veri['password']; ?></td>
                <td><?php echo $veri['yetki']; ?></td>
                <td><a href="user_edit.php?id=<?php echo $veri['id']; ?>">Düzenle</a></td> 
            </tr>
        <?php endforeach; ?>
    </table>
    
    <h1>Kullanıcı Giriş Ve İşlem Logları</h1>
    
        <table border="1">
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>İşlem</th>
            <th>İp Adresi</th>
            <th>Platform</th>
            <th>Tarih</th>
        </tr>
        <?php
          $sql = "SELECT * FROM logs";
        if (!empty($whereClause) || !empty($dateClause)) {
            $sql .= " WHERE $whereClause $dateClause";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $veriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($veriler as $veri): ?>
            <tr>
                <td><?php echo $veri['id']; ?></td>
                <td><?php echo $veri['username']; ?></td>
                <td><?php echo $veri['action']; ?></td>
                <td><?php echo $veri['ip_address']; ?></td>
                <td><?php echo $veri['user_agent']; ?></td>
                <td><?php echo $veri['timestamp']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
        
</body>
</html>