<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class QuotationPdfController extends Controller
{
    public function download(Quotation $quotation)
    {
        $this->authorizeView($quotation);

        $pdf = $this->generatePdf($quotation);
        $fileName = str_replace(['/', '\\'], '_', $quotation->quotation_number) . '.pdf';

        return $pdf->download($fileName);
    }

    public function preview(Quotation $quotation)
    {
        $this->authorizeView($quotation);

        $pdf = $this->generatePdf($quotation);
        $fileName = str_replace(['/', '\\'], '_', $quotation->quotation_number) . '.pdf';

        return $pdf->stream($fileName);
    }

    protected function authorizeView(Quotation $quotation): void
    {
        abort_unless(auth()->check(), 401);
        abort_unless(auth()->user()->can('view', $quotation), 403, 'Unauthorized to view this quotation.');
    }

    protected function generatePdf(Quotation $quotation)
    {
        $quotation->load(['items', 'client', 'createdBy']);
        $company = CompanySetting::getSettings();

        return Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'company' => $company,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isPhpEnabled', true)
        ->setOption('isRemoteEnabled', true);
    }
}
