<?php
include 'connection.php';
session_start();

if (isset($_GET['id_user']) && is_numeric($_GET['id_user'])) {
    $id_user = $_GET['id_user'];

    // Ambil status aktif saat ini
    $query = "SELECT is_active,id_ref,role FROM tm_user WHERE id_user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $currentStatus = $user['is_active'];
        $roleUser=$user['role'];
        $idRef=$user['id_ref'];

        $runUpdate=true;
        // validasi jika nonaktif
        if($currentStatus==1 && $roleUser==3){
                $message="";
                //validasi presensi
                $presensi="SELECT COUNT(*) AS total_presensi FROM detail_presensi j WHERE NOT EXISTS(SELECT 1 FROM final_presensi p WHERE p.id_murid=? AND p.id_jadwal =j.id_jadwal) AND j.id_murid=?";
                $selectPresensi= $conn->prepare($presensi);
                $selectPresensi->bind_param('ss',$idRef,$idRef);
                $selectPresensi->execute();
                $selectPresensi->bind_result($total_presensi);
                $selectPresensi->fetch();
                
                if($total_presensi >0){
                    $message .= "masih ada ".$total_presensi." jadwal belum terlaksana.</br>";
                    $runUpdate=false;
                }
                $selectPresensi->close();
                //validasi pembayran
                $pembayaran="SELECT COUNT(*) AS total_tagihan FROM pembayaran WHERE id_murid=? AND sisa_biaya > 0";
                $selectPembayaran= $conn->prepare($pembayaran);
                $selectPembayaran->bind_param('s',$idRef);
                $selectPembayaran->execute();
                $selectPembayaran->bind_result($total_tagihan);
                $selectPembayaran->fetch();
                
                if($total_tagihan >0){
                    $message .= "Gagal memperbaruhi status: ada pembayaran ". $total_tagihan." yang belum Lunas.</br>";
                    $runUpdate=false;
                }
                $selectPembayaran->close();
                $_SESSION['message']=$message;
            
        }

        if($runUpdate){
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
        
    }
    $stmt->close();
}

header("Location: list_user.php");
exit;
