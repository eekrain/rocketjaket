<div class="row mb-4">

  <div class="col-12 col-lg-5 mb-4">
    <div class="card">
      <div class="card-header">
        Pilih Apa Yang Ditampilkan
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="list-toko">Pilihan Tampilan</label>
          <select class="form-control" id="list-toko">
            <option value="all">Semuanya</option>
            <?php foreach ($toko as $x) { ?>
              <option value="<?= $x['id'] ?>"><?= $x['nama'] ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-header">
        Range Tanggal
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="input_date">Input Range Tanggal</label>
          <div id="input_date">
            <div class="row">
              <div class="col-12 col-lg-6 mb-3">
                <div class="input-group">
                  <input type="text" id="input_start_date" class="form-control" placeholder="Tanggal Mulai" aria-describedby="basic-addon1" data-toggle="datepicker" readonly="readonly" style="background-color:white">
                  <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-calendar-alt"></i></span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6 mb-3">
                <div class="input-group">
                  <input type="text" id="input_end_date" class="form-control" placeholder="Tanggal Akhir" aria-describedby="basic-addon2" data-toggle="datepicker" readonly="readonly" style="background-color:white">
                  <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2"><i class="fas fa-calendar-alt"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 text-center">
            <button type="button" class="btn btn-primary mr-4" id="btn-custom-reset">
              <span class="text">Reset</span>
            </button>
            <button type="button" class="btn btn-primary" id="btn-custom-range">
              <span class="text">Tampilkan</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div id="dashboard-content">

</div>