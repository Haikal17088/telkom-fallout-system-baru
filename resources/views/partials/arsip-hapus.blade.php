@php
  /*
  |--------------------------------------------------------------------------
  | ARSIP & HAPUS DATA
  |--------------------------------------------------------------------------
  | Semua angka dan daftar tanggal diambil langsung dari fallout_data
  | berdasarkan Witel yang sedang dibuka.
  |
  | Fitur yang tersedia:
  | 1. Hapus Per Tanggal
  | 2. Hapus Per Tahun
  | 3. Ringkasan per Tahun
  | 4. 10 Tanggal Terbaru
  |--------------------------------------------------------------------------
  */

  $dbRows = \App\Models\FalloutData::forWitel($witelSlug)
      ->orderByDesc('tanggal')
      ->orderByDesc('row_id')
      ->get()
      ->toBase();


  /*
  |--------------------------------------------------------------------------
  | DAFTAR TAHUN
  |--------------------------------------------------------------------------
  */

  $years = $dbRows
      ->filter(fn ($row) => !empty($row->tanggal))
      ->map(fn ($row) => \Carbon\Carbon::parse($row->tanggal)->year)
      ->unique()
      ->sortDesc()
      ->values();


  $currentYear = (int) now()->year;


  if (!$years->contains($currentYear)) {
      $years->push($currentYear);
  }


  $years = $years
      ->unique()
      ->sortDesc()
      ->values();


  /*
  |--------------------------------------------------------------------------
  | TAHUN TERPILIH
  |--------------------------------------------------------------------------
  */

  $selectedYear = (int) request()->query(
      'tahun',
      $currentYear
  );


  /*
  |--------------------------------------------------------------------------
  | RINGKASAN PER TAHUN
  |--------------------------------------------------------------------------
  */

  $yearSummary = $dbRows
      ->filter(fn ($row) => !empty($row->tanggal))
      ->groupBy(
          fn ($row) =>
              \Carbon\Carbon::parse(
                  $row->tanggal
              )->year
      )
      ->map(
          fn ($group) =>
              $group->count()
      )
      ->sortKeysDesc();


  $selectedYearCount =
      (int) (
          $yearSummary[$selectedYear]
          ?? 0
      );


  /*
  |--------------------------------------------------------------------------
  | TOTAL DATA WITEL
  |--------------------------------------------------------------------------
  */

  $totalWitelData =
      $dbRows->count();


  /*
  |--------------------------------------------------------------------------
  | 10 TANGGAL TERBARU
  |--------------------------------------------------------------------------
  */

  $latestDates = $dbRows
      ->filter(fn ($row) => !empty($row->tanggal))
      ->groupBy(
          function ($row) {
              return \Carbon\Carbon::parse(
                  $row->tanggal
              )->format('Y-m-d');
          }
      )
      ->map(
          fn ($group) =>
              $group->count()
      )
      ->sortKeysDesc()
      ->take(10);


  /*
  |--------------------------------------------------------------------------
  | TANGGAL YANG DIPILIH UNTUK HAPUS
  |--------------------------------------------------------------------------
  */

  $selectedTanggal =
      (string) request()->query(
          'tanggal',
          ''
      );


  $selectedTanggalCount = 0;


  if ($selectedTanggal !== '') {

      $selectedTanggalCount =
          $dbRows
              ->filter(
                  function ($row) use (
                      $selectedTanggal
                  ) {

                      if (!$row->tanggal) {
                          return false;
                      }

                      return \Carbon\Carbon::parse(
                          $row->tanggal
                      )->format('Y-m-d')
                      ===
                      $selectedTanggal;

                  }
              )
              ->count();

  }


  /*
  |--------------------------------------------------------------------------
  | FORMAT TANGGAL
  |--------------------------------------------------------------------------
  */

  $formatDate =
      function ($date) {

          if (!$date) {
              return '-';
          }

          return \Carbon\Carbon::parse(
              $date
          )->translatedFormat(
              'd F Y'
          );

      };

@endphp


