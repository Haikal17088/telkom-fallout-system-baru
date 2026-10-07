@php
// ── Query SEMUA data Fallout dari database ────────────────────────────
// Tidak dibatasi bulan berjalan. Setiap tab otomatis memakai periode
// terbaru yang memang tersedia di database, lalu filter dapat diganti.
$allRows = \App\Models\FalloutData::forWitel($witelSlug)
->orderByDesc('tanggal')
->orderByDesc('row_id')
->get();

// Periode yang benar-benar tersedia di database.
$availableDates = $allRows
->filter(fn ($row) => !empty($row->tanggal))
->map(fn ($row) => \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'))
->unique()
->sortDesc()
->values();

$availableMonths = $allRows
->filter(fn ($row) => !empty($row->tanggal))
->map(fn ($row) => \Carbon\Carbon::parse($row->tanggal)->format('Y-m'))
->unique()
->sortDesc()
->values();

$availableYears = $allRows
->filter(fn ($row) => !empty($row->tanggal))
->map(fn ($row) => \Carbon\Carbon::parse($row->tanggal)->format('Y'))
->unique()
->sortDesc()
->values();

$range = request()->query('range', 'harian');

if (!in_array($range, ['harian', 'bulanan', 'tahunan'], true)) {
    $range = 'bulanan';
}

// Default otomatis = periode TERBARU yang tersedia.
$selectedDate = request()->query(
    'tanggal',
    $availableDates->first() ?? now()->format('Y-m-d')
);

$latestMonthValue = $availableMonths->first() ?? now()->format('Y-m');

$selectedMonth = request()->query(
    'bulan',
    \Illuminate\Support\Str::substr($latestMonthValue, 5, 2)
);

$selectedYear = request()->query(
    'tahun',
    \Illuminate\Support\Str::substr($latestMonthValue, 0, 4)
);

if (!$availableDates->contains($selectedDate)) {
    $selectedDate = $availableDates->first() ?? now()->format('Y-m-d');
}

$monthValues = collect(range(1, 12))->mapWithKeys(function ($month) {
    return [
        str_pad((string) $month, 2, '0', STR_PAD_LEFT)
        => \Carbon\Carbon::create(2000, $month, 1)->translatedFormat('F')
    ];
});

if (!$monthValues->has((string) $selectedMonth)) {
    $selectedMonth = \Illuminate\Support\Str::substr(
        $latestMonthValue,
        5,
        2
    );
}

if (!$availableYears->contains((string) $selectedYear)) {
    $selectedYear = $availableYears->first() ?? now()->format('Y');
}

$selectedMonthValue = sprintf(
    '%s-%s',
    $selectedYear,
    str_pad((string) $selectedMonth, 2, '0', STR_PAD_LEFT)
);

// Data aktif mengikuti tab + filter yang sedang dipilih.
$activeRows = $allRows->filter(function ($row) use (
    $range,
    $selectedDate,
    $selectedMonth,
    $selectedYear,
    $selectedMonthValue
) {

    if (empty($row->tanggal)) {
        return false;
    }

    $date = \Carbon\Carbon::parse($row->tanggal);

    if ($range === 'harian') {
        return $date->format('Y-m-d') === $selectedDate;
    }

    if ($range === 'tahunan') {
        return $date->format('Y') === (string) $selectedYear;
    }

    return $date->format('Y-m') === $selectedMonthValue;
})->values();

$total = $activeRows->count();

$resolved = $activeRows
->filter(
    fn ($row) =>
        strtolower(
            trim(
                (string) ($row->resolved_eskalasi ?? '')
            )
        ) === 'resolved'
)
->count();

$completed = $activeRows
->filter(
    fn ($row) =>
        strtolower(
            trim(
                (string) ($row->status ?? '')
            )
        ) === 'completed'
)
->count();

$cancel = $activeRows
->filter(
    fn ($row) =>
        strtolower(
            trim(
                (string) ($row->resolved_eskalasi ?? '')
            )
        ) === 'cancel'
)
->count();

$eskalasiDit = $activeRows
->filter(
    fn ($row) =>
        strtolower(
            trim(
                (string) ($row->resolved_eskalasi ?? '')
            )
        ) === 'eskalasi_dit'
)
->count();

$close = $activeRows
->filter(
    fn ($row) =>
        strtolower(
            trim(
                (string) ($row->resolved_eskalasi ?? '')
            )
        ) === 'close'
)
->count();

// Uploader mengikuti periode aktif.
$uploaders = $activeRows
->whereNotNull('uploaded_by')
->groupBy('uploaded_by')
->map(function ($group) {
    $first = $group->first();

    return [
        'name' => $first->uploader->name ?? 'Tidak diketahui',
        'count' => $group->count(),
    ];
})
->sortByDesc('count')
->mapWithKeys(
    fn ($item) => [
        $item['name'] => $item['count']
    ]
)
->toArray();

// Breakdown per STO mengikuti periode aktif.
$stoBreakdown = $activeRows
->filter(
    fn ($row) =>
        trim(
            (string) ($row->sto ?? '')
        ) !== ''
)
->groupBy(function ($row) {

    return strtoupper(
        trim(
            (string) $row->sto
        )
    );

})
->map(
    fn ($group) =>
        $group->count()
)
->sortDesc()
->toArray();

$periodLabel = match ($range) {

    'harian' =>
        $selectedDate
            ? \Carbon\Carbon::parse(
                $selectedDate
            )->translatedFormat('d F Y')
            : 'Semua tanggal',

    'tahunan' =>
        $selectedYear,

    default =>
        $selectedMonthValue
            ? \Carbon\Carbon::createFromFormat(
                'Y-m',
                $selectedMonthValue
            )->translatedFormat('F Y')
            : 'Semua bulan',

};

$d = [

    'total' =>
        $total,

    'resolved' =>
        $resolved,

    'completed' =>
        $completed,

    'sto' =>
        count($stoBreakdown),

    'cancel' =>
        $cancel,

    'eskalasi_dit' =>
        $eskalasiDit,

    'close' =>
        $close,

    'uploaders' =>
        $uploaders,

    'sto_breakdown' =>
        $stoBreakdown,

    // Tetap sesuai kode sebelumnya karena kolom ini memang belum tersedia.
    'tipe_fallout' =>
        'Provisioning Failed',

    'sistem' =>
        'UIM / OSM / OSS',

];

$eskalasi =
    max(
        $d['total'] - $d['resolved'],
        0
    );

$resolvedPct =
    $d['total'] > 0
        ? round(
            $d['resolved']
            / $d['total']
            * 100
        )
        : 0;

$completedPct =
    $d['resolved'] > 0
        ? round(
            $d['completed']
            / $d['resolved']
            * 100
        )
        : 0;

$chartId =
    'stoChart_' . $witelSlug;

@endphp


<div class="tr-wrap">

    {{-- Loading hanya di dalam halaman Total Rekap --}}
    <div
        class="tr-page-loading"
        id="trPageLoading"
        aria-live="polite"
        aria-label="Memuat Total Rekap Fallout"
    >
        <div class="tr-page-loading-card">

            <div class="tr-page-loading-logo">
                <span>TF</span>
            </div>

            <div
                class="tr-page-loading-spinner"
                aria-hidden="true"
            ></div>

            <div class="tr-page-loading-title">
                Memuat Total Rekap Fallout
            </div>

            <div class="tr-page-loading-subtitle">
                Menyiapkan data {{ $witel }}
            </div>

        </div>
    </div>


    <div class="tr-head">

        <span class="tr-tag">
            {{ $witel }}
        </span>

        <h2>
            Total Rekap Fallout
        </h2>

        <p>
            Periode: {{ $periodLabel }}
        </p>

    </div>


    <div class="tr-tabs">

        <button
            type="button"
            class="tr-tab active"
            data-range="harian"
            onclick="trSwitchRange(this, 'harian')"
        >
            Rekap Harian
        </button>

        <button
            type="button"
            class="tr-tab"
            data-range="bulanan"
            onclick="trSwitchRange(this, 'bulanan')"
        >
            Rekap Bulanan
        </button>

        <button
            type="button"
            class="tr-tab"
            data-range="tahunan"
            onclick="trSwitchRange(this, 'tahunan')"
        >
            Rekap Tahunan
        </button>

    </div>


    <div
        class="tr-filters"
        data-range-group="harian"
    >

        <div class="tr-field">

            <label>
                Tanggal
            </label>

            <input
                type="date"
                data-auto-filter="tanggal"
                value="{{ $selectedDate }}"
                onchange="trApplyFilter('tanggal', this.value)"
            >

        </div>

    </div>


    <div
        class="tr-filters"
        data-range-group="bulanan"
        style="display:none;"
    >

        <div class="tr-field">

            <label>
                Bulan
            </label>

            <select
                data-auto-filter="bulan"
                onchange="trApplyFilter('bulan', this.value)"
            >

                @foreach ($monthValues as $monthValue => $monthLabel)

                    <option
                        value="{{ $monthValue }}"
                        {{ (string) $selectedMonth === (string) $monthValue ? 'selected' : '' }}
                    >
                        {{ $monthLabel }}
                    </option>

                @endforeach

            </select>

        </div>


        <div class="tr-field">

            <label>
                Tahun
            </label>

            <select
                data-auto-filter="tahun"
                onchange="trApplyFilter('tahun', this.value)"
            >

                @forelse ($availableYears as $year)

                    <option
                        value="{{ $year }}"
                        {{ (string) $selectedYear === (string) $year ? 'selected' : '' }}
                    >
                        {{ $year }}
                    </option>

                @empty

                    <option
                        value="{{ now()->format('Y') }}"
                    >
                        {{ now()->format('Y') }}
                    </option>

                @endforelse

            </select>

        </div>

    </div>


    <div
        class="tr-filters"
        data-range-group="tahunan"
        style="display:none;"
    >

        <div class="tr-field">

            <label>
                Tahun
            </label>

            <select
                data-auto-filter="tahun"
                onchange="trApplyFilter('tahun', this.value)"
            >

                @forelse ($availableYears as $year)

                    <option
                        value="{{ $year }}"
                        {{ (string) $selectedYear === (string) $year ? 'selected' : '' }}
                    >
                        {{ $year }}
                    </option>

                @empty

                    <option value="">
                        Tidak ada tahun
                    </option>

                @endforelse

            </select>

        </div>

    </div>


    <div class="tr-stats">

        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    Total Fallout
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <path
                            d="M9 2 1 16h16L9 2Z"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M9 8v3.5M9 13.2v.1"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['total'] }}
            </div>

            <div class="tr-stat-sub">
                {{ $periodLabel }}
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    RESOLVED
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <circle
                            cx="9"
                            cy="9"
                            r="8"
                            stroke="#C8102E"
                            stroke-width="1.6"
                        />

                        <path
                            d="M5.5 9.3l2.3 2.3L13 6.8"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['resolved'] }}
            </div>

            <div class="tr-stat-sub">
                {{ $eskalasi }} eskalasi
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    COMPLETED
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <circle
                            cx="9"
                            cy="9"
                            r="8"
                            stroke="#C8102E"
                            stroke-width="1.6"
                        />

                        <path
                            d="M9 5v4l3 2"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['completed'] }}
            </div>

            <div class="tr-stat-sub">
                proses selesai
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    STO Aktif
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <path
                            d="M2 15l4-5 3 3 6-8"
                            stroke="#C8102E"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M12 5h4v4"
                            stroke="#C8102E"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['sto'] }}
            </div>

            <div class="tr-stat-sub">
                titik STO tercatat
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    CANCEL
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <circle
                            cx="9"
                            cy="9"
                            r="8"
                            stroke="#C8102E"
                            stroke-width="1.6"
                        />

                        <path
                            d="M6 6l6 6M12 6l-6 6"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['cancel'] }}
            </div>

            <div class="tr-stat-sub">
                dibatalkan
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    ESKALASI DIT
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <path
                            d="M4 14l3-6 3 4 3-8 3 10"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['eskalasi_dit'] }}
            </div>

            <div class="tr-stat-sub">
                naik ke DIT
            </div>

        </div>


        <div class="tr-stat-card">

            <div class="tr-stat-top">

                <span class="tr-stat-label">
                    CLOSE
                </span>

                <span class="tr-stat-icon">

                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <rect
                            x="3"
                            y="3"
                            width="12"
                            height="12"
                            rx="3"
                            stroke="#C8102E"
                            stroke-width="1.6"
                        />

                        <path
                            d="M6.5 9l2 2 3-4"
                            stroke="#C8102E"
                            stroke-width="1.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                    </svg>

                </span>

            </div>

            <div class="tr-stat-value">
                {{ $d['close'] }}
            </div>

            <div class="tr-stat-sub">
                tiket ditutup
            </div>

        </div>

    </div>


    <div class="tr-panels">

        <div class="tr-panel">

            <h3>
                RESOLVED / ESKALASI
            </h3>

            <div class="tr-donut-wrap">

                <svg
                    width="150"
                    height="150"
                    viewBox="0 0 150 150"
                >

                    <circle
                        cx="75"
                        cy="75"
                        r="60"
                        stroke="#E7DEDD"
                        stroke-width="16"
                        fill="none"
                    />

                    <circle
                        cx="75"
                        cy="75"
                        r="60"
                        stroke="#C8102E"
                        stroke-width="16"
                        fill="none"
                        stroke-dasharray="{{ round(2 * 3.14159 * 60 * $resolvedPct / 100) }} 999"
                        stroke-linecap="round"
                        transform="rotate(-90 75 75)"
                    />

                    <text
                        x="75"
                        y="80"
                        text-anchor="middle"
                        font-family="Space Grotesk, sans-serif"
                        font-weight="700"
                        font-size="22"
                        fill="#20161A"
                    >
                        {{ $resolvedPct }}%
                    </text>

                </svg>


                <div class="tr-legend">

                    <div>
                        <span
                            class="dot"
                            style="background:#C8102E"
                        ></span>

                        Resolved ({{ $d['resolved'] }})
                    </div>

                    <div>
                        <span
                            class="dot"
                            style="background:#E7DEDD"
                        ></span>

                        Eskalasi ({{ $eskalasi }})
                    </div>

                </div>

            </div>

        </div>


        <div class="tr-panel">

            <h3>
                Status Penyelesaian
            </h3>

            <div class="tr-donut-wrap">

                <svg
                    width="150"
                    height="150"
                    viewBox="0 0 150 150"
                >

                    <circle
                        cx="75"
                        cy="75"
                        r="60"
                        stroke="#E7DEDD"
                        stroke-width="16"
                        fill="none"
                    />

                    <circle
                        cx="75"
                        cy="75"
                        r="60"
                        stroke="#D9A441"
                        stroke-width="16"
                        fill="none"
                        stroke-dasharray="{{ round(2 * 3.14159 * 60 * $completedPct / 100) }} 999"
                        stroke-linecap="round"
                        transform="rotate(-90 75 75)"
                    />

                    <text
                        x="75"
                        y="80"
                        text-anchor="middle"
                        font-family="Space Grotesk, sans-serif"
                        font-weight="700"
                        font-size="22"
                        fill="#20161A"
                    >
                        {{ $completedPct }}%
                    </text>

                </svg>


                <div class="tr-legend">

                    <div>
                        <span
                            class="dot"
                            style="background:#D9A441"
                        ></span>

                        Completed ({{ $d['completed'] }})
                    </div>

                    <div>

                        <span
                            class="dot"
                            style="background:#E7DEDD"
                        ></span>

                        Proses
                        ({{ $d['resolved'] - $d['completed'] }})

                    </div>

                </div>

            </div>

        </div>


        <div class="tr-panel tr-info">

            <h3>
                Info Rekap
            </h3>

            <div class="tr-info-row">

                <span>
                    Witel
                </span>

                <b>
                    {{ $witel }}
                </b>

            </div>


            <div class="tr-info-row">

                <span>
                    Periode Aktif
                </span>

                <b>
                    {{ $periodLabel }}
                </b>

            </div>


            <div class="tr-info-row">

                <span>
                    Total Record
                </span>

                <b>
                    {{ $d['total'] }} data
                </b>

            </div>


            @if (!empty($d['uploaders']))

                <div class="tr-info-sub">
                    Upload ({{ $periodLabel }})
                </div>

                <div class="tr-uploader-chips">

                    @foreach ($d['uploaders'] as $name => $count)

                        <span class="tr-chip">

                            {{ $name }}

                            <b>
                                {{ $count }}
                            </b>

                        </span>

                    @endforeach

                </div>

            @endif


            <div class="tr-info-row">

                <span>
                    Tipe Fallout
                </span>

                <b>
                    {{ $d['tipe_fallout'] }}
                </b>

            </div>


            <div class="tr-info-row">

                <span>
                    Sistem
                </span>

                <b>
                    {{ $d['sistem'] }}
                </b>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FALLOUT PER STO
         BAGIAN DATA/LOGIC LAIN TETAP SAMA.
         Hanya syntax JS diperbaiki agar Chart.js berjalan.
         ========================================================= --}}

    @if (!empty($d['sto_breakdown']))

        <div class="tr-panel tr-bar-panel">

            <h3>
                Fallout per STO
            </h3>

            <div class="tr-chart-box">

                <canvas
                    id="{{ $chartId }}"
                ></canvas>

            </div>

        </div>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>


        <script>
        (function () {

            const canvas =
                document.getElementById(
                    '{{ $chartId }}'
                );


            if (!canvas) {
                return;
            }


            function renderStoChart() {

                if (
                    typeof Chart === 'undefined'
                ) {
                    return;
                }


                const existingChart =
                    Chart.getChart(canvas);


                if (existingChart) {

                    existingChart.destroy();

                }


                new Chart(
                    canvas,
                    {

                        type: 'bar',

                        data: {

                            labels:
                                @json(
                                    array_keys(
                                        $d['sto_breakdown']
                                    )
                                ),

                            datasets: [

                                {

                                    label:
                                        'Jumlah Fallout',

                                    data:
                                        @json(
                                            array_values(
                                                $d['sto_breakdown']
                                            )
                                        ),

                                    backgroundColor:
                                        '#8A0F26',

                                    borderRadius:
                                        6,

                                    maxBarThickness:
                                        46,

                                }

                            ]

                        },

                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            plugins: {

                                legend: {

                                    display:
                                        false

                                },

                                tooltip: {

                                    backgroundColor:
                                        '#ffffff',

                                    titleColor:
                                        '#20161A',

                                    titleFont: {

                                        weight:
                                            '700'

                                    },

                                    bodyColor:
                                        '#C8102E',

                                    bodyFont: {

                                        weight:
                                            '600'

                                    },

                                    borderColor:
                                        '#E7DEDD',

                                    borderWidth:
                                        1,

                                    padding:
                                        12,

                                    displayColors:
                                        false,

                                    callbacks: {

                                        title:
                                            function (items) {

                                                return items[0]
                                                    .label;

                                            },

                                        label:
                                            function (item) {

                                                return (
                                                    'Jumlah Fallout : '
                                                    +
                                                    item.formattedValue
                                                );

                                            }

                                    }

                                }

                            },

                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        stepSize:
                                            1,

                                        precision:
                                            0

                                    },

                                    grid: {

                                        color:
                                            '#E7DEDD'

                                    }

                                },

                                x: {

                                    grid: {

                                        display:
                                            false

                                    }

                                }

                            }

                        }

                    }
                );

            }


            /*
             * Tunggu Chart.js selesai dimuat.
             * Ini aman ketika halaman Total Rekap
             * dimuat sebagai bagian dari dashboard.
             */

            if (
                typeof Chart !== 'undefined'
            ) {

                renderStoChart();

            } else {

                let attempts =
                    0;


                const waitChart =
                    setInterval(
                        function () {

                            attempts++;


                            if (
                                typeof Chart !== 'undefined'
                            ) {

                                clearInterval(
                                    waitChart
                                );

                                renderStoChart();

                            }


                            if (
                                attempts >= 50
                            ) {

                                clearInterval(
                                    waitChart
                                );

                            }

                        },
                        100
                    );

            }

        })();
        </script>

    @endif

