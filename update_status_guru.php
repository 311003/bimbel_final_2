<?php
include 'connection.php';
header("Content-Type: text/plain"); // Pastikan respons dalam format teks biasa

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_guru = isset($_POST['id_guru']) ? intval($_POST['id_guru']) : 0;
    $id_status_guru = isset($_POST['id_status_guru']) ? intval($_POST['id_status_guru']) : 0;

    //validasi jika akan di nonaktifkan
    if($id_status_guru==2){
        //validasi presensi
        $presensi="SELECT COUNT(*) AS total_presensi FROM jadwal j WHERE NOT EXISTS(SELECT 1 FROM presensi p WHERE p.id_guru=? AND p.id_jadwal =j.id_jadwal) AND j.`id_guru`=?";
        $selectPresensi= $conn->prepare($presensi);
        $selectPresensi->bind_param('ss',$id_guru,$id_guru);
        $selectPresensi->execute();
        $selectPresensi->bind_result($total_presensi);
        $selectPresensi->fetch();
        
        if($total_presensi >0){
            echo "masih ada ".$total_presensi." jadwal belum terlaksana";
            exit;
        }
        $selectPresensi->close();
        //validasi gaji
        $pembayaran="SELECT COUNT(*) AS total_tagihan FROM pembayaran_guru WHERE id_guru=? AND sisa_bayar > 0";
        $selectPembayaran= $conn->prepare($pembayaran);
        $selectPembayaran->bind_param('s',$id_guru);
        $selectPembayaran->execute();
        $selectPembayaran->bind_result($total_tagihan);
        $selectPembayaran->fetch();
        
        if($total_tagihan >0){
            echo "masih ada ".$total_tagihan." tagihan belum Lunas ";
            exit;
        }
        $selectPembayaran->close();

        
    }
    

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