<div class="ah-wrap">

  {{-- ============================================================
       LOADING KHUSUS HALAMAN ARSIP & HAPUS
       Menutup seluruh area content seperti halaman Edit Data.
  ============================================================ --}}
  <div
    class="ah-page-loading"
    id="ahPageLoading"
    aria-live="polite"
    aria-label="Memuat Arsip dan Hapus"
  >
    <div class="ah-page-loading-card">
      <div class="ah-page-loading-logo" aria-hidden="true">
        <span>TF</span>
      </div>

      <div class="ah-page-loading-spinner" aria-hidden="true"></div>

      <div class="ah-page-loading-title">
        Memuat Arsip &amp; Hapus
      </div>

      <div class="ah-page-loading-subtitle">
        Menyiapkan data {{ $witel }}
      </div>
    </div>
  </div>



  {{-- ============================================================
       HEADER
  ============================================================ --}}

  <div class="ah-head">

    <div>

      <h2>
        Arsip &amp; Hapus
      </h2>

      <p>
        Hapus data per tanggal atau per tahun secara permanen
        — {{ $witel }}
      </p>

    </div>


    <div class="ah-total-badge">
      {{ number_format($totalWitelData) }} data
    </div>

  </div>


  {{-- ============================================================
       PERINGATAN
  ============================================================ --}}

  <div class="ah-warning">

    <div class="ah-warning-icon">

      <svg
        width="17"
        height="17"
        viewBox="0 0 24 24"
        fill="none"
      >

        <path
          d="M12 3l9 17H3L12 3z"
          stroke="currentColor"
          stroke-width="1.7"
          stroke-linejoin="round"
        />

        <path
          d="M12 9v4"
          stroke="currentColor"
          stroke-width="1.7"
          stroke-linecap="round"
        />

        <circle
          cx="12"
          cy="16.5"
          r="0.8"
          fill="currentColor"
        />

      </svg>

    </div>


    <div>

      <strong>
        Peringatan:
      </strong>

      Data yang dihapus
      <strong>
        tidak bisa dikembalikan.
      </strong>

      Pastikan data sudah dibackup
      (download CSV/Excel) sebelum menghapus.

    </div>

  </div>


  {{-- ============================================================
       FLASH MESSAGE DITAMPILKAN MELALUI MODAL
  ============================================================ --}}

  {{-- ============================================================
       DUA CARD HAPUS
  ============================================================ --}}

  <div class="ah-delete-grid">


    {{-- ==========================================================
         HAPUS PER TANGGAL
    ========================================================== --}}

    <div class="ah-card ah-delete-card">

      <div class="ah-card-head">

        <div class="ah-card-icon">

          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
          >

            <rect
              x="4"
              y="5"
              width="16"
              height="15"
              rx="2"
              stroke="currentColor"
              stroke-width="1.8"
            />

            <path
              d="M8 3v4M16 3v4M4 10h16"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
            />

          </svg>

        </div>


        <div>

          <h3>
            Hapus Per Tanggal
          </h3>

          <p>
            Hapus semua data di tanggal tertentu
          </p>

        </div>

      </div>


      <form
        method="POST"
        action="{{ route('arsip.hapus.tanggal', ['witel' => $witelSlug]) }}"
        onsubmit="return confirmDeleteTanggal(this);"
      >

        @csrf

        @method('DELETE')


        <div class="ah-form-group">

          <label for="ahTanggal">
            Pilih Tanggal
          </label>

          <input
            type="date"
            id="ahTanggal"
            name="tanggal"
            value="{{ $selectedTanggal }}"
            onchange="ahUpdateTanggalPreview()"
          >

        </div>


        <div class="ah-danger-preview">

          <strong id="ahTanggalDeleteCount">
            @if($selectedTanggal !== '')
              {{ number_format($selectedTanggalCount) }}
              data akan dihapus
            @else
              Pilih tanggal terlebih dahulu
            @endif
          </strong>


          <span id="ahTanggalDeleteText">

            @if($selectedTanggal !== '')

              Seluruh data pada
              {{ $formatDate($selectedTanggal) }}

            @else

              Belum ada tanggal yang dipilih

            @endif

          </span>

        </div>


        <button
          type="submit"
          class="ah-delete-btn ah-delete-date-btn"
          id="ahDeleteTanggalBtn"
          {{ ($selectedTanggal === '' || $selectedTanggalCount <= 0) ? 'disabled' : '' }}
        >

          <svg
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
          >

            <path
              d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

          </svg>

          <span>
            Hapus Data Tanggal Ini
          </span>

        </button>

      </form>

    </div>


    {{-- ==========================================================
         HAPUS PER TAHUN
    ========================================================== --}}

    <div class="ah-card ah-delete-card">

      <div class="ah-card-head">

        <div class="ah-card-icon ah-card-icon-year">

          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
          >

            <circle
              cx="12"
              cy="12"
              r="8"
              stroke="currentColor"
              stroke-width="1.8"
            />

            <path
              d="M12 8v4l2.5 2"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

          </svg>

        </div>


        <div>

          <h3>
            Hapus Per Tahun
          </h3>

          <p>
            Hapus semua data dalam satu tahun
          </p>

        </div>

      </div>


      <form
        method="POST"
        action="{{ route('arsip.hapus.tahun', ['witel' => $witelSlug]) }}"
        onsubmit="return confirmDeleteYear(this);"
      >

        @csrf

        @method('DELETE')


        <div class="ah-form-group">

          <label for="ahTahun">
            Pilih Tahun
          </label>

          <select
            id="ahTahun"
            name="tahun"
            onchange="ahUpdateYearPreview()"
          >

            @foreach($years as $year)

              <option
                value="{{ $year }}"
                {{ (int) $year === $selectedYear ? 'selected' : '' }}
              >

                {{ $year }}

              </option>

            @endforeach

          </select>

        </div>


        <div class="ah-danger-preview">

          <strong id="ahDeleteCount">

            {{ number_format($selectedYearCount) }}
            data akan dihapus

          </strong>


          <span id="ahDeleteText">

            Seluruh data tahun
            {{ $selectedYear }}

            @if($selectedYearCount > 0)

              ({{ count(
                $dbRows
                  ->filter(function ($row) use ($selectedYear) {
                      return $row->tanggal
                        && \Carbon\Carbon::parse($row->tanggal)->year == $selectedYear;
                  })
                  ->groupBy(function ($row) {
                      return \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');
                  })
                  ->keys()
              ) }} tanggal)

            @endif

          </span>

        </div>


        <button
          type="submit"
          class="ah-delete-btn"
          id="ahDeleteYearBtn"
          {{ $selectedYearCount <= 0 ? 'disabled' : '' }}
        >

          <svg
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
          >

            <path
              d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
            />

          </svg>

          <span>

            Hapus Semua Data Tahun

            <span id="ahDeleteYearLabel">
              {{ $selectedYear }}
            </span>

          </span>

        </button>

      </form>

    </div>

  </div>


  {{-- ============================================================
       RINGKASAN DATABASE
  ============================================================ --}}

  <div class="ah-card ah-summary-card">

    <div class="ah-summary-title">
      Ringkasan Data di Database
    </div>


    <div class="ah-summary-grid">


      {{-- ========================================================
           PER TAHUN
      ======================================================== --}}

      <div class="ah-summary-section">

        <div class="ah-summary-label">
          PER TAHUN
        </div>


        <div class="ah-year-list">

          @forelse($yearSummary as $year => $count)

            <div class="ah-year-row">

              <span>
                {{ $year }}
              </span>

              <strong>
                {{ number_format($count) }} data
              </strong>

            </div>

          @empty

            <div class="ah-empty-mini">
              Belum ada data di database.
            </div>

          @endforelse

        </div>

      </div>


      {{-- ========================================================
           10 TANGGAL TERBARU
      ======================================================== --}}

      <div class="ah-summary-section">

        <div class="ah-summary-label">
          10 TANGGAL TERBARU
        </div>


        <div class="ah-date-list">

          @forelse($latestDates as $date => $count)

            <div class="ah-date-row">

              <span>
                {{ $formatDate($date) }}
              </span>

              <strong>
                {{ number_format($count) }}
                data
              </strong>

            </div>

          @empty

            <div class="ah-empty-mini">
              Belum ada data di database.
            </div>

          @endforelse

        </div>


        @if($latestDates->count() >= 10)

          <div class="ah-more-date">

            @php

              $totalUniqueDates =
                  $dbRows
                      ->filter(
                          fn ($row) =>
                              !empty($row->tanggal)
                      )
                      ->map(
                          fn ($row) =>
                              \Carbon\Carbon::parse(
                                  $row->tanggal
                              )->format('Y-m-d')
                      )
                      ->unique()
                      ->count();

              $otherDates =
                  max(
                      $totalUniqueDates - 10,
                      0
                  );

            @endphp


            @if($otherDates > 0)

              ...dan
              {{ $otherDates }}
              tanggal lainnya

            @endif

          </div>

        @endif

      </div>

    </div>

  </div>


  {{-- ============================================================
       INFORMASI
  ============================================================ --}}

  <div class="ah-info-card">

    <div class="ah-info-icon">
      ✓
    </div>


    <div>

      <strong>
        Data yang digunakan adalah data asli dari fallout_data
      </strong>

      <p>
        Jumlah data, ringkasan tahun, tanggal terbaru, serta
        proses penghapusan semuanya menggunakan data
        Witel {{ $witel }} yang tersimpan di database.
        Setelah penghapusan berhasil, halaman dimuat ulang
        sehingga angka langsung ikut berubah.
      </p>

    </div>

  </div>

