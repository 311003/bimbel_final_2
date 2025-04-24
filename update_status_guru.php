<?php
include 'connection.php';
header("Content-Type: text/plain"); // Pastikan respons dalam format teks biasa

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_guru = isset($_POST['id_guru']) ? intval($_POST['id_guru']) : 0;
    $id_status_guru = isset($_POST['id_status_guru']) ? intval($_POST['id_status_guru']) : 0;

    if ($id_guru <= 0 || $id_status_guru <= 0) {
        echo "invalid";
        exit;
    }

    $query = "UPDATE guru SET id_status_guru = ? WHERE id_guru = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo "error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("ii", $id_status_guru, $id_guru);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
}
?>
