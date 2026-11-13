<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        {{ $this->form }}

        @php
            $data = $this->reportData;
            $rows = $data['rows'] ?? [];
            $totals = $data['totals'] ?? [];
        @endphp

        <!-- Report Content Card -->
        <x-filament::section>
            <x-slot name="heading">
                @switch($report_type)
                    @case('client_sales')
                        Client-wise Sales Summary
                        @break
                    @case('target_achievement')
                        Executive Target Achievement Report
                        @break
                    @case('prospect_conversion')
                        Prospect Conversion & Win Rate Report
                        @break
                    @default
                        Quotation Register
                @endswitch
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex items-center gap-2">
                    <x-filament::button
                        size="sm"
                        color="success"
                        icon="heroicon-o-arrow-down-tray"
                        tag="a"
                        :href="route('reports.export.csv', $this->getFilterParams())"
                        target="_blank">
                        CSV
                    </x-filament::button>

                    <x-filament::button
                        size="sm"
                        color="primary"
                        icon="heroicon-o-table-cells"
                        tag="a"
                        :href="route('reports.export.excel', $this->getFilterParams())"
                        target="_blank">
                        Excel
                    </x-filament::button>
                </div>
            </x-slot>

            @if(empty($rows))
                <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                    <p class="text-base font-medium">No records found for the selected filters.</p>
                    <p class="text-xs text-gray-400 mt-1">Try widening the date range or adjusting the filters above.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    {{-- 1. Quotation Register --}}
                    @if($report_type === 'quotation_register')
                        <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-semibold text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2.5">Quotation No</th>
                                    <th class="px-3 py-2.5">Date</th>
                                    <th class="px-3 py-2.5">Client</th>
                                    <th class="px-3 py-2.5">Executive</th>
                                    <th class="px-3 py-2.5">Status</th>
                                    <th class="px-3 py-2.5 text-right">Taxable (₹)</th>
                                    <th class="px-3 py-2.5 text-right">Tax (₹)</th>
                                    <th class="px-3 py-2.5 text-right">Grand Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                        <td class="px-3 py-2 font-semibold text-primary-600 dark:text-primary-400">{{ $r['number'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['date'] }}</td>
                                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $r['client'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['executive'] }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                                @if($r['status'] === 'Accepted') bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400
                                                @elseif($r['status'] === 'Sent') bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-400
                                                @elseif($r['status'] === 'Under Negotiation') bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400
                                                @elseif($r['status'] === 'Rejected') bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-400
                                                @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 @endif">
                                                {{ $r['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['taxable']) }}</td>
                                        <td class="px-3 py-2 text-right text-gray-500">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['tax']) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-gray-100">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['grand_total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-800/80 font-bold border-t-2 border-gray-300 dark:border-gray-700">
                                <tr>
                                    <td class="px-3 py-3" colspan="2">TOTAL ({{ $totals['count'] }} Quotes)</td>
                                    <td class="px-3 py-3" colspan="3"></td>
                                    <td class="px-3 py-3 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['taxable']) }}</td>
                                    <td class="px-3 py-3 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['tax']) }}</td>
                                    <td class="px-3 py-3 text-right text-base text-primary-700 dark:text-primary-400">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['grand_total']) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                    {{-- 2. Client-wise Sales --}}
                    @elseif($report_type === 'client_sales')
                        <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-semibold text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2.5">Client Name</th>
                                    <th class="px-3 py-2.5">State</th>
                                    <th class="px-3 py-2.5 text-center">Accepted Quotations</th>
                                    <th class="px-3 py-2.5 text-right">Total Won Sales (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                        <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ $r['client_name'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-300">{{ $r['state'] }}</td>
                                        <td class="px-3 py-2 text-center font-medium">{{ $r['accepted_count'] }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-emerald-700 dark:text-emerald-400">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['total_value']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-800/80 font-bold border-t-2 border-gray-300 dark:border-gray-700">
                                <tr>
                                    <td class="px-3 py-3">TOTAL ({{ $totals['client_count'] }} Clients)</td>
                                    <td class="px-3 py-3"></td>
                                    <td class="px-3 py-3 text-center">{{ $totals['total_quotes'] }} Deals</td>
                                    <td class="px-3 py-3 text-right text-base text-emerald-700 dark:text-emerald-400">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['total_sales']) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                    {{-- 3. Executive Target Achievement --}}
                    @elseif($report_type === 'target_achievement')
                        <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-semibold text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2.5">Sales Executive</th>
                                    <th class="px-3 py-2.5 text-right">Target (₹)</th>
                                    <th class="px-3 py-2.5 text-right">Achieved (₹)</th>
                                    <th class="px-3 py-2.5 text-right">Variance (₹)</th>
                                    <th class="px-3 py-2.5 text-center">Achievement %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                        <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ $r['executive'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['target']) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-gray-100">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['achieved']) }}</td>
                                        <td class="px-3 py-2 text-right font-medium {{ $r['variance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ ($r['variance'] >= 0 ? '+ ' : '- ') . \App\Support\IndianCurrencyHelper::formatRupees(abs($r['variance'])) }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                                @if($r['percentage'] >= 100) bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                                @elseif($r['percentage'] >= 50) bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300
                                                @else bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 @endif">
                                                {{ number_format($r['percentage'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-800/80 font-bold border-t-2 border-gray-300 dark:border-gray-700">
                                <tr>
                                    <td class="px-3 py-3">FIRM TOTAL</td>
                                    <td class="px-3 py-3 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['target']) }}</td>
                                    <td class="px-3 py-3 text-right">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['achieved']) }}</td>
                                    <td class="px-3 py-3 text-right {{ $totals['variance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ ($totals['variance'] >= 0 ? '+ ' : '- ') . \App\Support\IndianCurrencyHelper::formatRupees(abs($totals['variance'])) }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-base font-bold text-primary-700 dark:text-primary-400">
                                        {{ number_format($totals['percentage'], 1) }}%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                    {{-- 4. Prospect Conversion --}}
                    @elseif($report_type === 'prospect_conversion')
                        <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-xs uppercase font-semibold text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-3 py-2.5">Lead Source</th>
                                    <th class="px-3 py-2.5 text-center">Total Leads</th>
                                    <th class="px-3 py-2.5 text-center text-emerald-600">Won</th>
                                    <th class="px-3 py-2.5 text-center text-rose-600">Lost</th>
                                    <th class="px-3 py-2.5 text-center text-blue-600">Open</th>
                                    <th class="px-3 py-2.5 text-center">Win Rate %</th>
                                    <th class="px-3 py-2.5 text-right">Pipeline Expected Value (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal">
                                @foreach($rows as $r)
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                                        <td class="px-3 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ $r['source'] }}</td>
                                        <td class="px-3 py-2 text-center font-medium">{{ $r['total_leads'] }}</td>
                                        <td class="px-3 py-2 text-center text-emerald-700 dark:text-emerald-400 font-semibold">{{ $r['won'] }}</td>
                                        <td class="px-3 py-2 text-center text-rose-600 dark:text-rose-400">{{ $r['lost'] }}</td>
                                        <td class="px-3 py-2 text-center text-blue-600 dark:text-blue-400 font-medium">{{ $r['open'] }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                                @if($r['win_rate'] >= 50) bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400
                                                @elseif($r['win_rate'] >= 25) bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400
                                                @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 @endif">
                                                {{ number_format($r['win_rate'], 1) }}%
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-gray-100">{{ \App\Support\IndianCurrencyHelper::formatRupees($r['pipeline_value']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-800/80 font-bold border-t-2 border-gray-300 dark:border-gray-700">
                                <tr>
                                    <td class="px-3 py-3">TOTAL SUMMARY</td>
                                    <td class="px-3 py-3 text-center">{{ $totals['total_leads'] }} Leads</td>
                                    <td class="px-3 py-3 text-center text-emerald-700">{{ $totals['won'] }}</td>
                                    <td class="px-3 py-3 text-center text-rose-600">{{ $totals['lost'] }}</td>
                                    <td class="px-3 py-3 text-center text-blue-600">{{ $totals['open'] }}</td>
                                    <td class="px-3 py-3 text-center text-base text-primary-700 dark:text-primary-400">{{ number_format($totals['win_rate'], 1) }}%</td>
                                    <td class="px-3 py-3 text-right text-base text-gray-900 dark:text-gray-100">{{ \App\Support\IndianCurrencyHelper::formatRupees($totals['pipeline_value']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @endif
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
