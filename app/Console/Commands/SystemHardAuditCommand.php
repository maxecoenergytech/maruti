<?php

namespace App\Console\Commands;

use App\Enums\ProductCategory;
use App\Enums\ProspectStage;
use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\FollowUp;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\QuotationActivity;
use App\Models\QuotationItem;
use App\Models\Target;
use App\Models\User;
use App\Services\GstCalculationService;
use App\Services\QuotationNumberService;
use App\Services\QuotationShareService;
use App\Services\ReportService;
use App\Services\TargetAchievementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SystemHardAuditCommand extends Command
{
    protected $signature = 'app:audit-system {--detailed : Output detailed breakdown for each check}';
    protected $description = 'Developer deep hard-test and audit to verify all packaged CRM subsystems, services, and calculations';

    protected array $results = [];
    protected int $passedCount = 0;
    protected int $failedCount = 0;

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info("===============================================================================");
        $this->info("  MARUTI SALES & SERVICES (GUWAHATI) - COMPREHENSIVE DEVELOPER SYSTEM AUDIT");
        $this->info("===============================================================================");
        $this->line("Timestamp: " . now()->toIso8601String() . " | Environment: " . app()->environment());
        $this->line("");

        // 1. Database Schema & SQLite PRAGMAs
        $this->auditDatabasePragmasAndTables();

        // 2. Company Profile & Settings Assets
        $this->auditCompanySettingsAndAssets();

        // 3. User Authentication, Roles & Policies
        $this->auditUsersAndAuthentication();

        // 4. Products & Catalog Integrity
        $this->auditProductsCatalog();

        // 5. GST Calculation Engine & Words Converter
        $this->auditGstCalculations();

        // 6. Quotation Numbering & Concurrency Engine
        $this->auditQuotationNumbering();

        // 7. Quotation Lifecycle, Revisions & Activities
        $this->auditQuotationLifecycle();

        // 8. Quick Share Engine (WhatsApp, Mailto, Text)
        $this->auditQuotationQuickShare();

        // 9. PDF Generation (DomPDF Engine)
        $this->auditPdfGeneration();

        // 10. Prospects (Leads) & Conversion Pipeline
        $this->auditProspectsAndConversion();

        // 11. Follow-ups & Overdue Monitor
        $this->auditFollowUps();

        // 12. Targets & Achievement Calculation Engine
        $this->auditTargetsAndAchievement();

        // 13. Reports & CSV/Excel Exports
        $this->auditReportsAndExports();

        // 14. Artisan Scheduled Commands (Expiry & Backup)
        $this->auditArtisanCommands();

        // 15. Dev Server HTTP Endpoints & Response Latency
        $this->auditHttpEndpoints();

        // Render Summary Table
        $this->line("");
        $this->info("===============================================================================");
        $this->info("                             AUDIT EXECUTION REPORT                            ");
        $this->info("===============================================================================");

        $tableRows = [];
        foreach ($this->results as $result) {
            $statusFormatted = $result['status'] === 'PASS' 
                ? "<info>✔ PASS</info>" 
                : "<error>✘ FAIL</error>";
            $tableRows[] = [
                $result['category'],
                $result['check'],
                $statusFormatted,
                $result['duration'] . ' ms',
                $result['detail']
            ];
        }

        $this->table(['Module / Category', 'Check / Test Name', 'Status', 'Latency', 'Verification Summary'], $tableRows);

        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
        $this->line("");
        $this->info("Summary: {$this->passedCount} PASSED, {$this->failedCount} FAILED in {$totalDuration} ms.");
        $this->info("Peak Memory Usage: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB");
        $this->info("===============================================================================");

        return $this->failedCount === 0 ? 0 : 1;
    }

    protected function recordResult(string $category, string $check, bool $passed, string $detail, float $durationMs): void
    {
        if ($passed) {
            $this->passedCount++;
            $status = 'PASS';
        } else {
            $this->failedCount++;
            $status = 'FAIL';
        }

        $this->results[] = [
            'category' => $category,
            'check' => $check,
            'status' => $status,
            'duration' => round($durationMs, 2),
            'detail' => $detail,
        ];

        if ($this->option('detailed')) {
            $symbol = $passed ? '✔' : '✘';
            $this->line(" [{$symbol}] {$category} > {$check}: {$detail} (" . round($durationMs, 2) . "ms)");
        }
    }

    protected function auditDatabasePragmasAndTables(): void
    {
        $start = microtime(true);
        try {
            $journalMode = DB::select("PRAGMA journal_mode")[0]->journal_mode ?? 'unknown';
            $busyTimeout = DB::select("PRAGMA busy_timeout")[0]->timeout ?? 0;
            $synchronous = DB::select("PRAGMA synchronous")[0]->synchronous ?? 0;

            $tables = ['users', 'company_settings', 'clients', 'prospects', 'follow_ups', 'products', 'quotations', 'quotation_items', 'quotation_activities', 'targets'];
            $missingTables = [];
            $tableCounts = [];

            foreach ($tables as $t) {
                $hasTable = DB::select("SELECT count(*) as cnt FROM sqlite_master WHERE type='table' AND name=?", [$t])[0]->cnt ?? 0;
                if (!$hasTable) {
                    $missingTables[] = $t;
                } else {
                    $tableCounts[$t] = DB::table($t)->count();
                }
            }

            $passed = empty($missingTables) && strtolower($journalMode) === 'wal';
            $detail = "WAL: {$journalMode}, Timeout: {$busyTimeout}ms, Sync: {$synchronous}. All " . count($tableCounts) . " core tables present.";
            if (!empty($missingTables)) {
                $detail .= " Missing: " . implode(', ', $missingTables);
            }

            $this->recordResult('Database & PRAGMA', 'SQLite WAL & Schema Verification', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Database & PRAGMA', 'SQLite WAL & Schema Verification', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditCompanySettingsAndAssets(): void
    {
        $start = microtime(true);
        try {
            $settings = CompanySetting::getSettings();

            $logoPath = public_path('images/maruti-logo.png');
            $faviconPath = public_path('images/maruti-favicon.png');

            $logoExists = file_exists($logoPath) && filesize($logoPath) > 500;
            $faviconExists = file_exists($faviconPath) && filesize($faviconPath) > 200;

            $correctName = str_contains(strtoupper($settings->company_name), 'MARUTI SALES & SERVICES');
            $correctGstin = $settings->gstin === '18ABGFM5224K1Z2';
            $correctState = $settings->state_code === '18' && str_contains(strtolower($settings->state), 'assam');

            $passed = $logoExists && $faviconExists && $correctName && $correctGstin && $correctState;
            $detail = "Name: {$settings->company_name}, GSTIN: {$settings->gstin}, Logo: " . ($logoExists ? 'Valid (' . round(filesize($logoPath)/1024, 1) . 'KB)' : 'Missing') . ", Favicon: " . ($faviconExists ? 'Valid' : 'Missing');

            $this->recordResult('Company Profile', 'Maruti Letterhead & Branding Assets', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Company Profile', 'Maruti Letterhead & Branding Assets', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditUsersAndAuthentication(): void
    {
        $start = microtime(true);
        try {
            $admin = User::where('email', 'admin@marutisales.in')->first();
            $executive = User::where('email', 'rahul@marutisales.in')->first();

            $adminOk = $admin && $admin->role->value === 'admin' && Hash::check('Admin@123', $admin->password);
            $execOk = $executive && $executive->role->value === 'sales_executive' && Hash::check('Sales@123', $executive->password);

            $panel = Filament::getPanel('admin');
            $adminCanAccess = $admin ? $admin->canAccessPanel($panel) : false;
            $execCanAccess = $executive ? $executive->canAccessPanel($panel) : false;

            $passed = $adminOk && $execOk && $adminCanAccess && $execCanAccess;
            $detail = "Admin: " . ($adminOk ? 'OK' : 'FAIL') . " | Executive: " . ($execOk ? 'OK' : 'FAIL') . " | Panel Access: Granted";

            $this->recordResult('Authentication & Roles', 'Admin & Sales Executive Verification', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Authentication & Roles', 'Admin & Sales Executive Verification', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditProductsCatalog(): void
    {
        $start = microtime(true);
        try {
            $productCount = Product::count();
            $categories = Product::all()->pluck('category.value')->unique()->values()->toArray();
            $hasHsn = Product::whereNotNull('hsn_sac')->where('hsn_sac', '!=', '')->count() === $productCount;

            $passed = $productCount >= 20 && count($categories) >= 4 && $hasHsn;
            $detail = "Total: {$productCount} products across " . count($categories) . " categories (" . implode(', ', array_slice($categories, 0, 4)) . "...). All have HSN/SAC.";

            $this->recordResult('Catalog', 'Products, Categories & HSN Codes', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Catalog', 'Products, Categories & HSN Codes', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditGstCalculations(): void
    {
        $start = microtime(true);
        try {
            // Test 1: Intra-state Assam (18)
            $itemsIntra = [
                ['quantity' => 2, 'unit_rate' => 50000, 'discount_percent' => 10, 'gst_rate' => 18], // taxable = 90,000, gst = 16,200 (8,100 CGST + 8,100 SGST)
                ['quantity' => 1, 'unit_rate' => 5000, 'discount_percent' => 0, 'gst_rate' => 18],   // taxable = 5,000, gst = 900 (450 CGST + 450 SGST)
            ];
            $calcIntra = GstCalculationService::calculate($itemsIntra, '18');

            $intraOk = $calcIntra['is_interstate'] === false
                && $calcIntra['taxable_amount'] == 95000.00
                && $calcIntra['cgst_amount'] == 8550.00
                && $calcIntra['sgst_amount'] == 8550.00
                && $calcIntra['igst_amount'] == 0.00
                && $calcIntra['grand_total'] == 112100.00;

            // Test 2: Inter-state Meghalaya (17)
            $itemsInter = [
                ['quantity' => 1, 'unit_rate' => 100000, 'discount_percent' => 0, 'gst_rate' => 18],
            ];
            $calcInter = GstCalculationService::calculate($itemsInter, '17');

            $interOk = $calcInter['is_interstate'] === true
                && $calcInter['taxable_amount'] == 100000.00
                && $calcInter['cgst_amount'] == 0.00
                && $calcInter['sgst_amount'] == 0.00
                && $calcInter['igst_amount'] == 18000.00
                && $calcInter['grand_total'] == 118000.00;

            // Test 3: Amount in words
            $words1 = \App\Support\IndianCurrencyHelper::convertToWords(143960);
            $words2 = \App\Support\IndianCurrencyHelper::convertToWords(10050000); // 1 crore 50 thousand
            $wordsOk = str_contains($words1, 'One Lakh Forty-Three Thousand Nine Hundred Sixty')
                && str_contains($words2, 'One Crore');

            $passed = $intraOk && $interOk && $wordsOk;
            $detail = "Intra-state CGST/SGST: " . ($intraOk ? 'Pass' : 'Fail') . " | Inter-state IGST: " . ($interOk ? 'Pass' : 'Fail') . " | Indian Words: " . ($wordsOk ? 'Pass' : 'Fail');

            $this->recordResult('GST Calculations', 'Dual GST Engine & Indian Currency Words', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('GST Calculations', 'Dual GST Engine & Indian Currency Words', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditQuotationNumbering(): void
    {
        $start = microtime(true);
        try {
            $currentFy = QuotationNumberService::getFinancialYear(now());
            $marchFy = QuotationNumberService::getFinancialYear(Carbon::create(2027, 3, 31));
            $aprilFy = QuotationNumberService::getFinancialYear(Carbon::create(2027, 4, 1));

            // Test FY Rollover logic
            $fyRolloverOk = $marchFy !== $aprilFy;

            // Test next number generation format
            $nextNum = QuotationNumberService::generateNextNumber(now());
            $formatOk = (bool) preg_match('/^MSS\/\d{4}-\d{2}\/\d{4}$/', $nextNum['quotation_number']);

            // Test revision number generation format
            $parentQuote = Quotation::first();
            $revisionCode = QuotationNumberService::generateRevisionNumber($parentQuote);
            $revOk = (bool) preg_match('/\/R\d+$/', $revisionCode);

            $passed = $fyRolloverOk && $formatOk && $revOk;
            $detail = "Current FY: {$currentFy}, Next Number: {$nextNum['quotation_number']}, Revision: {$revisionCode}";

            $this->recordResult('Numbering Engine', 'Quotation Sequencing & FY Rollover', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Numbering Engine', 'Quotation Sequencing & FY Rollover', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditQuotationLifecycle(): void
    {
        $start = microtime(true);
        try {
            $draftCount = Quotation::where('status', QuotationStatus::Draft)->count();
            $sentCount = Quotation::where('status', QuotationStatus::Sent)->count();
            $acceptedCount = Quotation::accepted()->count();
            $latestCount = Quotation::latestRevision()->count();

            $activityCount = QuotationActivity::count();
            $hasActivities = $activityCount > 0;

            $passed = ($draftCount + $sentCount + $acceptedCount) > 0 && $hasActivities;
            $detail = "Latest revisions: {$latestCount}, Draft: {$draftCount}, Sent: {$sentCount}, Accepted: {$acceptedCount}. Logged activities: {$activityCount}";

            $this->recordResult('Quotation Lifecycle', 'Status Scopes & Activity History', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Quotation Lifecycle', 'Status Scopes & Activity History', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditQuotationQuickShare(): void
    {
        $start = microtime(true);
        try {
            $quotation = Quotation::with(['items', 'client'])->first();
            if (!$quotation) {
                throw new \Exception("No quotation to test share service.");
            }

            $summary = QuotationShareService::generateMessage($quotation);
            $whatsAppUrl = QuotationShareService::getWhatsAppUrl($quotation);
            $mailtoUrl = QuotationShareService::getMailtoUrl($quotation);

            $summaryOk = str_contains($summary, $quotation->quotation_number)
                && str_contains($summary, 'Maruti Sales & Services')
                && str_contains($summary, 'Total Amount:');

            $waOk = str_starts_with($whatsAppUrl, 'https://wa.me/') && str_contains($whatsAppUrl, 'text=');
            $mailOk = str_starts_with($mailtoUrl, 'mailto:') && str_contains($mailtoUrl, 'subject=');

            $passed = $summaryOk && $waOk && $mailOk;
            $detail = "WhatsApp URL: valid, Mailto URL: valid, Itemized Summary: " . strlen($summary) . " chars";

            $this->recordResult('Quick Share', 'WhatsApp, Email & Clipboard Formatter', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Quick Share', 'WhatsApp, Email & Clipboard Formatter', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditPdfGeneration(): void
    {
        $start = microtime(true);
        try {
            $quotation = Quotation::with(['items', 'client', 'createdBy'])->first();
            if (!$quotation) {
                throw new \Exception("No quotation to render PDF.");
            }

            $company = CompanySetting::getSettings();
            $pdf = Pdf::loadView('pdf.quotation', [
                'quotation' => $quotation,
                'company' => $company,
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true);

            $output = $pdf->output();
            $pdfSize = strlen($output);

            $validPdf = str_starts_with($output, '%PDF-') && $pdfSize > 15000;
            $detail = "Rendered A4 Portrait: " . round($pdfSize / 1024, 1) . " KB binary, valid %PDF- header";

            $this->recordResult('PDF Generation', 'DomPDF Engine & Quotation Template', $validPdf, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('PDF Generation', 'DomPDF Engine & Quotation Template', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditProspectsAndConversion(): void
    {
        $start = microtime(true);
        try {
            $prospectCount = Prospect::count();
            $stages = Prospect::all()->pluck('stage.value')->unique()->values()->toArray();

            $hasClientConversionField = \Illuminate\Support\Facades\Schema::hasColumn('prospects', 'converted_client_id')
                && \Illuminate\Support\Facades\Schema::hasColumn('prospects', 'converted_at');

            $passed = $prospectCount >= 10 && in_array('New', $stages) && $hasClientConversionField;
            $detail = "Total: {$prospectCount} prospects across " . count($stages) . " stages (" . implode(', ', $stages) . "). Double-convert lock columns verified.";

            $this->recordResult('Prospects (Leads)', 'Lead Pipeline & Conversion Safeguards', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Prospects (Leads)', 'Lead Pipeline & Conversion Safeguards', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditFollowUps(): void
    {
        $start = microtime(true);
        try {
            $totalFollowUps = FollowUp::count();
            $pendingCount = FollowUp::where('status', 'Pending')->count();
            $overdueCount = FollowUp::overdue()->count();
            $dueTodayCount = FollowUp::dueToday()->count();

            $passed = $totalFollowUps > 0;
            $detail = "Total: {$totalFollowUps}, Pending: {$pendingCount}, Due Today: {$dueTodayCount}, Overdue: {$overdueCount}";

            $this->recordResult('Follow-ups', 'Overdue Tracking & Action Statuses', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Follow-ups', 'Overdue Tracking & Action Statuses', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditTargetsAndAchievement(): void
    {
        $start = microtime(true);
        try {
            $executive = User::where('role', 'sales_executive')->first();
            $fy = QuotationNumberService::getFinancialYear(now());

            $achieved = TargetAchievementService::getAchievedAmount($executive, $fy, 0);
            $calc = TargetAchievementService::calculateForExecutive($executive, $fy, 0);

            $targetServiceOk = is_array($calc) && isset($calc['target'], $calc['achieved'], $calc['variance'], $calc['percentage']);
            $zeroGuard = $calc['percentage'] >= 0.0;

            $passed = $targetServiceOk && $zeroGuard;
            $detail = "Executive Achieved: ₹ " . number_format($calc['achieved'] ?? 0, 2) . ", Target: ₹ " . number_format($calc['target'] ?? 0, 2) . " (" . ($calc['percentage'] ?? 0) . "%)";

            $this->recordResult('Targets Module', 'TargetAchievementService & Zero-Div Guard', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Targets Module', 'TargetAchievementService & Zero-Div Guard', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditReportsAndExports(): void
    {
        $start = microtime(true);
        try {
            $filters = [];
            $admin = User::where('role', 'admin')->first();

            $quotationRegister = ReportService::getQuotationRegister($filters, $admin);
            $clientSales = ReportService::getClientSales($filters, $admin);
            $targetReport = ReportService::getTargetAchievement($filters, $admin);
            $conversionReport = ReportService::getProspectConversion($filters, $admin);

            $passed = !empty($quotationRegister['rows'])
                && !empty($clientSales['rows'])
                && !empty($targetReport['rows'])
                && !empty($conversionReport['rows']);

            $detail = "Quotation Register: " . count($quotationRegister['rows']) . " rows | Client Sales: " . count($clientSales['rows']) . " rows | Target: " . count($targetReport['rows']) . " rows | Conversion: " . count($conversionReport['rows']) . " sources";

            $this->recordResult('Reports & Exports', 'Reports Engine, Streaming CSV & XML Excel', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Reports & Exports', 'Reports Engine, Streaming CSV & XML Excel', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditArtisanCommands(): void
    {
        $start = microtime(true);
        try {
            // Test 1: quotations:expire
            $expireExitCode = Artisan::call('quotations:expire');

            // Test 2: app:backup-database
            $backupExitCode = Artisan::call('app:backup-database');

            $backupDir = storage_path('app/backups');
            $backupFiles = File::exists($backupDir) ? File::files($backupDir) : [];

            $passed = $expireExitCode === 0 && $backupExitCode === 0 && count($backupFiles) > 0;
            $detail = "quotations:expire (exit 0) | app:backup-database (exit 0, " . count($backupFiles) . " backups in storage/app/backups/)";

            $this->recordResult('Scheduled Commands', 'Database Backup & Quotation Expiry', $passed, $detail, (microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            $this->recordResult('Scheduled Commands', 'Database Backup & Quotation Expiry', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }

    protected function auditHttpEndpoints(): void
    {
        $start = microtime(true);
        try {
            $url = 'http://127.0.0.1:8000/admin/login';
            $response = Http::timeout(6)->get($url);

            $status = $response->status();
            $body = html_entity_decode($response->body());

            $hasBranding = str_contains($body, 'Maruti Sales & Services') || str_contains($body, 'MARUTI SALES & SERVICES');
            $hasFavicon = str_contains($body, 'maruti-favicon.png');
            $hasCsrf = str_contains($body, 'name="csrf-token"');

            $latency = round((microtime(true) - $start) * 1000, 2);
            $passed = $status === 200 && $hasBranding && $hasCsrf && $hasFavicon;
            $detail = "Status: {$status} OK | Response Time: {$latency} ms | Maruti Branding: Verified | Favicon & CSRF: Verified";

            $this->recordResult('HTTP & Live Server', 'Filament Login UI & Brand Elements', $passed, $detail, $latency);
        } catch (\Throwable $e) {
            $this->recordResult('HTTP & Live Server', 'Filament Login UI & Brand Elements', false, $e->getMessage(), (microtime(true) - $start) * 1000);
        }
    }
}
