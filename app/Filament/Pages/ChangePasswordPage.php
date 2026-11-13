<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $title = 'Change Password Required';

    protected static ?string $slug = 'change-password';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.change-password-page';

    public ?string $current_password = '';
    public ?string $new_password = '';
    public ?string $new_password_confirmation = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Security Notice')
                    ->description('For security, you must update your password before accessing the system.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current / Default Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->currentPassword(),

                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::min(8)->letters()->numbers())
                            ->same('new_password_confirmation')
                            ->helperText('Minimum 8 characters with letters and numbers.'),

                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->revealable()
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }

    public function updatePassword(): void
    {
        $this->validate();

        $user = auth()->user();
        $user->password = Hash::make($this->new_password);
        $user->must_change_password = false;
        $user->save();

        Notification::make()
            ->title('Password Updated')
            ->body('Your password has been changed successfully.')
            ->success()
            ->send();

        $this->redirect('/admin');
    }
}