</div>


<style>

.tr-wrap {

    position:
        relative;

    width:
        100%;

    min-height:
        calc(100vh - 250px);

    padding:
        8px 6px 28px;

    box-sizing:
        border-box;
}


/* Loading hanya menutupi area Total Rekap, bukan dashboard/sidebar */

.tr-page-loading {

    pointer-events:
        auto;

    cursor:
        none;

    user-select:
        none;

    overflow:
        hidden;

    position:
        absolute;

    inset:
        0;

    z-index:
        99999;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        28px;

    background:
        rgba(
            250,
            247,
            247,
            .94
        );

    backdrop-filter:
        blur(
            8px
        );

    -webkit-backdrop-filter:
        blur(
            8px
        );

    border-radius:
        22px;

    transition:
        opacity .28s ease,
        visibility .28s ease;

}


.tr-page-loading.is-hidden {

    opacity:
        0;

    visibility:
        hidden;

    pointer-events:
        none;

}


.tr-page-loading-card {

    width:
        min(
            320px,
            100%
        );

    padding:
        28px 24px 24px;

    text-align:
        center;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .9
        );

    border-radius:
        18px;

    background:
        rgba(
            255,
            255,
            255,
            .88
        );

    box-shadow:
        0 24px
        60px
        -30px
        rgba(
            58,
            4,
            16,
            .30
        );

}


