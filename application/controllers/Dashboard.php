<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    //validasi jika user belum login

    if ($this->session->userdata('role') > 2) {
      $url_kasir = base_url('Kasir');
      redirect($url_kasir);
    }

    if ($this->session->userdata('masuk') != TRUE) {
      $url = base_url('Auth');
      redirect($url);
    } else {
      $this->load->model('Notifikasi_model');
      $this->load->model('Dashboard_model');
    }
  }

  public function index()
  {
    $this->load->library('cart');
    $this->cart->destroy();
    $data['title'] = "Dashboard";
    $data['not_read'] = $this->Notifikasi_model->getNotRead();
    $data['toko'] = $this->Dashboard_model->getListToko();
    $data['first_date_of_record'] = $this->Dashboard_model->getFirstDateOfRecord()[0]['tanggal'];
    $data['last_date_of_record'] = $this->Dashboard_model->getLastDateOfRecord()[0]['tanggal'];
    // $data['month'] = $this->Dashboard_model->getProfitByMonth(2, 2019, 12);
    // $data['year'] = $this->Dashboard_model->getProfitByYear(2, 2019);
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar');
    $this->load->view('templates/topbar', $data);
    $this->load->view('dashboard/index');
    $this->load->view('templates/footer');
  }
  //---------------------------------------------------------------

  public function getChartData()
  {
    $toko = $this->input->post('toko');
    $mode = $this->input->post('mode');
    $bulan = $this->input->post('bulan');
    $tahun = $this->input->post('tahun');

    $c1;
    $c2;
    $c3;
    $c4;
    $c5;
    $c6;

    if ($mode == "hari") {
      $c1 = $this->Dashboard_model->getProfitByMonth($toko, $tahun, $bulan);
      $c2 = $this->Dashboard_model->getOmsetByMonth($toko, $tahun, $bulan);
      $c3 = $this->Dashboard_model->getTransaksiByMonth($toko, $tahun, $bulan);
      $c4 = $this->Dashboard_model->getTerjualByMonth($toko, $tahun, $bulan);
      $c5 = $this->Dashboard_model->getPengunjungByMonth($toko, $tahun, $bulan);
      $c6 = $this->Dashboard_model->getOperasionalByMonth($toko, $tahun, $bulan);
    } else if ($mode == "bulan") {
      $c1 = $this->Dashboard_model->getProfitByYear($toko, $tahun);
      $c2 = $this->Dashboard_model->getOmsetByYear($toko, $tahun);
      $c3 = $this->Dashboard_model->getTransaksiByYear($toko, $tahun);
      $c4 = $this->Dashboard_model->getTerjualByYear($toko, $tahun);
      $c5 = $this->Dashboard_model->getPengunjungByYear($toko, $tahun);
      $c6 = $this->Dashboard_model->getOperasionalByYear($toko, $tahun);
    } else if ($mode == "tahun") {
      $c1 = $this->Dashboard_model->getProfitAnnually($toko);
      $c2 = $this->Dashboard_model->getOmsetAnnually($toko);
      $c3 = $this->Dashboard_model->getTransaksiAnnually($toko);
      $c4 = $this->Dashboard_model->getTerjualAnnually($toko);
      $c5 = $this->Dashboard_model->getPengunjungAnnually($toko);
      $c6 = $this->Dashboard_model->getOperasionalAnnually($toko);
    }

    $chartData = array(
      'c1' => $c1,
      'c2' => $c2,
      'c3' => $c3,
      'c4' => $c4,
      'c5' => $c5,
      'c6' => $c6
    );

    echo json_encode($chartData);
  }
}
