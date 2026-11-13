<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 items-start">
        @foreach ($this->stages as $stage)
            @php
                $stageProspects = $this->prospectsByStage[$stage->value] ?? collect();
                $totalValue = $stageProspects->sum('expected_value');
            @endphp
            <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-3 flex flex-col gap-3 min-h-[400px] border border-gray-200 dark:border-gray-700 shadow-sm">
                <!-- Column Header -->
                <div class="flex items-center justify-between border-b pb-2 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <x-filament::badge :color="$stage->getColor()">
                            {{ $stage->value }}
                        </x-filament::badge>
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            ({{ $stageProspects->count() }})
                        </span>
                    </div>
                </div>

                @if ($totalValue > 0)
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 px-1">
                        Total: <span class="font-bold text-primary-600 dark:text-primary-400">{{ \App\Support\IndianCurrencyHelper::formatRupees($totalValue) }}</span>
                    </div>
                @endif

                <!-- Cards Container -->
                <div class="flex flex-col gap-3 overflow-y-auto max-h-[70vh] pr-1">
                    @forelse ($stageProspects as $prospect)
                        <div class="bg-white dark:bg-gray-900 p-3.5 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-1">
                                <a href="{{ \App\Filament\Resources\ProspectResource::getUrl('view', ['record' => $prospect->id]) }}" class="font-semibold text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $prospect->contact_person }}
                                </a>
                                @if ($prospect->is_converted)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Converted
                                    </span>
                                @endif
                            </div>

                            @if ($prospect->company_name)
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate">
                                    🏢 {{ $prospect->company_name }}
                                </div>
                            @endif

                            <div class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 italic">
                                "{{ $prospect->requirement }}"
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 border-t dark:border-gray-800">
                                <span class="font-bold text-gray-900 dark:text-gray-100">
                                    {{ \App\Support\IndianCurrencyHelper::formatRupees($prospect->expected_value) }}
                                </span>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    📞 {{ $prospect->phone }}
                                </span>
                            </div>

                            @if ($prospect->next_follow_up_date)
                                <div class="text-[11px] flex items-center justify-between pt-1">
                                    <span class="text-gray-500">Follow-up:</span>
                                    <span class="font-medium {{ $prospect->next_follow_up_date->isPast() && !$prospect->next_follow_up_date->isToday() ? 'text-red-600 dark:text-red-400 font-bold' : ($prospect->next_follow_up_date->isToday() ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-gray-600') }}">
                                        {{ $prospect->next_follow_up_date->format('d-m-Y') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Quick Move Stage Selector -->
                            <div class="pt-2 flex items-center justify-between gap-1">
                                <label class="text-[10px] text-gray-400">Move:</label>
                                <select wire:change="updateStage({{ $prospect->id }}, $event.target.value)" class="text-xs py-1 px-1.5 rounded border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500">
                                    @foreach ($this->stages as $targetStage)
                                        <option value="{{ $targetStage->value }}" {{ $prospect->stage === $targetStage ? 'selected' : '' }}>
                                            {{ $targetStage->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-gray-400 italic">
                            No prospects
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
