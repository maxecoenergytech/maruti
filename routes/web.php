<?php

use App\Http\Controllers\QuotationPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/quotations/{quotation}/pdf/download', [QuotationPdfController::class, 'download'])
        ->name('quotations.pdf.download');

    Route::get('/admin/quotations/{quotation}/pdf/preview', [QuotationPdfController::class, 'preview'])
        ->name('quotations.pdf.preview');

    Route::get('/admin/reports/export/csv', [\App\Http\Controllers\ReportExportController::class, 'exportCsv'])
        ->name('reports.export.csv');

    Route::get('/admin/reports/export/excel', [\App\Http\Controllers\ReportExportController::class, 'exportExcel'])
        ->name('reports.export.excel');
});
