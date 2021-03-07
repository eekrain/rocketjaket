<?php
class Dashboard_model extends CI_Model
{
  public function getListToko()
  {
    $sql = "SELECT id, nama FROM toko WHERE NOT id = 1";
    return $this->db->query($sql)->result_array();
  }

  public function getFirstDateOfRecord()
  {
    $sql = "
      SELECT tanggal
      FROM invoice  
      ORDER BY tanggal
      LIMIT 1
    ";
    return $this->db->query($sql)->result_array();
  }

  public function getLastDateOfRecord()
  {
    $sql = "
      SELECT tanggal
      FROM invoice  
      ORDER BY tanggal DESC
      LIMIT 1
    ";
    return $this->db->query($sql)->result_array();
  }

  //------------- By Month ----------------------------------------------

  public function getProfitByMonth($toko_id, $year, $month)
  {
    $profit = 0;
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_profit_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_profit = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE tanggal = '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE toko_id = '$toko_id' AND tanggal = '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_profit = $query->result_array();

      foreach ($data_profit as $x) {
        $profit += ($x['harga'] - $x['modal'] - $x['diskon']) * $x['qty'];
      }
      $insert_profit = array(
        'tick' => $i,
        'profit' => $profit
      );
      array_push($insert_profit_final, $insert_profit);
      $profit = 0;
    }

