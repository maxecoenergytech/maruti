<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex p-1.5 rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                            <x-heroicon-m-arrow-trending-up class="w-5 h-5" />
                        </span>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Monthly Sales Target Progress &mdash; {{ $monthName }}
                        </h3>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Scope: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $scopeLabel }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider {{ $textClass }} bg-gray-100 dark:bg-gray-800">
                        {{ $statusLabel }}
                    </span>
                    <span class="text-lg font-bold {{ $textClass }}">
                        {{ number_format($percentage, 1) }}%
                    </span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-1">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="h-2.5 rounded-full {{ $barClass }} transition-all duration-700 ease-out shadow-sm"
                         style="width: {{ $displayPercent }}%;"></div>
                </div>
                <div class="flex justify-between text-[11px] text-gray-400 dark:text-gray-500 px-0.5">
                    <span>₹ 0</span>
                    <span>Target: {{ $targetFormatted }}</span>
                </div>
            </div>

            <!-- Metrics Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Monthly Target</div>
                    <div class="text-base font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $targetFormatted }}</div>
                </div>

                <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Won Value (Accepted)</div>
                    <div class="text-base font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $achievedFormatted }}</div>
                </div>

                <div class="p-2.5 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Variance (Surplus / Shortfall)</div>
                    <div class="text-base font-bold {{ $textClass }} mt-0.5">{{ $varianceFormatted }}</div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
