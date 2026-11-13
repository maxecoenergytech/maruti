<?php

namespace App\Filament\Pages;

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use App\Services\QuotationNumberService;
use App\Services\ReportService;
use App\Support\IndianCurrencyHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Reports & Analytics';

    protected static ?string $title = 'Business Reports & Analytics';

    protected static ?string $slug = 'reports';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.reports-page';

    public ?string $report_type = 'quotation_register';
    public ?string $date_from = '2026-04-01';
    public ?string $date_to = '2027-03-31';
    public ?int $user_id = null;
    public ?int $client_id = null;
    public ?string $status = null;
    public bool $include_all_revisions = false;
    public ?string $financial_year = '2026-27';

    public function mount(): void
    {
        $this->financial_year = QuotationNumberService::getFinancialYear();
        $this->form->fill([
            'report_type' => 'quotation_register',
            'date_from' => '2026-04-01',
            'date_to' => '2027-03-31',
            'financial_year' => $this->financial_year,
            'include_all_revisions' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Type & Filters')
                    ->schema([
                        Forms\Components\Select::make('report_type')
                            ->label('Select Report')
                            ->options([
                                'quotation_register' => '1. Quotation Register',
                                'client_sales' => '2. Client-wise Sales Summary',
                                'target_achievement' => '3. Executive Target Achievement',
                                'prospect_conversion' => '4. Prospect Conversion & Pipeline',
                            ])
                            ->live()
                            ->required()
                            ->columnSpan(['sm' => 2, 'md' => 4]),

                        Forms\Components\DatePicker::make('date_from')
                            ->label('From Date')
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),

                        Forms\Components\DatePicker::make('date_to')
                            ->label('To Date')
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),

                        Forms\Components\Select::make('user_id')
                            ->label('Executive')
                            ->options(
                                User::where('role', UserRole::SalesExecutive)
                                    ->orWhere('role', UserRole::Admin)
                                    ->pluck('name', 'id')
                            )
                            ->placeholder('All Executives')
                            ->visible(fn () => auth()->user()?->isAdmin() ?? false)
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),

                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->options(Client::visibleTo(auth()->user())->pluck('company_name', 'id'))
                            ->placeholder('All Clients')
                            ->searchable()
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),

                        Forms\Components\Select::make('status')
                            ->label('Quotation Status')
                            ->options(QuotationStatus::class)
                            ->placeholder('All Statuses')
                            ->visible(fn () => $this->report_type === 'quotation_register')
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),

                        Forms\Components\Toggle::make('include_all_revisions')
                            ->label('Show All Revisions')
                            ->helperText('Include superseded /R1 revisions')
                            ->visible(fn () => $this->report_type === 'quotation_register')
                            ->live()
                            ->columnSpan(['sm' => 1, 'md' => 2]),
                    ])
                    ->columns(['sm' => 2, 'md' => 4]),
            ]);
    }

    public function getFilterParams(): array
    {
        return array_filter([
            'type' => $this->report_type,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'status' => $this->status,
            'include_all_revisions' => $this->include_all_revisions ? 1 : 0,
            'financial_year' => $this->financial_year,
        ]);
    }

    public function getReportDataProperty(): array
    {
        $filters = $this->getFilterParams();

        return match ($this->report_type) {
            'client_sales' => ReportService::getClientSales($filters),
            'target_achievement' => ReportService::getTargetAchievement($filters),
            'prospect_conversion' => ReportService::getProspectConversion($filters),
            default => ReportService::getQuotationRegister($filters),
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_csv')
                ->label('Export to CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('reports.export.csv', $this->getFilterParams()))
                ->openUrlInNewTab(),

            Actions\Action::make('export_excel')
                ->label('Export to Excel')
                ->icon('heroicon-o-table-cells')
                ->color('primary')
                ->url(fn () => route('reports.export.excel', $this->getFilterParams()))
                ->openUrlInNewTab(),
        ];
    }
}
