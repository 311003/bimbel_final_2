<?php

class Cashflow{
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }
    public function add($tipe,$tanggal,$keterangan,$nominal,$table,$id_ref){
        $debet = $kredit = 0;

        if ($tipe == 'Pemasukan') {
            $debet = $nominal;
        } else {
            $kredit = $nominal;
        }

        $query = "INSERT INTO cashflow (tipe, keterangan, tanggal, debet, kredit,table_name,id_ref) VALUES (?, ?, ?, ?, ?,?,?)";
        $stmt =  $this->conn->prepare($query);
        $stmt->bind_param("sssddss", $tipe, $keterangan, $tanggal, $debet, $kredit,$table,$id_ref);
        $stmt->execute();
    }

     public function remove($table,$id_ref){
        $query = "DELETE FROM cashflow WHERE table_name=? AND id_ref=?";
        $stmt =  $this->conn->prepare($query);
        $stmt->bind_param("ss", $table,$id_ref);
        $stmt->execute();
    }
}