</div>



  {{-- ============================================================
       UI ALERT / CONFIRM
  ============================================================ --}}
  <div class="ah-ui-alert-overlay" id="ahUiAlertOverlay" aria-hidden="true">
    <div class="ah-ui-alert-card" role="dialog" aria-modal="true" aria-labelledby="ahUiAlertTitle">
      <div class="ah-ui-alert-icon" id="ahUiAlertIcon"></div>

      <h3 id="ahUiAlertTitle">Informasi</h3>

      <div class="ah-ui-alert-message" id="ahUiAlertMessage"></div>

      <div class="ah-ui-alert-actions" id="ahUiAlertActions">
        <button
          type="button"
          class="ah-ui-alert-btn ah-ui-alert-cancel"
          id="ahUiAlertCancel"
        >
          Batal
        </button>

        <button
          type="button"
          class="ah-ui-alert-btn ah-ui-alert-confirm"
          id="ahUiAlertConfirm"
        >
          OK
        </button>
      </div>
    </div>
  </div>


<style>


  /* ============================================================
     LOADING FULL AREA ARSIP & HAPUS
     ============================================================ */

  .content.ah-page-loading-lock{
    overflow:hidden !important;
    cursor:none !important;
  }

  .content.ah-page-loading-lock,
  .content.ah-page-loading-lock *{
    cursor:none !important;
  }

  .ah-page-loading{
    position:fixed;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:28px;
    background:rgba(250,247,247,.94);
    backdrop-filter:blur(8px);
    -webkit-backdrop-filter:blur(8px);
    border-radius:0;
    z-index:2147482000;
    transition:opacity .28s ease, visibility .28s ease;
    pointer-events:auto;
    cursor:none !important;
    box-sizing:border-box;
  }

  .ah-page-loading,
  .ah-page-loading *{
    cursor:none !important;
  }

  .ah-page-loading.is-hidden{
    opacity:0;
    visibility:hidden;
    pointer-events:none;
  }

  .ah-page-loading-card{
    width:min(320px,100%);
    padding:28px 24px 24px;
    text-align:center;
    border:1px solid rgba(255,255,255,.9);
    border-radius:18px;
    background:rgba(255,255,255,.90);
    box-shadow:0 24px 60px -30px rgba(58,4,16,.30);
  }

  .ah-page-loading-logo{
    width:52px;
    height:52px;
    margin:0 auto 14px;
    display:grid;
    place-items:center;
    border-radius:14px;
    background:linear-gradient(145deg,#C8102E,#8A0F26);
    color:#fff;
    font-family:'Space Grotesk',sans-serif;
    font-size:17px;
    font-weight:700;
    letter-spacing:.03em;
    box-shadow:0 12px 24px -12px rgba(200,16,46,.75);
  }

  .ah-page-loading-spinner{
    width:30px;
    height:30px;
    margin:0 auto 14px;
    border:3px solid #F1D8DD;
    border-top-color:#C8102E;
    border-right-color:#C8102E;
    border-radius:50%;
    animation:ahPageLoadingSpin .8s linear infinite;
  }

  .ah-page-loading-title{
    font-family:'Space Grotesk',sans-serif;
    font-size:15px;
    font-weight:700;
    color:#2C2528;
  }

  .ah-page-loading-subtitle{
    margin-top:5px;
    font-family:'Inter',sans-serif;
    font-size:11.5px;
    color:#7A6B6F;
  }

  @keyframes ahPageLoadingSpin{
    to{transform:rotate(360deg)}
  }

  .ah-wrap{
    width:100%;
    padding:8px 6px 28px;
  }


  /* ==========================================================
     HEADER
  ========================================================== */

  .ah-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    margin-bottom:16px;
  }


  .ah-head h2{
    font-family:'Space Grotesk',sans-serif;
    font-size:22px;
    font-weight:700;
    color:#C8102E;
  }


  .ah-head p{
    margin-top:4px;
    font-size:12.5px;
    color:#7A6B6F;
  }


  .ah-total-badge{
    padding:9px 14px;
    border-radius:999px;
    background:rgba(200,16,46,.08);
    color:#C8102E;
    font-size:12px;
    font-weight:800;
  }


  /* ==========================================================
     WARNING
  ========================================================== */

  .ah-warning{
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding:13px 15px;
    margin-bottom:16px;
    border-radius:12px;
    border:1px solid #F2D17B;
    background:#FFF9E9;
    color:#9A5B00;
    font-size:12px;
    line-height:1.5;
  }


  .ah-warning-icon{
    flex:none;
    color:#D58A00;
    margin-top:1px;
  }


  .ah-warning strong{
    font-weight:800;
  }


  /* ==========================================================
     ALERT
  ========================================================== */

  .ah-alert{
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding:12px 14px;
    border-radius:12px;
    margin-bottom:14px;
    font-size:12.5px;
  }


  .ah-alert-success{
    background:rgba(30,122,70,.09);
    border:1px solid rgba(30,122,70,.16);
    color:#1E7A46;
  }


  .ah-alert-error{
    background:rgba(200,16,46,.08);
    border:1px solid rgba(200,16,46,.16);
    color:#9B1028;
  }


  /* ==========================================================
     DELETE GRID
  ========================================================== */

  .ah-delete-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
    margin-bottom:16px;
  }


  .ah-card{
    background:rgba(255,255,255,.72);
    border:1px solid rgba(255,255,255,.85);
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px -24px rgba(58,4,16,.28);
  }


  .ah-delete-card{
    min-height:280px;
  }


  .ah-card-head{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:18px;
  }


  .ah-card-icon{
    width:38px;
    height:38px;
    border-radius:10px;
    background:#F9ECEE;
    color:#C8102E;
    display:grid;
    place-items:center;
    flex:none;
  }


  .ah-card-icon-year{
    color:#7A6B6F;
  }


  .ah-card-head h3{
    font-size:15px;
    font-weight:800;
    color:#20161A;
  }


  .ah-card-head p{
    margin-top:3px;
    font-size:11.5px;
    color:#7A6B6F;
  }


  /* ==========================================================
     FORM
  ========================================================== */

  .ah-form-group{
    margin-bottom:16px;
  }


  .ah-form-group label{
    display:block;
    margin-bottom:7px;
    font-size:12px;
    font-weight:700;
    color:#7A6B6F;
  }


  .ah-form-group input,
  .ah-form-group select{
    width:100%;
    min-height:42px;
    padding:10px 12px;
    border:1.2px solid #E7DEDD;
    border-radius:10px;
    background:#fff;
    color:#20161A;
    font-family:'Inter',sans-serif;
    font-size:13px;
  }


  .ah-form-group input:focus,
  .ah-form-group select:focus{
    outline:none;
    border-color:#C8102E;
  }


  /* ==========================================================
     DELETE PREVIEW
  ========================================================== */

  .ah-danger-preview{
    display:flex;
    flex-direction:column;
    gap:3px;
    min-height:59px;
    justify-content:center;
    background:rgba(200,16,46,.06);
    border:1px solid rgba(200,16,46,.16);
    border-radius:12px;
    padding:10px 14px;
    margin-bottom:14px;
  }


  .ah-danger-preview strong{
    font-size:12.5px;
    font-weight:800;
    color:#A30E28;
  }


  .ah-danger-preview span{
    font-size:11px;
    color:#8A6C71;
    line-height:1.4;
  }


  /* ==========================================================
     DELETE BUTTON
  ========================================================== */

  .ah-delete-btn{
    width:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    min-height:41px;
    border:none;
    border-radius:10px;
    padding:10px 14px;
    background:#5C0A1B;
    color:#fff;
    font-family:'Inter',sans-serif;
    font-size:12.5px;
    font-weight:800;
    cursor:pointer;
    transition:
      transform .12s ease,
      opacity .15s ease,
      background .15s ease;
  }


  .ah-delete-btn:hover:not(:disabled){
    background:#430713;
    transform:translateY(-1px);
  }


  .ah-delete-btn:disabled{
    opacity:.45;
    cursor:not-allowed;
    transform:none;
  }


  /* ==========================================================
     SUMMARY
  ========================================================== */

  .ah-summary-card{
    margin-bottom:16px;
  }


  .ah-summary-title{
    font-size:15px;
    font-weight:800;
    color:#C8102E;
    margin-bottom:16px;
  }


  .ah-summary-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
  }


  .ah-summary-label{
    font-size:10px;
    font-weight:800;
    letter-spacing:.08em;
    color:#9A777D;
    margin-bottom:8px;
  }


  .ah-year-list,
  .ah-date-list{
    display:flex;
    flex-direction:column;
    gap:5px;
  }


  .ah-year-row,
  .ah-date-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    padding:10px 12px;
    border-radius:10px;
    background:#FBF0F2;
    font-size:12px;
    color:#20161A;
  }


  .ah-year-row strong,
  .ah-date-row strong{
    font-weight:800;
    color:#20161A;
  }


  .ah-date-row{
    background:#FBF9F7;
    border:1px solid #F1EAE9;
  }


  .ah-more-date{
    text-align:center;
    margin-top:7px;
    font-size:10.5px;
    color:#A18489;
  }


  .ah-empty-mini{
    padding:16px 12px;
    border:1px dashed #E7DEDD;
    border-radius:10px;
    color:#7A6B6F;
    font-size:12px;
    text-align:center;
  }


  /* ==========================================================
     INFO
  ========================================================== */

  .ah-info-card{
    display:flex;
    align-items:flex-start;
    gap:11px;
    background:rgba(255,255,255,.72);
    border:1px solid rgba(255,255,255,.85);
    border-radius:16px;
    padding:15px 18px;
    box-shadow:0 10px 30px -24px rgba(58,4,16,.22);
  }


  .ah-info-icon{
    width:26px;
    height:26px;
    border-radius:50%;
    background:#EEF7F1;
    color:#1E7A46;
    display:grid;
    place-items:center;
    font-size:13px;
    font-weight:800;
    flex:none;
  }


  .ah-info-card strong{
    display:block;
    font-size:12.5px;
    color:#20161A;
  }


  .ah-info-card p{
    margin-top:4px;
    font-size:11.5px;
    line-height:1.55;
    color:#7A6B6F;
  }


  /* ==========================================================
     RESPONSIVE
  ========================================================== */

  @media(max-width:850px){

    .ah-delete-grid,
    .ah-summary-grid{
      grid-template-columns:1fr;
    }

  }


  /* ==========================================================
     UI ALERT / CONFIRM
  ========================================================== */

  html.ah-ui-lock,
  body.ah-ui-lock{
    overflow:hidden !important;
  }

  .ah-ui-alert-overlay{
    display:none;
    position:fixed !important;
    inset:0 !important;
    width:100vw !important;
    height:100vh !important;
    z-index:2147483000 !important;
    background:rgba(20,16,18,.56);
    backdrop-filter:blur(7px);
    -webkit-backdrop-filter:blur(7px);
    align-items:center;
    justify-content:center;
    padding:16px;
    overflow:hidden !important;
    box-sizing:border-box;
  }

  .ah-ui-alert-overlay.open{
    display:flex;
    animation:ahUiAlertFadeIn .18s ease;
  }

  .ah-ui-alert-card{
    width:min(440px, calc(100vw - 32px));
    max-width:440px;
    max-height:calc(100vh - 32px);
    overflow:hidden;
    background:#fff;
    border-radius:22px;
    padding:24px 22px 20px;
    box-shadow:0 34px 90px rgba(0,0,0,.30);
    text-align:center;
    animation:ahUiAlertPop .18s ease;
    box-sizing:border-box;
  }

  .ah-ui-alert-icon{
    width:54px;
    height:54px;
    margin:0 auto 14px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(200,16,46,.08);
    color:#C8102E;
  }

  .ah-ui-alert-icon.success{
    background:rgba(30,122,70,.08);
    color:#16A34A;
  }

  .ah-ui-alert-icon.error{
    background:rgba(200,16,46,.08);
    color:#C8102E;
  }

  .ah-ui-alert-icon svg{
    width:27px;
    height:27px;
    display:block;
    stroke:currentColor;
  }

  .ah-ui-alert-card h3{
    margin:0 0 8px;
    font-family:'Space Grotesk',sans-serif;
    font-size:18px;
    font-weight:700;
    color:#252331;
  }

  .ah-ui-alert-message{
    min-height:42px;
    margin:0 auto 20px;
    max-width:380px;
    font-family:'Inter',sans-serif;
    font-size:12.5px;
    line-height:1.65;
    color:#7A808B;
    overflow-wrap:anywhere;
    word-break:break-word;
  }

  .ah-ui-alert-message strong{
    color:#2C3442;
    font-weight:800;
  }

  .ah-ui-alert-actions{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
  }

  .ah-ui-alert-actions.single{
    grid-template-columns:1fr;
  }

  .ah-ui-alert-btn{
    height:42px;
    border-radius:11px;
    font-family:'Inter',sans-serif;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.18s ease;
  }

  .ah-ui-alert-cancel{
    border:1px solid #E5E0E1;
    background:#fff;
    color:#6F7177;
  }

  .ah-ui-alert-cancel:hover{
    background:#F8F6F6;
  }

  .ah-ui-alert-confirm{
    border:1px solid #B40000;
    background:#B40000;
    color:#fff;
    box-shadow:0 10px 22px -14px rgba(180,0,0,.8);
  }

  .ah-ui-alert-confirm:hover{
    background:#980000;
    border-color:#980000;
  }

  .ah-ui-alert-confirm.success-btn{
    background:#650014;
    border-color:#650014;
  }

  @keyframes ahUiAlertFadeIn{
    from{opacity:0}
    to{opacity:1}
  }

  @keyframes ahUiAlertPop{
    from{opacity:0;transform:translateY(10px) scale(.98)}
    to{opacity:1;transform:translateY(0) scale(1)}
  }

  @media(max-width:520px){
    .ah-ui-alert-card{
      max-width:calc(100vw - 32px);
      padding:20px 18px 18px;
    }
  }

