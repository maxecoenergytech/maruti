<?php

namespace App\Filament\Pages;

use App\Models\CompanySetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageCompanySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings & Admin';

    protected static ?string $navigationLabel = 'Company Settings';

    protected static ?string $title = 'Company Profile & Letterhead Settings';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-company-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $settings = CompanySetting::getSettings();
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SettingsTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General & Letterhead')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\TextInput::make('company_name')
                                    ->label('Company / Firm Name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('tagline')
                                    ->label('Tagline')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone Numbers')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('email')
                                    ->label('Official Email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('website')
                                    ->label('Website URL')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('quotation_prefix')
                                    ->label('Quotation Prefix')
                                    ->default('MSS')
                                    ->required()
                                    ->maxLength(10),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('GST & Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\TextInput::make('gstin')
                                    ->label('Company GSTIN')
                                    ->maxLength(15),
                                Forms\Components\TextInput::make('pan')
                                    ->label('Company PAN')
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('state')
                                    ->label('Home State')
                                    ->default('Assam')
                                    ->required(),
                                Forms\Components\TextInput::make('state_code')
                                    ->label('Home State Code')
                                    ->default('18')
                                    ->required()
                                    ->length(2),
                                Forms\Components\TextInput::make('city')
                                    ->label('City')
                                    ->default('Guwahati')
                                    ->required(),
                                Forms\Components\TextInput::make('pincode')
                                    ->label('PIN Code')
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('address_line1')
                                    ->label('Address Line 1')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('address_line2')
                                    ->label('Address Line 2')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Bank Details')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('bank_account_name')
                                    ->label('Beneficiary / Account Name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('bank_account_no')
                                    ->label('Account Number')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('bank_ifsc')
                                    ->label('IFSC Code')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('bank_branch')
                                    ->label('Branch Name')
                                    ->maxLength(255),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('PDF & Print Layout')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('signatory_title')
                                    ->label('Signatory Title')
                                    ->default('Authorized Signatory')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('jurisdiction_text')
                                    ->label('Jurisdiction Note')
                                    ->default('Subject to Guwahati jurisdiction')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('show_signature_on_pdf')
                                    ->label('Display Digital Signature on Quotation PDF')
                                    ->default(false),
                                Forms\Components\Toggle::make('show_computer_generated_note')
                                    ->label('Display "This is a computer-generated quotation" notice')
                                    ->default(false),
                                Forms\Components\Textarea::make('quotation_default_terms')
                                    ->label('Default Terms & Conditions')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $settings = CompanySetting::first() ?? new CompanySetting();
        $settings->fill($state);
        $settings->save();

        Notification::make()
            ->title('Settings Saved Successfully')
            ->success()
            ->send();
    }
}