.tr-page-loading-logo {

    width:
        52px;

    height:
        52px;

    margin:
        0 auto 14px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        14px;

    background:
        linear-gradient(
            145deg,
            #C8102E,
            #8A0F26
        );

    color:
        #fff;

    font-family:
        'Space Grotesk',
        sans-serif;

    font-size:
        17px;

    font-weight:
        700;

    letter-spacing:
        .03em;

    box-shadow:
        0 12px
        24px
        -12px
        rgba(
            200,
            16,
            46,
            .75
        );

}


.tr-page-loading-spinner {

    width:
        30px;

    height:
        30px;

    margin:
        0 auto 14px;

    border:
        3px solid
        rgba(
            200,
            16,
            46,
            .14
        );

    border-top-color:
        #C8102E;

    border-right-color:
        #8A0F26;

    border-radius:
        50%;

    animation:
        trPageLoadingSpin
        .8s
        linear
        infinite;

}


.tr-page-loading-title {

    font-family:
        'Space Grotesk',
        sans-serif;

    font-size:
        14px;

    font-weight:
        700;

    color:
        #20161A;

}


.tr-page-loading-subtitle {

    margin-top:
        5px;

    font-family:
        'Inter',
        sans-serif;

    font-size:
        11px;

    line-height:
        1.5;

    color:
        #817377;

}


