<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
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
    echo "Veritabanına bağlanırken hata oluştu: " . $e->getMessage();
    exit();
}

try {
    $stmt = $conn->query("SELECT DISTINCT sirket FROM form");
    $sirketler = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    echo "Şirket isimleri alınırken hata oluştu: " . $e->getMessage();
    exit();
}

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_POST['export'])) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    $dateClause = "";
    if (!empty($start_date) && !empty($end_date)) {
        $dateClause = " AND date BETWEEN '$start_date' AND '$end_date'";
    }

    $sql = "SELECT * FROM form WHERE LOWER(sirket) LIKE LOWER('%$search%') $dateClause";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $veriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'user_id');
    $sheet->setCellValue('B1', 'Şirket');
    $sheet->setCellValue('C1', 'Konu');
    $sheet->setCellValue('D1', 'Mesaj');
    $sheet->setCellValue('E1', 'Tarih');

    $row = 2;
    foreach ($veriler as $veri) {
        $sheet->setCellValue('A' . $row, $veri['user_id']);
        $sheet->setCellValue('B' . $row, $veri['sirket']);
        $sheet->setCellValue('C' . $row, $veri['konu']);
        $sheet->setCellValue('D' . $row, $veri['mesaj']);
        $sheet->setCellValue('E' . $row, $veri['date']);
        $row++;
    }

    $tempFileName = tempnam(sys_get_temp_dir(), 'veriler');
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFileName);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="veriler.xlsx"');
    header('Cache-Control: max-age=0');

    readfile($tempFileName);

    unlink($tempFileName);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h2>Eklenmiş Veriler</h2>
     <p>Giriş yapan kullanıcı: <?php echo htmlspecialchars($username); ?></p>
    <a href="logout.php">Çıkış Yap</a>
    
    <form method="GET" action="">
        <label for="search">Şirket Seç:</label>
        <select id="search" name="search" onchange="updateDateFilter()">
            <option value="">Tüm Şirketler</option>
            <?php foreach ($sirketler as $sirket): ?>
                <option value="<?php echo $sirket; ?>" <?php echo isset($_GET['search']) && $_GET['search'] == $sirket ? 'selected' : ''; ?>><?php echo $sirket; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filtrele">
    </form>

    <form method="GET" action="">
        <label for="start_date">Başlangıç Tarihi:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">

        <label for="end_date">Bitiş Tarihi:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">

        <input type="submit" value="Tarih Filtrele">
    </form>

    <form method="post">
        <input type="submit" name="export" value="Excel'e Aktar">
    </form>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Kullanıcı Adı</th>
            <th>Şirket</th>
            <th>Konu</th>
            <th>Mesaj</th>
            <th>Tarih</th>
            <th>İşlemler</th>
        </tr>
        <?php 
        $whereClause = "";
        if(isset($_GET['search'])) {
            $search = $_GET['search'];
            if (!empty($search)) {
                $whereClause .= "LOWER(sirket) LIKE LOWER('%$search%')";
            }
        }
        $dateClause = "";
        if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            if (!empty($start_date) && !empty($end_date)) {
                $dateClause .= "date BETWEEN '$start_date' AND '$end_date'";
            }
        }

        if (!empty($whereClause) && !empty($dateClause)) {
            $whereClause .= " AND ";
        }

        $sql = "SELECT * FROM form";
        if (!empty($whereClause) || !empty($dateClause)) {
            $sql .= " WHERE $whereClause $dateClause";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $veriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($veriler as $veri): ?>
            <tr>
                <td><?php echo $veri['id']; ?></td>
                <td><?php echo $veri['user_id']; ?></td>
                <td><?php echo $veri['sirket']; ?></td>
                <td><?php echo $veri['konu']; ?></td>
                <td><?php echo $veri['mesaj']; ?></td>
                <td><?php echo $veri['date']; ?></td>
                <td><a href="edit.php?id=<?php echo $veri['id']; ?>">Düzenle</a></td> 
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
function updateDateFilter() {
    var selectedCompany = document.getElementById("search").value;

    var startDateInput = document.getElementById("start_date");
    var endDateInput = document.getElementById("end_date");

    var startDate = startDateInput.value;
    var endDate = endDateInput.value;

    var url = window.location.href;

    var newUrl = url.split('?')[0]; 
    var params = [];

    if (selectedCompany) {
        params.push("search=" + encodeURIComponent(selectedCompany));
    }

    if (startDate) {
        params.push("serach" + "start_date=" + startDate);
    }
    if (endDate) {
        params.push("end_date=" + endDate);
    }

    var searchParam = getParameterByName('search', url);
    if (searchParam) {
        params.push("search=" + encodeURIComponent(searchParam));
    }

    if (params.length > 0) {
        newUrl += "?" + params.join("&");
    }

    window.location.replace(newUrl);
}

function getParameterByName(name, url) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

    </script>
    <a href="import.php">Veri eklemek için tıklayınız.</a>
</body>
</html>


