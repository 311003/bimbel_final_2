<?php
include 'connection.php';
session_start();

if (isset($_GET['id_user']) && is_numeric($_GET['id_user'])) {
    $id_user = $_GET['id_user'];

    // Ambil status aktif saat ini
    $query = "SELECT is_active FROM tm_user WHERE id_user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $currentStatus = $user['is_active'];
        $newStatus = ($currentStatus == 1) ? 0 : 1;

        // Update status
        $update = $conn->prepare("UPDATE tm_user SET is_active = ? WHERE id_user = ?");
        $update->bind_param("ii", $newStatus, $id_user);
        if ($update->execute()) {
            $_SESSION['message'] = "Status user berhasil diperbarui.";
        } else {
            $_SESSION['message'] = "Gagal memperbarui status user.";
        }
        $update->close();
    }
    $stmt->close();
}

header("Location: list_user.php");
exit;