@keyframes trPageLoadingSpin {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


.tr-head {

    margin-bottom:
        22px;

}


.tr-tag {

    display:
        inline-block;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.04em;

    color:
        #C8102E;

    background:
        rgba(
            200,
            16,
            46,
            0.08
        );

    padding:
        5px 12px;

    border-radius:
        999px;

    margin-bottom:
        10px;

}


.tr-head h2 {

    font-family:
        'Space Grotesk',
        sans-serif;

    font-size:
        22px;

    font-weight:
        600;

    color:
        #20161A;

}


.tr-head p {

    margin-top:
        4px;

    font-size:
        12.5px;

    color:
        #7A6B6F;

}


.tr-tabs {

    display:
        inline-flex;

    gap:
        4px;

    padding:
        5px;

    border-radius:
        12px;

    background:
        rgba(
            255,
            255,
            255,
            0.6
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.7
        );

    margin-bottom:
        20px;

}


.tr-tab {

    border:
        none;

    background:
        none;

    cursor:
        pointer;

    padding:
        9px 16px;

    border-radius:
        9px;

    font-size:
        12.5px;

    font-weight:
        600;

    color:
        #7A6B6F;

    font-family:
        'Inter',
        sans-serif;

}


.tr-tab.active {

    background:
        #3A0410;

    color:
        #fff;

}


.tr-filters {

    display:
        flex;

    gap:
        14px;

    margin-bottom:
        22px;

}


.tr-field label {

    display:
        block;

    font-size:
        11.5px;

    font-weight:
        700;

    color:
        #20161A;

    margin-bottom:
        6px;

}


.tr-field select,
.tr-field input[type="date"] {

    padding:
        10px 14px;

    border-radius:
        9px;

    border:
        1.3px solid
        #E7DEDD;

    background:
        #fff;

    font-size:
        13px;

    color:
        #20161A;

    font-family:
        'Inter',
        sans-serif;

    min-width:
        140px;

}


.tr-stats {

    display:
        grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(
                160px,
                1fr
            )
        );

    gap:
        14px;

    margin-bottom:
        22px;

}


