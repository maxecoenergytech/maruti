<x-filament-panels::page>
    <div class="max-w-xl mx-auto space-y-6">
        <form wire:submit="updatePassword" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end gap-3">
                <x-filament::button type="submit" size="lg" color="primary">
                    Update Password & Continue
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
