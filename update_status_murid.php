<?php
include 'connection.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch the data from the form
    $id_murid = $_POST['id_murid'];
    $status_murid = $_POST['status_murid'];

    // Update the status in the database
    $query = "UPDATE master_murid SET status_murid = ? WHERE id_murid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status_murid, $id_murid);
    $stmt->execute();
    $stmt->close();

    // update_status_murid.php
    include 'connection.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_murid = $_POST['id_murid'] ?? '';
        $status_murid = $_POST['status_murid'] ?? '';

    // Konversi status ke ID status_murid dari tabel (misalnya 1 = Aktif, 2 = Tidak Aktif)
    $statusMap = ['Aktif' => 1, 'Tidak Aktif' => 2];
    $id_status = $statusMap[$status_murid] ?? null;

    if ($id_murid && $id_status) {
        $stmt = $conn->prepare("UPDATE master_murid SET status_murid = ? WHERE id_murid = ?");
        $stmt->bind_param("is", $id_status, $id_murid);
        if ($stmt->execute()) {
            echo "<script>window.location.href = 'master_murid.php';</script>"; // Auto-refresh
        } else {
            echo "Gagal update data.";
        }
        $stmt->close();
    }
}

    // Redirect back to the master_murid page after updating
    header("Location: master_murid.php");
    exit();
}
?>