.tr-stat-card {

    background:
        rgba(
            255,
            255,
            255,
            0.7
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.8
        );

    border-radius:
        16px;

    padding:
        18px;

}


.tr-stat-top {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

}


.tr-stat-label {

    font-size:
        11.5px;

    color:
        #7A6B6F;

    font-weight:
        600;

}


.tr-stat-icon {

    width:
        30px;

    height:
        30px;

    border-radius:
        9px;

    background:
        rgba(
            200,
            16,
            46,
            0.08
        );

    display:
        grid;

    place-items:
        center;

}


.tr-stat-value {

    font-family:
        'Space Grotesk',
        sans-serif;

    font-weight:
        700;

    font-size:
        28px;

    color:
        #20161A;

    margin-top:
        12px;

}


.tr-stat-sub {

    font-size:
        11px;

    color:
        #7A6B6F;

    margin-top:
        4px;

}


.tr-panels {

    display:
        grid;

    grid-template-columns:
        1fr 1fr 1fr;

    gap:
        14px;

    margin-bottom:
        14px;

    align-items:
        start;

}


.tr-panel {

    background:
        rgba(
            255,
            255,
            255,
            0.7
        );

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.8
        );

    border-radius:
        16px;

    padding:
        18px;

}


.tr-panel h3 {

    font-family:
        'Space Grotesk',
        sans-serif;

    font-size:
        13px;

    font-weight:
        600;

    color:
        #C8102E;

    margin-bottom:
        14px;

}


