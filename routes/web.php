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
});
