<?php


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

try {
    $sql = "SELECT * FROM feedback";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Tablosu</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .email-link {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Feedback Tablosu</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Konu</th>
            <th>Tarih</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // $feedbacks tanımlanmış ve boş değilse işlem yap
        if(isset($feedbacks) && is_array($feedbacks) && !empty($feedbacks)): 
            foreach ($feedbacks as $feedback): ?>
            <tr>
                <td><?php echo $feedback['id']; ?></td>
                <td><?php echo $feedback['adsoyad']; ?></td>
                <td><?php echo $feedback['telefon']; ?></td>
                <td><span class="email-link" onclick="sendEmail('<?php echo $feedback['email']; ?>')"><?php echo $feedback['email']; ?></span></td>
                <td><?php echo $feedback['konu']; ?></td>
                <td><?php echo $feedback['tarih']; ?></td>
            </tr>
        <?php endforeach; 
        else: ?>
            <tr>
                <td colspan="6">Geribildirim bulunamadı.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function sendEmail(email) {
    window.location.href = "mailto:" + email;
}
</script>

</body>
</html>