.tr-donut-wrap {

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    gap:
        12px;

}


.tr-legend {

    font-size:
        11.5px;

    color:
        #20161A;

    width:
        100%;

}


.tr-legend div {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        4px 0;

}


.tr-legend .dot {

    width:
        9px;

    height:
        9px;

    border-radius:
        50%;

    flex:
        none;

    display:
        inline-block;

}


.tr-info-row {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    padding:
        10px 0;

    font-size:
        12px;

    color:
        #7A6B6F;

    border-bottom:
        1px solid
        #E7DEDD;

}


.tr-info-row:last-child {

    border-bottom:
        none;

}


.tr-info-row b {

    color:
        #20161A;

    font-size:
        12.5px;

}


.tr-info-sub {

    font-size:
        10.5px;

    font-weight:
        700;

    letter-spacing:
        0.03em;

    color:
        #7A6B6F;

    text-transform:
        uppercase;

    padding-top:
        12px;

    margin-bottom:
        4px;

}


.tr-uploader-chips {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        6px;

    padding-bottom:
        10px;

    border-bottom:
        1px solid
        #E7DEDD;

    margin-bottom:
        2px;

}


.tr-chip {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    padding:
        5px 10px;

    border-radius:
        999px;

    background:
        rgba(
            200,
            16,
            46,
            0.07
        );

    font-size:
        11px;

    color:
        #20161A;

    font-weight:
        500;

}


