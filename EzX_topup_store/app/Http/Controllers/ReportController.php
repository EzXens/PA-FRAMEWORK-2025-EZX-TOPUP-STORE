<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function exportCsv()
    {
        $fileName = 'laporan_keuangan.csv';

        // Ambil data dari service (sesuaikan dengan lokasimu)
        $monthlyFinancials = app('App\\Services\\ReportService')->getMonthlyFinancials();

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

       $callback = function () use ($monthlyFinancials) {
    $file = fopen('php://output', 'w');

    // Tambahkan BOM UTF-8
    fwrite($file, "\xEF\xBB\xBF");

    // Header kolom CSV
    fputcsv($file, [
        'Bulan',
        'Pendapatan',
        'Pending',
        'Total Transaksi',
        'Pendapatan + Pending'
    ]);

    foreach ($monthlyFinancials as $row) {
        fputcsv($file, [
            $row['label'],
            $row['revenue'],
            $row['pending'],
            $row['transaction_count'],
            $row['revenue'] + $row['pending'],
        ]);
    }

    fclose($file);
};


        return response()->stream($callback, 200, $headers);
    }
}
