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

        $id_cashflow=$this->conn->insert_id;

        //update id_cashflow

        $allowanceTable=['bukti_pembayaran','bukti_pembayaran_guru'];
        if(in_array($table,$allowanceTable)){
            $query = "UPDATE `$table` SET id_cashflow= ? WHERE id_bukti=? ";
            $stmt =  $this->conn->prepare($query);
            $stmt->bind_param("ii", $id_cashflow,$id_ref);
            $stmt->execute();
        }
        
    }

     public function remove($table,$id_ref){
        $query = "DELETE FROM cashflow WHERE table_name=? AND id_ref=?";
        $stmt =  $this->conn->prepare($query);
        $stmt->bind_param("ss", $table,$id_ref);
        $stmt->execute();
    }
}