</style>


<script>

  (function(){

    /* ===========================================================
       LOADING FULL AREA ARSIP & HAPUS
       Mengunci scroll + cursor pada area .content saja.
       =========================================================== */

    const ahPageLoading = document.getElementById('ahPageLoading');
    const ahWrap = document.querySelector('.ah-wrap');
    const ahContent = ahWrap
      ? (ahWrap.closest('.content') || ahWrap.parentElement)
      : null;

    let ahLoadingResizeHandler = null;

    function ahPositionPageLoading(){
      if (!ahPageLoading || !ahContent) return;

      const rect = ahContent.getBoundingClientRect();

      ahPageLoading.style.left = rect.left + 'px';
      ahPageLoading.style.top = rect.top + 'px';
      ahPageLoading.style.width = rect.width + 'px';
      ahPageLoading.style.height = rect.height + 'px';
    }

    if (ahPageLoading && ahContent){

      ahContent.classList.add('ah-page-loading-lock');

      ahPositionPageLoading();

      ahLoadingResizeHandler = ahPositionPageLoading;
      window.addEventListener('resize', ahLoadingResizeHandler);

      /* Pastikan overlay langsung berada di atas seluruh content. */
      if (ahPageLoading.parentElement !== document.body){
        document.body.appendChild(ahPageLoading);
      }

      ahPositionPageLoading();

      setTimeout(function(){

        ahPageLoading.classList.add('is-hidden');
        ahContent.classList.remove('ah-page-loading-lock');

        if (ahLoadingResizeHandler){
          window.removeEventListener('resize', ahLoadingResizeHandler);
          ahLoadingResizeHandler = null;
        }

        setTimeout(function(){
          if (ahPageLoading && ahPageLoading.parentElement === document.body){
            ahPageLoading.remove();
          }
        }, 320);

      }, 1000);
    }


    /*
    |--------------------------------------------------------------------------
    | DATA JUMLAH PER TAHUN
    |--------------------------------------------------------------------------
    */

    const yearCounts = @json(
      $yearSummary->mapWithKeys(
        fn($count, $year) => [
          (string) $year => (int) $count
        ]
      )
    );


    /*
    |--------------------------------------------------------------------------
    | DATA JUMLAH PER TANGGAL
    |--------------------------------------------------------------------------
    */

    const dateCounts = @json(
      $latestDates->mapWithKeys(
        fn($count, $date) => [
          (string) $date => (int) $count
        ]
      )
    );


    /*
    |--------------------------------------------------------------------------
    | UPDATE PREVIEW TAHUN
    |--------------------------------------------------------------------------
    */

    window.ahUpdateYearPreview =
      function(){

        const select =
          document.getElementById(
            'ahTahun'
          );


        const countEl =
          document.getElementById(
            'ahDeleteCount'
          );


        const textEl =
          document.getElementById(
            'ahDeleteText'
          );


        const button =
          document.getElementById(
            'ahDeleteYearBtn'
          );


        const yearLabel =
          document.getElementById(
            'ahDeleteYearLabel'
          );


        if (!select){
          return;
        }


        const year =
          select.value;


        const count =
          Number(
            yearCounts[year] || 0
          );


        countEl.textContent =
          count.toLocaleString(
            'id-ID'
          )
          +
          ' data akan dihapus';


        textEl.textContent =
          'Seluruh data tahun '
          +
          year;


        yearLabel.textContent =
          year;


        button.disabled =
          count <= 0;

      };


    /*
    |--------------------------------------------------------------------------
    | UPDATE PREVIEW TANGGAL
    |--------------------------------------------------------------------------
    */

    window.ahUpdateTanggalPreview =
      function(){

        const input =
          document.getElementById(
            'ahTanggal'
          );


        const countEl =
          document.getElementById(
            'ahTanggalDeleteCount'
          );


        const textEl =
          document.getElementById(
            'ahTanggalDeleteText'
          );


        const button =
          document.getElementById(
            'ahDeleteTanggalBtn'
          );


        if (!input){
          return;
        }


        const date =
          input.value;


        if (!date){

          countEl.textContent =
            'Pilih tanggal terlebih dahulu';


          textEl.textContent =
            'Belum ada tanggal yang dipilih';


          button.disabled =
            true;


          return;
        }


        /*
        |--------------------------------------------------------------------------
        | Untuk tanggal yang tidak masuk 10 tanggal terbaru,
        | nilai count belum tersedia di JS. Agar tombol tetap benar,
        | arahkan browser ke query tanggal dan gunakan nilai dari server.
        |--------------------------------------------------------------------------
        */

        const params =
          new URLSearchParams(
            window.location.search
          );


        params.set(
          'tanggal',
          date
        );


        window.location.search =
          params.toString();

      };


    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI HAPUS TANGGAL
    |--------------------------------------------------------------------------
    */

    const ahUiAlertOverlay = document.getElementById('ahUiAlertOverlay');
    const ahUiAlertIcon = document.getElementById('ahUiAlertIcon');
    const ahUiAlertTitle = document.getElementById('ahUiAlertTitle');
    const ahUiAlertMessage = document.getElementById('ahUiAlertMessage');
    const ahUiAlertActions = document.getElementById('ahUiAlertActions');
    const ahUiAlertCancel = document.getElementById('ahUiAlertCancel');
    const ahUiAlertConfirm = document.getElementById('ahUiAlertConfirm');
    let ahUiAlertResolver = null;

    if (ahUiAlertOverlay && ahUiAlertOverlay.parentElement !== document.body) {
      document.body.appendChild(ahUiAlertOverlay);
    }

    function ahEscapeUiHtml(value){
      return String(value ?? '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
    }

    function ahUiIcon(type){
      if(type === 'success'){
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 9.2 17 19 7"/></svg>';
      }

      if(type === 'error'){
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg>';
      }

      return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8.5V6a3 3 0 0 1 6 0v2.5"/><path d="M5 8.5h14l-1 11H6l-1-11Z"/><path d="M9 11.5v4"/><path d="M15 11.5v4"/></svg>';
    }

    function ahOpenUiAlert({
      type='error',
      title='Informasi',
      message='',
      confirmText='OK',
      cancelText='Batal',
      showCancel=false
    }){
      return new Promise(resolve => {
        ahUiAlertResolver = resolve;

        ahUiAlertIcon.className = `ah-ui-alert-icon ${type}`;
        ahUiAlertIcon.innerHTML = ahUiIcon(type);

        ahUiAlertTitle.textContent = title;
        ahUiAlertMessage.innerHTML = message;
        ahUiAlertCancel.textContent = cancelText;
        ahUiAlertConfirm.textContent = confirmText;

        ahUiAlertCancel.style.display = showCancel ? '' : 'none';
        ahUiAlertActions.classList.toggle('single', !showCancel);
        ahUiAlertConfirm.classList.toggle('success-btn', type === 'success');

        document.documentElement.classList.add('ah-ui-lock');
        document.body.classList.add('ah-ui-lock');

        ahUiAlertOverlay.classList.add('open');
        ahUiAlertOverlay.setAttribute('aria-hidden','false');

        setTimeout(() => ahUiAlertConfirm.focus(), 20);
      });
    }

    function ahCloseUiAlert(value){
      ahUiAlertOverlay.classList.remove('open');
      ahUiAlertOverlay.setAttribute('aria-hidden','true');

      document.documentElement.classList.remove('ah-ui-lock');
      document.body.classList.remove('ah-ui-lock');

      if(ahUiAlertResolver){
        const resolve = ahUiAlertResolver;
        ahUiAlertResolver = null;
        resolve(value);
      }
    }

    function ahShowUiAlert(options){
      return ahOpenUiAlert({...options, showCancel:false});
    }

    function ahShowUiConfirm(options){
      return ahOpenUiAlert({...options, showCancel:true, type:'error'});
    }

    ahUiAlertConfirm.addEventListener('click', () => ahCloseUiAlert(true));
    ahUiAlertCancel.addEventListener('click', () => ahCloseUiAlert(false));

    ahUiAlertOverlay.addEventListener('click', event => {
      if(event.target === ahUiAlertOverlay){
        ahCloseUiAlert(false);
      }
    });

    document.addEventListener('keydown', event => {
      if(event.key === 'Escape' && ahUiAlertOverlay.classList.contains('open')){
        ahCloseUiAlert(false);
      }
    });

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI HAPUS TANGGAL
    |--------------------------------------------------------------------------
    */

    window.confirmDeleteTanggal =
      async function(form){

        const input =
          document.getElementById(
            'ahTanggal'
          );


        if (!input || !input.value){

          await ahShowUiAlert({
            type:'error',
            title:'Tanggal Belum Dipilih',
            message:'Pilih tanggal terlebih dahulu.',
            confirmText:'OK'
          });

          return false;
        }


        const countText =
          document.getElementById(
            'ahTanggalDeleteCount'
          )?.textContent
          || '';


        if (
          countText.includes('0 data')
          ||
          countText.includes('Pilih tanggal terlebih dahulu')
        ){

          await ahShowUiAlert({
            type:'error',
            title:'Data Tidak Ditemukan',
            message:`Tidak ada data pada tanggal <strong>${ahEscapeUiHtml(input.value)}</strong>.`,
            confirmText:'OK'
          });

          return false;
        }


        const confirmed =
          await ahShowUiConfirm({
            title:'Hapus Data?',
            message:`Apakah kamu yakin ingin menghapus seluruh data pada tanggal <strong>${ahEscapeUiHtml(input.value)}</strong>? Data yang sudah dihapus tidak dapat dikembalikan.`,
            confirmText:'Hapus',
            cancelText:'Batal'
          });


        if (!confirmed){
          return false;
        }


        HTMLFormElement.prototype.submit.call(form);
        return false;
      };





    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI HAPUS TAHUN
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI HAPUS TAHUN
    |--------------------------------------------------------------------------
    */

    window.confirmDeleteYear =
      async function(form){

        const select =
          document.getElementById(
            'ahTahun'
          );


        const year =
          select?.value
          || '';


        const count =
          Number(
            yearCounts[year]
            || 0
          );


        if (count <= 0){

          await ahShowUiAlert({
            type:'error',
            title:'Data Tidak Ditemukan',
            message:`Tidak ada data pada tahun <strong>${ahEscapeUiHtml(year)}</strong>.`,
            confirmText:'OK'
          });

          return false;
        }


        const confirmed =
          await ahShowUiConfirm({
            title:'Hapus Data?',
            message:`Apakah kamu yakin ingin menghapus <strong>${count.toLocaleString('id-ID')} data</strong> pada tahun <strong>${ahEscapeUiHtml(year)}</strong> untuk <strong>{{ addslashes($witel) }}</strong>? Data yang sudah dihapus tidak dapat dikembalikan.`,
            confirmText:'Hapus',
            cancelText:'Batal'
          });


        if (!confirmed){
          return false;
        }


        HTMLFormElement.prototype.submit.call(form);
        return false;
      };





    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */

    ahUpdateYearPreview();


    /*
    |--------------------------------------------------------------------------
    | HASIL HAPUS DARI SERVER
    |--------------------------------------------------------------------------
    */

    const ahServerSuccess =
      @json(session('status'));

    const ahServerErrors =
      @json($errors->all());


    if (ahServerSuccess){

      ahShowUiAlert({
        type:'success',
        title:'Berhasil',
        message:ahEscapeUiHtml(ahServerSuccess),
        confirmText:'OK'
      });

    } else if (
      Array.isArray(ahServerErrors)
      &&
      ahServerErrors.length
    ){

      ahShowUiAlert({
        type:'error',
        title:'Gagal',
        message:ahServerErrors
          .map(error => ahEscapeUiHtml(error))
          .join('<br>'),
        confirmText:'OK'
      });

    }

  })();

</script>