    return $insert_profit_final;
  }

  public function getOmsetByMonth($toko_id, $year, $month)
  {
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_omset_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_omset = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE tanggal = '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal = '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_omset = $query->result_array();

      $insert_omset = array(
        'tick' => $i,
        'omset' => $data_omset[0]['totalday']
      );
      array_push($insert_omset_final, $insert_omset);
    }

    return $insert_omset_final;
  }

  public function getTransaksiByMonth($toko_id, $year, $month)
  {
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_transaksi_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_transaksi = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE tanggal = '$check_date'
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal = '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_transaksi = $query->result_array();

      $insert_transaksi = array(
        'tick' => $i,
        'transaksi' => $data_transaksi[0]['transaksi']
      );
      array_push($insert_transaksi_final, $insert_transaksi);
    }

    return $insert_transaksi_final;
  }

  public function getTerjualByMonth($toko_id, $year, $month)
  {
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_terjual_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_terjual = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE tanggal = '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE toko_id = '$toko_id' AND tanggal = '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_terjual = $query->result_array();

      $insert_terjual = array(
        'tick' => $i,
        'terjual' => $data_terjual[0]['terjual']
      );
      array_push($insert_terjual_final, $insert_terjual);
    }

    return $insert_terjual_final;
  }

  public function getPengunjungByMonth($toko_id, $year, $month)
  {
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_pengunjung_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_pengunjung = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE tanggal = '$check_date' AND is_beli=1
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal = '$check_date' AND is_beli=1
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_beli = $query->result_array();

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE tanggal = '$check_date' AND is_beli=0
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal = '$check_date' AND is_beli=0
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_tdk_beli = $query->result_array();

      $insert_pengunjung = array(
        'tick' => $i,
        'beli' => $data_pengunjung_beli[0]['beli'],
        'tdk_beli' => $data_pengunjung_tdk_beli[0]['tdk_beli']
      );
      array_push($insert_pengunjung_final, $insert_pengunjung);
    }

    return $insert_pengunjung_final;
  }

  public function getOperasionalByMonth($toko_id, $year, $month)
  {
    $ref_date = $year . '-' . sprintf("%02d", $month) . '-01';
    $day_end = (int) date('t', strtotime($ref_date));
    $insert_operasional_final = array();
    $sql = "";
    for ($i = 1; $i <= $day_end; $i++) {
      $insert_operasional = array();
      $check_date = $year . '-' . sprintf("%02d", $month) . '-' . sprintf("%02d", $i);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE created_at = '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE toko_id = '$toko_id' AND created_at = '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_operasional = $query->result_array();

      $insert_operasional = array(
        'tick' => $i,
        'operasional' => $data_operasional[0]['totalday']
      );
      array_push($insert_operasional_final, $insert_operasional);
    }

    return $insert_operasional_final;
  }

  //------------- By Year ----------------------------------------------

  public function getProfitByYear($toko_id, $year)
  {
    $profit = 0;
    $insert_profit_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_profit = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE tanggal LIKE '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_profit = $query->result_array();

      foreach ($data_profit as $x) {
        $profit += ($x['harga'] - $x['modal'] - $x['diskon']) * $x['qty'];
      }
      $insert_profit = array(
        'tick' => $i,
        'profit' => $profit
      );
      array_push($insert_profit_final, $insert_profit);
      $profit = 0;
    }

    return $insert_profit_final;
  }

  public function getOmsetByYear($toko_id, $year)
  {
    $insert_omset_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_omset = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE tanggal LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_omset = $query->result_array();

      $insert_omset = array(
        'tick' => $i,
        'omset' => $data_omset[0]['totalday']
      );
      array_push($insert_omset_final, $insert_omset);
    }

    return $insert_omset_final;
  }

  public function getTransaksiByYear($toko_id, $year)
  {
    $insert_transaksi_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_transaksi = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE tanggal LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_transaksi = $query->result_array();

      $insert_transaksi = array(
        'tick' => $i,
        'transaksi' => $data_transaksi[0]['transaksi']
      );
      array_push($insert_transaksi_final, $insert_transaksi);
    }

    return $insert_transaksi_final;
  }

  public function getTerjualByYear($toko_id, $year)
  {
    $insert_terjual_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_terjual = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE tanggal LIKE '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_terjual = $query->result_array();

      $insert_terjual = array(
        'tick' => $i,
        'terjual' => $data_terjual[0]['terjual']
      );
      array_push($insert_terjual_final, $insert_terjual);
    }

    return $insert_terjual_final;
  }

  public function getPengunjungByYear($toko_id, $year)
  {
    $insert_pengunjung_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_pengunjung = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE tanggal LIKE '$check_date' AND is_beli=1
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date' AND is_beli=1
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_beli = $query->result_array();

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE tanggal LIKE '$check_date' AND is_beli=0
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date' AND is_beli=0
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_tdk_beli = $query->result_array();

      $insert_pengunjung = array(
        'tick' => $i,
        'beli' => $data_pengunjung_beli[0]['beli'],
        'tdk_beli' => $data_pengunjung_tdk_beli[0]['tdk_beli']
      );
      array_push($insert_pengunjung_final, $insert_pengunjung);
    }

    return $insert_pengunjung_final;
  }

  public function getOperasionalByYear($toko_id, $year)
  {
    $insert_operasional_final = array();
    $sql = "";
    for ($i = 1; $i <= 12; $i++) {
      $insert_operasional = array();
      $check_date = $year . '-' . sprintf("%02d", $i) . '-%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE created_at LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE toko_id = '$toko_id' AND created_at LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_operasional = $query->result_array();

      $insert_operasional = array(
        'tick' => $i,
        'operasional' => $data_operasional[0]['totalday']
      );
      array_push($insert_operasional_final, $insert_operasional);
    }

    return $insert_operasional_final;
  }

  //--------------- Annually -----------------------------------------

  public function getProfitAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $profit = 0;
    $insert_profit_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_profit = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE tanggal LIKE '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT ii.harga, p.harga_modal as modal, ii.jumlah_diskon as diskon, ii.jumlah_pembelian as qty, i.tanggal
        FROM invoice_items ii
        JOIN produk p
          ON p.SKU = ii.produk_SKU
        JOIN invoice i
          ON i.id = ii.invoice_id
        WHERE invoice_id IN (
            SELECT id
            FROM invoice
            WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_profit = $query->result_array();

      foreach ($data_profit as $x) {
        $profit += ($x['harga'] - $x['modal'] - $x['diskon']) * $x['qty'];
      }
      $insert_profit = array(
        'tick' => $i,
        'profit' => $profit
      );
      array_push($insert_profit_final, $insert_profit);
      $profit = 0;
    }

    return $insert_profit_final;
  }

  public function getOmsetAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $insert_omset_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_omset = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE tanggal LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(total) AS totalday
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_omset = $query->result_array();

      $insert_omset = array(
        'tick' => $i,
        'omset' => $data_omset[0]['totalday']
      );
      array_push($insert_omset_final, $insert_omset);
    }

    return $insert_omset_final;
  }

  public function getTransaksiAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $insert_transaksi_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_transaksi = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE tanggal LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS transaksi
        FROM invoice
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_transaksi = $query->result_array();

      $insert_transaksi = array(
        'tick' => $i,
        'transaksi' => $data_transaksi[0]['transaksi']
      );
      array_push($insert_transaksi_final, $insert_transaksi);
    }

    return $insert_transaksi_final;
  }

  public function getTerjualAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $insert_terjual_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_terjual = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE tanggal LIKE '$check_date'
        )
      ";
      } else {
        $sql = "
        SELECT SUM(jumlah_pembelian) as terjual
        FROM invoice_items
        WHERE invoice_id IN (
          SELECT id
          FROM invoice
          WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date'
        )
      ";
      }
      $query = $this->db->query($sql);
      $data_terjual = $query->result_array();

      $insert_terjual = array(
        'tick' => $i,
        'terjual' => $data_terjual[0]['terjual']
      );
      array_push($insert_terjual_final, $insert_terjual);
    }

    return $insert_terjual_final;
  }

  public function getPengunjungAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $insert_pengunjung_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_pengunjung = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE tanggal LIKE '$check_date' AND is_beli=1
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date' AND is_beli=1
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_beli = $query->result_array();

      if ($toko_id == "all") {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE tanggal LIKE '$check_date' AND is_beli=0
      ";
      } else {
        $sql = "
        SELECT COUNT(id) AS tdk_beli
        FROM `pengunjung`
        WHERE toko_id = '$toko_id' AND tanggal LIKE '$check_date' AND is_beli=0
      ";
      }
      $query = $this->db->query($sql);
      $data_pengunjung_tdk_beli = $query->result_array();

      $insert_pengunjung = array(
        'tick' => $i,
        'beli' => $data_pengunjung_beli[0]['beli'],
        'tdk_beli' => $data_pengunjung_tdk_beli[0]['tdk_beli']
      );
      array_push($insert_pengunjung_final, $insert_pengunjung);
    }

    return $insert_pengunjung_final;
  }

  public function getOperasionalAnnually($toko_id)
  {
    $ref_start = $this->getFirstDateOfRecord()[0]['tanggal'];
    $ref_end = $this->getLastDateOfRecord()[0]['tanggal'];

    $start = (int) date('Y', strtotime($ref_start));
    $end = (int) date('Y', strtotime($ref_end));

    $insert_operasional_final = array();
    $sql = "";
    for ($i = $start; $i <= $end; $i++) {
      $insert_operasional = array();
      $check_date = $i . '%';
      $check_date = strval($check_date);

      if ($toko_id == "all") {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE created_at LIKE '$check_date'
      ";
      } else {
        $sql = "
        SELECT SUM(biaya) AS totalday
        FROM operasional
        WHERE toko_id = '$toko_id' AND created_at LIKE '$check_date'
      ";
      }
      $query = $this->db->query($sql);
      $data_operasional = $query->result_array();

      $insert_operasional = array(
        'tick' => $i,
        'operasional' => $data_operasional[0]['totalday']
      );
      array_push($insert_operasional_final, $insert_operasional);
    }

    return $insert_operasional_final;
  }
}
