<?php
require 'vendor/autoload.php';
require 'db_connection.php'; // PDO bağlantısı burada yapılacak

use Ramsey\Uuid\Uuid;

function createShare($calendarData) {
    global $pdo;

    // UUID oluştur
    $uuid = Uuid::uuid4()->getBytes();

    // Veritabanına kaydet
    $stmt = $pdo->prepare('INSERT INTO shares (id, calendar_data) VALUES (:id, :calendar_data)');
    $stmt->execute([
        ':id' => $uuid,
        ':calendar_data' => $calendarData,
    ]);

    return Uuid::fromBytes($uuid)->toString();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calendarData = $_POST['calendar_data'] ?? '';

    if ($calendarData) {
        $shareId = createShare($calendarData);
        echo json_encode('https://www.dreamcalendars.com/SaveCalendars/app/share.php?id=' . $shareId);
    } else {
        echo json_encode('Error: No calendar data provided');
    }
}
?>
