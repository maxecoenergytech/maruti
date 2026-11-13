<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorizeAccess();

        $type = $request->query('type', 'quotation_register');
        $filters = $request->all();
        $user = auth()->user();

        $fileName = "maruti_{$type}_" . now()->format('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($type, $filters, $user) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            match ($type) {
                'quotation_register' => $this->writeQuotationRegisterCsv($handle, $filters, $user),
                'client_sales' => $this->writeClientSalesCsv($handle, $filters, $user),
                'target_achievement' => $this->writeTargetAchievementCsv($handle, $filters, $user),
                'prospect_conversion' => $this->writeProspectConversionCsv($handle, $filters, $user),
                default => null,
            };

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorizeAccess();

        $type = $request->query('type', 'quotation_register');
        $filters = $request->all();
        $user = auth()->user();

        $fileName = "maruti_{$type}_" . now()->format('Ymd_His') . ".xls";

        return response()->streamDownload(function () use ($type, $filters, $user) {
            match ($type) {
                'quotation_register' => $this->writeQuotationRegisterXml($filters, $user),
                'client_sales' => $this->writeClientSalesXml($filters, $user),
                'target_achievement' => $this->writeTargetAchievementXml($filters, $user),
                'prospect_conversion' => $this->writeProspectConversionXml($filters, $user),
                default => null,
            };
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->check(), 401);
        abort_unless(auth()->user()->is_active, 403);
    }

    /* -------------------------------------------------------------------------- */
    /* CSV Writers                                                                */
    /* -------------------------------------------------------------------------- */

    protected function writeQuotationRegisterCsv($handle, array $filters, $user): void
    {
        $data = ReportService::getQuotationRegister($filters, $user);

        fputcsv($handle, ['Quotation Number', 'Date', 'Client', 'State', 'Executive', 'Status', 'Taxable (INR)', 'Tax (INR)', 'Grand Total (INR)']);

        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['number'],
                $row['date'],
                $row['client'],
                $row['state'],
                $row['executive'],
                $row['status'],
                number_format($row['taxable'], 2, '.', ''),
                number_format($row['tax'], 2, '.', ''),
                number_format($row['grand_total'], 2, '.', ''),
            ]);
        }

        fputcsv($handle, [
            'TOTALS',
            "{$data['totals']['count']} Quotations",
            '',
            '',
            '',
            '',
            number_format($data['totals']['taxable'], 2, '.', ''),
            number_format($data['totals']['tax'], 2, '.', ''),
            number_format($data['totals']['grand_total'], 2, '.', ''),
        ]);
    }

    protected function writeClientSalesCsv($handle, array $filters, $user): void
    {
        $data = ReportService::getClientSales($filters, $user);

        fputcsv($handle, ['Client Name', 'State', 'Accepted Quotations Count', 'Total Sales Value (INR)']);

        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['client_name'],
                $row['state'],
                $row['accepted_count'],
                number_format($row['total_value'], 2, '.', ''),
            ]);
        }

        fputcsv($handle, [
            'TOTALS',
            "{$data['totals']['client_count']} Clients",
            $data['totals']['total_quotes'],
            number_format($data['totals']['total_sales'], 2, '.', ''),
        ]);
    }

    protected function writeTargetAchievementCsv($handle, array $filters, $user): void
    {
        $data = ReportService::getTargetAchievement($filters, $user);

        fputcsv($handle, ['Sales Executive', 'Email', 'Target (INR)', 'Achieved (INR)', 'Variance (INR)', 'Percentage (%)']);

        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['executive'],
                $row['email'],
                number_format($row['target'], 2, '.', ''),
                number_format($row['achieved'], 2, '.', ''),
                number_format($row['variance'], 2, '.', ''),
                number_format($row['percentage'], 2, '.', '') . '%',
            ]);
        }

        fputcsv($handle, [
            'TOTALS',
            '',
            number_format($data['totals']['target'], 2, '.', ''),
            number_format($data['totals']['achieved'], 2, '.', ''),
            number_format($data['totals']['variance'], 2, '.', ''),
            number_format($data['totals']['percentage'], 2, '.', '') . '%',
        ]);
    }

    protected function writeProspectConversionCsv($handle, array $filters, $user): void
    {
        $data = ReportService::getProspectConversion($filters, $user);

        fputcsv($handle, ['Lead Source', 'Total Leads', 'Won Deals', 'Lost Deals', 'Open Pipeline', 'Win Rate (%)', 'Pipeline Value (INR)']);

        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['source'],
                $row['total_leads'],
                $row['won'],
                $row['lost'],
                $row['open'],
                number_format($row['win_rate'], 1, '.', '') . '%',
                number_format($row['pipeline_value'], 2, '.', ''),
            ]);
        }

        fputcsv($handle, [
            'TOTALS',
            $data['totals']['total_leads'],
            $data['totals']['won'],
            $data['totals']['lost'],
            $data['totals']['open'],
            number_format($data['totals']['win_rate'], 1, '.', '') . '%',
            number_format($data['totals']['pipeline_value'], 2, '.', ''),
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /* XML / Excel Writers (Native Excel XML with Number formatting)              */
    /* -------------------------------------------------------------------------- */

    protected function writeQuotationRegisterXml(array $filters, $user): void
    {
        $data = ReportService::getQuotationRegister($filters, $user);
        $this->outputXmlHeader('Quotation Register');

        echo '<Row ss:StyleID="Header">';
        foreach (['Quotation Number', 'Date', 'Client', 'State', 'Executive', 'Status', 'Taxable (INR)', 'Tax (INR)', 'Grand Total (INR)'] as $h) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($data['rows'] as $r) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['number']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['date']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['client']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['state']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['executive']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['status']) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['taxable'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['tax'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['grand_total'] . '</Data></Cell>';
            echo '</Row>';
        }

        // Totals
        echo '<Row ss:StyleID="Total">';
        echo '<Cell><Data ss:Type="String">TOTALS</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $data['totals']['count'] . ' Quotes</Data></Cell>';
        echo '<Cell><Data ss:Type="String"></Data></Cell>';
        echo '<Cell><Data ss:Type="String"></Data></Cell>';
        echo '<Cell><Data ss:Type="String"></Data></Cell>';
        echo '<Cell><Data ss:Type="String"></Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['taxable'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['tax'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['grand_total'] . '</Data></Cell>';
        echo '</Row>';

        $this->outputXmlFooter();
    }

    protected function writeClientSalesXml(array $filters, $user): void
    {
        $data = ReportService::getClientSales($filters, $user);
        $this->outputXmlHeader('Client Sales');

        echo '<Row ss:StyleID="Header">';
        foreach (['Client Name', 'State', 'Accepted Count', 'Total Sales Value (INR)'] as $h) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($data['rows'] as $r) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['client_name']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['state']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $r['accepted_count'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['total_value'] . '</Data></Cell>';
            echo '</Row>';
        }

        // Totals
        echo '<Row ss:StyleID="Total">';
        echo '<Cell><Data ss:Type="String">TOTALS</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . $data['totals']['client_count'] . ' Clients</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $data['totals']['total_quotes'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['total_sales'] . '</Data></Cell>';
        echo '</Row>';

        $this->outputXmlFooter();
    }

    protected function writeTargetAchievementXml(array $filters, $user): void
    {
        $data = ReportService::getTargetAchievement($filters, $user);
        $this->outputXmlHeader('Target Achievement');

        echo '<Row ss:StyleID="Header">';
        foreach (['Sales Executive', 'Email', 'Target (INR)', 'Achieved (INR)', 'Variance (INR)', 'Percentage (%)'] as $h) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($data['rows'] as $r) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['executive']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['email']) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['target'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['achieved'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['variance'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Percent"><Data ss:Type="Number">' . ($r['percentage'] / 100) . '</Data></Cell>';
            echo '</Row>';
        }

        // Totals
        echo '<Row ss:StyleID="Total">';
        echo '<Cell><Data ss:Type="String">TOTALS</Data></Cell>';
        echo '<Cell><Data ss:Type="String"></Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['target'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['achieved'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['variance'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Percent"><Data ss:Type="Number">' . ($data['totals']['percentage'] / 100) . '</Data></Cell>';
        echo '</Row>';

        $this->outputXmlFooter();
    }

    protected function writeProspectConversionXml(array $filters, $user): void
    {
        $data = ReportService::getProspectConversion($filters, $user);
        $this->outputXmlHeader('Prospect Conversion');

        echo '<Row ss:StyleID="Header">';
        foreach (['Lead Source', 'Total Leads', 'Won Deals', 'Lost Deals', 'Open Pipeline', 'Win Rate (%)', 'Pipeline Value (INR)'] as $h) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($data['rows'] as $r) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($r['source']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $r['total_leads'] . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $r['won'] . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $r['lost'] . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $r['open'] . '</Data></Cell>';
            echo '<Cell ss:StyleID="Percent"><Data ss:Type="Number">' . ($r['win_rate'] / 100) . '</Data></Cell>';
            echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $r['pipeline_value'] . '</Data></Cell>';
            echo '</Row>';
        }

        // Totals
        echo '<Row ss:StyleID="Total">';
        echo '<Cell><Data ss:Type="String">TOTALS</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $data['totals']['total_leads'] . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $data['totals']['won'] . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $data['totals']['lost'] . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $data['totals']['open'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="Percent"><Data ss:Type="Number">' . ($data['totals']['win_rate'] / 100) . '</Data></Cell>';
        echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $data['totals']['pipeline_value'] . '</Data></Cell>';
        echo '</Row>';

        $this->outputXmlFooter();
    }

    protected function outputXmlHeader(string $sheetName): void
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo '<Styles>' . "\n";
        echo '<Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Bottom"/><Font ss:FontName="Calibri" ss:Size="11"/></Style>' . "\n";
        echo '<Style ss:ID="Header"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1E3A8A" ss:Pattern="Solid"/></Style>' . "\n";
        echo '<Style ss:ID="Total"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/><Interior ss:Color="#E2E8F0" ss:Pattern="Solid"/></Style>' . "\n";
        echo '<Style ss:ID="Currency"><NumberFormat ss:Format="#,##0.00"/></Style>' . "\n";
        echo '<Style ss:ID="Percent"><NumberFormat ss:Format="0.0%"/></Style>' . "\n";
        echo '</Styles>' . "\n";
        echo '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">' . "\n";
        echo '<Table>' . "\n";
    }

    protected function outputXmlFooter(): void
    {
        echo '</Table>' . "\n";
        echo '</Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
    }
}