.tr-chip b {

    color:
        #C8102E;

    font-weight:
        700;

}


/* =========================================================
   CHART
   ========================================================= */

.tr-bar-panel {

    width:
        100%;

    padding-bottom:
        12px;

}


.tr-chart-box {

    position:
        relative;

    height:
        220px;

    width:
        100%;

    margin-top:
        4px;

}


@media (max-width: 980px) {

    .tr-panels {

        grid-template-columns:
            1fr;

    }

}


/* FULL PAGE LOADING LOCK — hanya halaman ini, tanpa menyentuh dashboard/sidebar */

html.trLoadingLock,
body.trLoadingLock {

    overflow:
        hidden !important;

    cursor:
        none !important;

}


body.trLoadingLock *,
html.trLoadingLock * {

    cursor:
        none !important;

}


.tr-wrap.trLoadingLock {

    overflow:
        hidden !important;

    cursor:
        none !important;

}


.tr-page-loading.is-hidden {

    cursor:
        none !important;

}

</style>


<script>

// ============================================================
// FULL LOADING LOCK — seperti Edit Data: seluruh area halaman
// tertutup, scrollbar dikunci, dan cursor disembunyikan 1 detik.
// ============================================================

(function() {

    const pageLoading =
        document.getElementById(
            'trPageLoading'
        );

    const pageWrap =
        document.querySelector(
            '.tr-wrap'
        );

    const lockClass =
        'trLoadingLock';

    const lockedNodes =
        [];


    if (
        !pageLoading ||
        !pageWrap
    ) {
        return;
    }


    function lockScrollableParents() {

        let node =
            pageWrap;


        while (node) {

            const style =
                window.getComputedStyle(
                    node
                );


            const canScrollY =
                [
                    'auto',
                    'scroll',
                    'overlay'
                ].includes(
                    style.overflowY
                )
                ||
                node.scrollHeight >
                node.clientHeight + 1;


            const canScrollX =
                [
                    'auto',
                    'scroll',
                    'overlay'
                ].includes(
                    style.overflowX
                )
                ||
                node.scrollWidth >
                node.clientWidth + 1;


            if (
                canScrollY ||
                canScrollX
            ) {

                lockedNodes.push({

                    node,

                    overflow:
                        node.style.overflow,

                    overflowY:
                        node.style.overflowY,

                    overflowX:
                        node.style.overflowX,

                });


                node.style.setProperty(
                    'overflow',
                    'hidden',
                    'important'
                );


                node.style.setProperty(
                    'overflow-y',
                    'hidden',
                    'important'
                );


                node.style.setProperty(
                    'overflow-x',
                    'hidden',
                    'important'
                );

            }


            if (
                node ===
                document.body
            ) {
                break;
            }


            node =
                node.parentElement;

        }

    }


    function unlockScrollableParents() {

        lockedNodes
            .reverse()
            .forEach(
                function(item) {

                    item.node.style.overflow =
                        item.overflow;

                    item.node.style.overflowY =
                        item.overflowY;

                    item.node.style.overflowX =
                        item.overflowX;

                }
            );

    }


    document.documentElement.classList.add(
        lockClass
    );


    document.body.classList.add(
        lockClass
    );


    pageWrap.classList.add(
        lockClass
    );


    pageLoading.style.cursor =
        'none';


    lockScrollableParents();


    setTimeout(
        function() {

            unlockScrollableParents();


            pageWrap.classList.remove(
                lockClass
            );


            document.documentElement.classList.remove(
                lockClass
            );


            document.body.classList.remove(
                lockClass
            );


            pageLoading.classList.add(
                'is-hidden'
            );

        },
        1000
    );

})();


