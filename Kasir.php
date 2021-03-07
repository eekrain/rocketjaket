<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kasir extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    //validasi jika user belum login
    if ($this->session->userdata('masuk') != TRUE) {
      $url = base_url();
      redirect($url);
    } else {
      $this->load->library('cart');
      $this->load->model('Kasir_model');
      $this->load->model('Toko_model');
      $this->load->model('Notifikasi_model');
    }
  }

  public function index()
  {
    if ($this->session->userdata('toko') == 0) {
      $this->getTokoId();
    } else {
      $nama_toko = $this->Toko_model->getNamaToko($this->session->userdata('toko'));
      $data['title'] = "Kasir " . $nama_toko[0]['nama'];
      $data['not_read'] = $this->Notifikasi_model->getNotRead();
      $data['datacat'] = $this->Kasir_model->getCategoryProduct($this->session->userdata('toko'));
      $data['datapdk'] = $this->Kasir_model->getAllProduct($this->session->userdata('toko'));
      $data['toko_id'] = $this->session->userdata('toko');
      $data['tdk_beli'] = $this->Kasir_model->getPengunjungTidakBeli($this->session->userdata('toko'));

      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar');
      $this->load->view('templates/topbar', $data);
      $this->load->view('kasir/index', $data);
      $this->load->view('templates/footer');
    }
  }

  public function admin($toko_id)
  {
    $nama_toko = $this->Toko_model->getNamaToko($toko_id);
    $data['title'] = "Kasir " . $nama_toko[0]['nama'];
    $data['not_read'] = $this->Notifikasi_model->getNotRead();
    $data['datacat'] = $this->Kasir_model->getCategoryProduct($toko_id);
    $data['datapdk'] = $this->Kasir_model->getAllProduct($toko_id);
    $data['toko_id'] = $toko_id;
    $data['tdk_beli'] = $this->Kasir_model->getPengunjungTidakBeli($toko_id);

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar');
    $this->load->view('templates/topbar', $data);
    $this->load->view('kasir/index', $data);
    $this->load->view('templates/footer');
  }

  public function getTokoId()
  {
    $data['title'] = "Kasir";
    $data['not_read'] = $this->Notifikasi_model->getNotRead();
    $data['model'] = $this->Kasir_model->getAllToko();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar');
    $this->load->view('templates/topbar', $data);
    $this->load->view('kasir/getTokoId', $data);
    $this->load->view('templates/footer');
  }

  public function add()
  {
    $sku = $this->input->post('sku');
    $this->load->model('Produk_model');
    $data = $this->Produk_model->getData($sku);

    $insertCart = array(
      'id' => $data['SKU'],
      'name' => $data['nama'],
      'price' => $data['harga_jual'],
      'image' => $data['foto'],
      'diskon' => $data['diskon'],
      'qty' => 1
    );

    $this->cart->insert($insertCart);
    //$dataCart['cart'] = $this->cart->contents();

    $html = $this->load->view('kasir/displayCart');
    echo json_encode($html);
  }

  public function remove()
  {
    $rowid = $this->input->post('rowid');

    if ($rowid == "all") {
      $this->cart->destroy();
    } else {
      $remove = array(
        'rowid' => $rowid,
        'qty' => 0
      );
      $this->cart->update($remove);
    }
    $html = $this->load->view('kasir/displayCart');
    echo json_encode($html);
  }

  public function getTotalQtyCart($sku)
  {
    if ($cart = $this->cart->contents()) {
      foreach ($cart as $item) {
        if ($item['id'] == $sku) {
          echo $item['qty'];
        } else {
          echo 0;
        }
      }
    } else {
      echo 0;
    }
  }

  public function getGrandTotal()
  {
    if ($cart = $this->cart->contents()) {
      $grandtotal = 0;
      foreach ($cart as $item) {
        $grandtotal += ($item['price'] * $item['qty']) - ($item['diskon'] * $item['qty']);
      }

      $finaltotal = 'Rp' . number_format($grandtotal, 0, ',', '.');
      echo $finaltotal;
    } else {
      echo 0;
    }
  }

  public function getGrandTotalNumber()
  {
    if ($cart = $this->cart->contents()) {
      $grandtotal = 0;
      foreach ($cart as $item) {
        $grandtotal += ($item['price'] * $item['qty']) - ($item['diskon'] * $item['qty']);
      }
      return $grandtotal;
    } else {
      return 0;
    }
  }

  public function getGrandTotalNumberAjax()
  {
    if ($cart = $this->cart->contents()) {
      $grandtotal = 0;
      foreach ($cart as $item) {
        $grandtotal += ($item['price'] * $item['qty']) - ($item['diskon'] * $item['qty']);
      }
      echo $grandtotal;
    } else {
      echo 0;
    }
  }

  public function cekCartAny()
  {
    if ($this->cart->contents())
      echo "true";
    else
      echo "false";
  }

  public function prosesPembayaran()
  {
    $toko_id = $this->input->post('toko_id');
    $total = $this->getGrandTotalNumber();

    $jml_pengunjung_beli = $this->input->post('jml_cust');
    $data_tambah_pengunjung_beli = array();
    $inserted_pengunjung_beli = 0;
    for ($x = 0; $x < $jml_pengunjung_beli; $x++) {
      $data = array(
        'tanggal' => date('Y/m/d'),
        'is_beli' => 1,
        'toko_id' => $toko_id
      );
      $status_tambah_pengunjung_beli = $this->Kasir_model->tambahPengunjungBeli($data);
      array_push($data_tambah_pengunjung_beli, $status_tambah_pengunjung_beli);
      if ($status_tambah_pengunjung_beli['status'] === TRUE) {
        $inserted_pengunjung_beli++;
      }
    }

    $cust_id = $this->Kasir_model->tambahCustomer();

    $invoice_id = $this->Kasir_model->prosesInvoice($cust_id, $total);
    $invoice_id;

    $mode = $this->input->post('contact_mode');
    $contact = $this->input->post('contact');

    $status_invoice_details_all = FALSE;
    $status_update_inventory = FALSE;
    $status_insert_invoice_details = FALSE;
    $data_insert_invoice_details = array();
    $data_update_inventory = array();
    if ($cart = $this->cart->contents()) :
      $total_items = 0;
      $inserted_inv_dtl = 0;
      $updated_inv_item = 0;
      foreach ($cart as $item) :
        $invoice_details = array(
          'invoice_id' => $invoice_id,
          'produk_SKU' => $item['id'],
          'harga' => $item['price'],
          'jumlah_pembelian' => $item['qty'],
          'jumlah_diskon' => $item['diskon'] * $item['qty'],
          'subtotal' => ($item['price'] * $item['qty']) - ($item['diskon'] * $item['qty'])
        );
        $total_items++;

        $inv_dtl = $this->Kasir_model->prosesInvoiceDetails($invoice_details);
        array_push($data_insert_invoice_details, $inv_dtl);
        if ($inv_dtl['status'] === TRUE)
          $inserted_inv_dtl++;

        $update_inventory_callback = $this->Kasir_model->updateInventory($invoice_details['produk_SKU'], $invoice_details['jumlah_pembelian'], $toko_id);
        array_push($data_update_inventory, $update_inventory_callback);

        if ($update_inventory_callback['status'] === TRUE)
          $updated_inv_item++;
      endforeach;
      $this->cart->destroy();

      if ($total_items == $inserted_inv_dtl)
        $status_insert_invoice_details = TRUE;

      if ($total_items == $updated_inv_item)
        $status_update_inventory = TRUE;

      if ($total_items == $inserted_inv_dtl && $total_items == $updated_inv_item)
        $status_invoice_details_all = TRUE;
    endif;

    echo '$status_invoice_details_all' . $status_invoice_details_all;
    echo "\n";
    echo '$status_update_inventory' . $status_update_inventory;
    echo "\n";
    echo '$status_insert_invoice_details' . $status_insert_invoice_details;
    echo "\n\n";
    echo '$data_insert_invoice_details';
    echo "\n";
    print_r($data_insert_invoice_details);
    echo "\n\n";
    echo '$data_update_inventory';
    echo "\n";
    print_r($data_update_inventory);

    // $status = '';
    // if ($inserted_pengunjung_beli == $jml_pengunjung_beli && $cust_id !== FALSE && $invoice_id !== FALSE && $status_invoice_details_all === TRUE) {
    //   foreach ($data_update_inventory as $x) {
    //     if ($x['status'] === TRUE) {
    //       if ($x['tsd_after'] <= $x['tsd_min'])
    //         $this->Kasir_model->notifikasiProdukLimit($x['id_inv'], $x['tsd_bfr']);
    //     }
    //   }

    //   if ($mode == "email") {
    //     $status = $this->_sendInvoiceEmail($contact, $invoice_id);
    //   } else {
    //     $status = $this->_sendInvoiceSMS($contact, $invoice_id);
    //   }
    // } else {
    //   foreach ($data_tambah_pengunjung_beli as $x) {
    //     if ($x['status'] === TRUE)
    //       $this->Kasir_model->deletePengunjung($x['id']);
    //   }
    //   if ($cust_id !== FALSE)
    //     $this->Kasir_model->deleteCustomer($cust_id);
    //   if ($invoice_id !== FALSE)
    //     $this->Kasir_model->deleteInvoice($invoice_id);
    //   foreach ($data_insert_invoice_details as $x) {
    //     if ($x['status'] === TRUE)
    //       $this->Kasir_model->deletePengunjung($x['id']);
    //   }
    //   foreach ($data_update_inventory as $x) {
    //     if ($x['status'] === TRUE)
    //       $this->Kasir_model->rollbackUpdateInventory($x['id_inv'], $x['tsd_bfr']);
    //   }

    //   $status = 'error';
    // }
    // echo $status;
  }

  private function _sendInvoiceSMS($contact, $inv_id)
  {
    $invoice = $this->Kasir_model->getInvoiceData($inv_id);
    $msg =
      'Terima kasih ' . ucfirst($invoice[0]['c_nama']) . ' telah berbelanja di Rocketjaket. Invoice anda ' . $invoice[0]['kode_invoice'] . '.' . 'Detail pembelian : ';

    $items = $this->Kasir_model->getInvoiceItemsData($inv_id);
    $pdk_all = '';
    $i = 0;
    $len = count($items);
    foreach ($items as $x) {
      if ($i == $len - 1)
        $pdk_all = $pdk_all . $x['sku'] . '/' . $x['nama_pdk'] . ' ' . $x['qty'] . 'pcs subtotal = Rp ' . number_format($x['subtotal'], 0, ",", ".") . '.';
      else
        $pdk_all = $pdk_all . $x['sku'] . '/' . $x['nama_pdk'] . ' ' . $x['qty'] . 'pcs subtotal = Rp ' . number_format($x['subtotal'], 0, ",", ".") . ', ';
      $i++;
    }
    $msg = $msg . $pdk_all;

    $msg = $msg . ' Grand total : Rp ' . number_format($invoice[0]['grand_total'], 0, ",", ".");

    //proses sms
    $mobile = $contact;
    $message = $msg;
    $msgencode = urlencode($message);
    $userkey = "py4tb6";
    $passkey = "nh89fzd90z";
    $router = "";

    $postdata = array(
      'authkey' => $userkey,
      'mobile' => $mobile,
      'message' => $msgencode,
      'router' => $router
    );
    $url = "https://reguler.zenziva.net/apps/smsapi.php?userkey=$userkey&passkey=$passkey&nohp=$mobile&pesan=$msgencode";

    $ch  = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => $postdata
    ));

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_exec($ch);
  }

  private function _sendInvoiceEmail($contact, $inv_id)
  {
    $invoice = $this->Kasir_model->getInvoiceData($inv_id);
    $items = $this->Kasir_model->getInvoiceItemsData($inv_id);

    $invoice[0]['tanggal'] = date("j F Y", strtotime($invoice[0]['tanggal']));
    $html = $this->load->view('kasir/invoiceEmail', array('invoice' => $invoice[0], 'items' => $items), true);

    $this->load->config('email');
    $this->load->library('email');

    $from = $this->config->item('smtp_user');
    $to = $contact;
    $subject = 'Invoice Pembelian Item Rocketjaket Store';
    $message = $html;

    $this->email->set_mailtype("html");
    $this->email->set_newline("\r\n");
    $this->email->from($from);
    $this->email->to($to);
    $this->email->subject($subject);
    $this->email->message($message);

    if ($this->email->send()) {
      return 'sukses';
    } else {
      return 'error';
    }
  }

  public function getViewKatalog($toko_id)
  {
    $datacat = $this->Kasir_model->getCategoryProduct($toko_id);
    $datapdk = $this->Kasir_model->getAllProduct($toko_id);
    $cari = $this->load->view('kasir/displayProdukCardCari', array('kategori' => $datacat, 'produk' => $datapdk));
    $katalog = $this->load->view('kasir/displayProdukCard', array('kategori' => $datacat, 'produk' => $datapdk));
    $html = array(
      'cari' => $cari,
      'katalog' => $katalog
    );
    echo json_encode($html);
  }

  public function tambahPengunjungTidakBeli()
  {
    $data = $this->Kasir_model->tambahPengunjungTidakBeli();
    echo $data;
  }

  public function tes()
  {
    $sql = "
    SELECT nama
    FROM toko
    WHERE id = 11
    LIMIT 1
    ";
    $query = $this->db->query($sql);
    $toko = $query->row_array();
    print_r($toko);
  }
}
