<?php

namespace App\Imports;

use App\Models\FalloutData;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FalloutImport implements
    ToModel,
    WithStartRow,
    WithCalculatedFormulas,
    SkipsEmptyRows
{
    protected string $batchId;
    protected ?string $uploadedBy;

    public function __construct(
        string $batchId,
        ?string $uploadedBy
    ) {
        $this->batchId = $batchId;
        $this->uploadedBy = $uploadedBy;
    }

    /**
     * Data Excel mulai dari baris 2.
     *
     * Sheet ALL:
     *
     * A = Order ID
     * B = Status Message
     * C = STO
     * D = Tgl Fallout
     * E = PIC
     * F = RESOLVED/ESKALASI
     * G = Status
     * H = KET
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $orderId = trim((string) ($row[0] ?? ''));

        // Lewati baris kosong / tidak mempunyai Order ID.
        if ($orderId === '') {
            return null;
        }

        $deskripsi = $row[1] ?? null;
        $sto       = $row[2] ?? null;
        $tanggal   = $this->parseTanggal($row[3] ?? null);
        $pic       = $row[4] ?? null;
        $resolved  = $row[5] ?? null;
        $status    = $row[6] ?? null;
        $ket       = $row[7] ?? null;

        // Bersihkan nilai error dari Excel.
        $deskripsi = $this->cleanExcelValue($deskripsi);
        $sto       = $this->cleanExcelValue($sto);
        $resolved  = $this->cleanExcelValue($resolved);
        $status    = $this->cleanExcelValue($status);
        $ket       = $this->cleanExcelValue($ket);

        /*
         * Kalau status dari formula Excel tidak terbaca,
         * tentukan berdasarkan KET.
         */
        if (!$status) {
            $status = $this->statusFromKet($ket);
        }

        return new FalloutData([
            'row_id'            => (string) Str::uuid(),
            'batch_id'          => $this->batchId,
            'order_id'          => $orderId,
            'deskripsi'         => $deskripsi,
            'sto'               => $sto
                ? strtoupper(trim((string) $sto))
                : null,
            'tanggal'           => $tanggal,
            'pic'               => $pic
                ? trim((string) $pic)
                : null,
            'resolved_eskalasi' => $this->normalizeResolved($resolved),
            'status'            => $this->normalizeStatus($status),
            'ket'               => $ket
                ? trim((string) $ket)
                : null,
            'uploaded_by'       => $this->uploadedBy,
        ]);
    }

    /**
     * Membersihkan error formula Excel.
     */
    protected function cleanExcelValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (in_array($value, [
            '#REF!',
            '#VALUE!',
            '#N/A',
            '#NAME?',
            '#DIV/0!',
            '#NULL!',
            '#NUM!',
        ], true)) {
            return null;
        }

        return $value;
    }

    /**
     * RESOLVED / ESKALASI DIT
     * menjadi format konsisten.
     */
    protected function normalizeResolved($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        return str_replace(
            [' ', '/', '-'],
            '_',
            $value
        );
    }

    /**
     * Status:
     *
     * COMPLETED
     * Process OSS (Provision Issued)
     */
    protected function normalizeStatus($value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        if ($value === 'completed') {
            return 'completed';
        }

        if (str_starts_with($value, 'process oss')) {
            return 'process_oss';
        }

        return str_replace(
            [' ', '/', '-'],
            '_',
            $value
        );
    }

    /**
     * Tentukan status berdasarkan KET
     * jika formula Status tidak terbaca.
     */
    protected function statusFromKet($ket): ?string
    {
        if (!$ket) {
            return null;
        }

        $ket = strtolower(trim((string) $ket));

        $completedCodes = [
            '1054',
            '1053',
            '1030',
            '1011',
            '1052',
            '2200',
            '2400',
            '5003',
            'term',
        ];

        if (in_array($ket, $completedCodes, true)) {
            return 'completed';
        }

        return 'process_oss';
    }

    /**
     * Konversi tanggal Excel menjadi YYYY-MM-DD.
     */
    protected function parseTanggal($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject(
                    $value
                )->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