function trSwitchRange(
    btn,
    range
) {

    const url =
        new URL(
            window.location.href
        );


    // Tab menentukan periode yang tampil. Saat berpindah tab,
    // periode lama dibersihkan agar tab baru otomatis memakai
    // periode TERBARU yang tersedia di database.

    url.searchParams.set(
        'range',
        range
    );


    if (
        range === 'harian'
    ) {

        url.searchParams.delete(
            'bulan'
        );

        url.searchParams.delete(
            'tahun'
        );

    }
    else if (
        range === 'bulanan'
    ) {

        url.searchParams.delete(
            'tanggal'
        );

    }
    else if (
        range === 'tahunan'
    ) {

        url.searchParams.delete(
            'tanggal'
        );

        url.searchParams.delete(
            'bulan'
        );

    }


    window.location.href =
        url.toString();

}


function trApplyFilter(
    type,
    value
) {

    const url =
        new URL(
            window.location.href
        );


    const range =
        url.searchParams.get(
            'range'
        )
        ||
        'bulanan';


    url.searchParams.set(
        'range',
        range
    );


    url.searchParams.set(
        type,
        value
    );


    if (
        range === 'harian'
    ) {

        url.searchParams.delete(
            'bulan'
        );

        url.searchParams.delete(
            'tahun'
        );

    }
    else if (
        range === 'bulanan'
    ) {

        url.searchParams.delete(
            'tanggal'
        );

    }
    else if (
        range === 'tahunan'
    ) {

        url.searchParams.delete(
            'tanggal'
        );

        url.searchParams.delete(
            'bulan'
        );

    }


    window.location.href =
        url.toString();

}


document.addEventListener(
    'DOMContentLoaded',
    function() {

        const range =
            @json($range);


        const wrap =
            document.querySelector(
                '.tr-wrap'
            );


        if (!wrap) {
            return;
        }


        wrap
            .querySelectorAll(
                '.tr-tab'
            )
            .forEach(
                function(tab) {

                    tab.classList.toggle(
                        'active',
                        tab.dataset.range === range
                    );

                }
            );


        wrap
            .querySelectorAll(
                '[data-range-group]'
            )
            .forEach(
                function(group) {

                    group.style.display =
                        group.dataset.rangeGroup === range
                            ? 'flex'
                            : 'none';

                }
            );

    }
);

</script>