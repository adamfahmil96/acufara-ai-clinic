<x-filament-panels::page>

    {{-- Filter Controls --}}
    <x-filament::section>
        <x-slot name="heading">
            Filter Periode
        </x-slot>
        <x-slot name="description">
            Pilih bulan, tahun, dan cabang lalu tekan tombol Terapkan.
        </x-slot>

        <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem;">
            <div style="flex: 1; min-width: 120px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Bulan</label>
                <select
                    wire:model="selectedMonth"
                    style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: #fff; color: #111827;"
                >
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 80px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Tahun</label>
                <input
                    type="number"
                    wire:model="selectedYear"
                    min="2020"
                    max="2099"
                    style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: #fff; color: #111827;"
                />
            </div>

            <div style="flex: 1; min-width: 140px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Cabang</label>
                <select
                    wire:model="selectedBranchId"
                    style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; font-size: 0.875rem; background-color: #fff; color: #111827;"
                >
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $id => $name)
                        <option value="{{ $id }}" {{ $selectedBranchId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{ loading: false }">
                <button
                    wire:click="applyFilters"
                    x-on:click="loading = true; $nextTick(() => setTimeout(() => loading = false, 3000))"
                    type="button"
                    :style="'display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.5rem; background-color: #87A878; padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 600; color: #fff; cursor: pointer; border: none; white-space: nowrap; opacity: ' + (loading ? '0.7' : '1') + '; cursor: ' + (loading ? 'not-allowed' : 'pointer') + ';'"
                    :disabled="loading"
                >
                    <span x-show="!loading" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 1rem; height: 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Terapkan
                    </span>
                    <span x-show="loading" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 1rem; height: 1rem; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </div>
    </x-filament::section>

    {{-- Stats Cards --}}
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan — {{ $selectedMonthName }} {{ $selectedYear }}
        </x-slot>
        <x-slot name="description">
            Statistik janji temu dan pendapatan pada periode yang dipilih.
        </x-slot>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
            {{-- Total Janji Temu --}}
            <div style="border-radius: 0.75rem; padding: 0.875rem; background-color: #eaf4f1; border: 1px solid #d5e8e0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: rgba(135,168,120,0.15); color: #87A878; flex-shrink: 0;">
                        <x-heroicon-o-calendar-days style="width:16px;height:16px" />
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Janji Temu</span>
                </div>
                <div style="font-size: 1.375rem; font-weight: 700; color: #111827;">{{ $totalAppointments }}</div>
            </div>

            {{-- Selesai --}}
            <div style="border-radius: 0.75rem; padding: 0.875rem; background-color: #ecfdf5; border: 1px solid #d1fae5;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: rgba(16,185,129,0.15); color: #059669; flex-shrink: 0;">
                        <x-heroicon-o-check-circle style="width:16px;height:16px" />
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Selesai</span>
                </div>
                <div style="font-size: 1.375rem; font-weight: 700; color: #111827;">{{ $completedAppointments }}</div>
                <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.125rem;">dari {{ $totalAppointments }} janji temu</div>
            </div>

            {{-- Pendapatan --}}
            <div style="border-radius: 0.75rem; padding: 0.875rem; background-color: #fffbeb; border: 1px solid #fef3c7;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: rgba(245,158,11,0.15); color: #d97706; flex-shrink: 0;">
                        <x-heroicon-o-banknotes style="width:16px;height:16px" />
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Pendapatan</span>
                </div>
                <div style="font-size: 1.375rem; font-weight: 700; color: #111827;">
                    {{ $isDemo ? 'Rp ***' : 'Rp ' . number_format($revenue, 0, ',', '.') }}
                </div>
            </div>

            {{-- Pasien Baru --}}
            <div style="border-radius: 0.75rem; padding: 0.875rem; background-color: #f5f3ff; border: 1px solid #ede9fe;">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background-color: rgba(139,92,246,0.15); color: #7c3aed; flex-shrink: 0;">
                        <x-heroicon-o-user-group style="width:16px;height:16px" />
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Pasien Baru</span>
                </div>
                <div style="font-size: 1.375rem; font-weight: 700; color: #111827;">{{ $newPatients }}</div>
                <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.125rem;">terdaftar bulan ini</div>
            </div>
        </div>
    </x-filament::section>

    {{-- Tren Pendapatan 6 Bulan --}}
    <x-filament::section>
        <x-slot name="heading">
            Tren Pendapatan 6 Bulan Terakhir
        </x-slot>
        <x-slot name="description">
            Perbandingan pendapatan bulanan dari jadwal yang sudah selesai.
        </x-slot>

        @php
            $maxRev = $trendData->max('revenue') ?: 1;
            $chartHeight = 140;
            $barColors = [
                '#a7c4a0', // 5 months ago — sage mist
                '#8db587', // 4 months ago — soft sage
                '#73a66d', // 3 months ago — medium sage
                '#5c9a56', // 2 months ago — deep sage
                '#4a8c44', // 1 month ago — forest sage
                '#3a7a35', // current month — accent green
            ];
        @endphp

        <div style="margin-top: 0.5rem;" x-data="{ hoverIndex: null }">
            <div style="display: flex; align-items: flex-end; gap: 0.375rem; height: {{ $chartHeight }}px; padding: 0 0.125rem;">
                @foreach ($trendData as $index => $trend)
                    @php
                        $barHeight = $maxRev > 0 ? ($trend['revenue'] / $maxRev) * $chartHeight : 0;
                        $barHeight = max($barHeight, 4);
                        $isCurrentMonth = $index === 5;
                        $barColor = $barColors[$index] ?? '#87A878';
                    @endphp
                    <div style="flex: 1; position: relative;" class="group" @mouseenter="hoverIndex = {{ $index }}" @mouseleave="hoverIndex = null">
                        <div
                            x-show="hoverIndex === {{ $index }}"
                            x-transition
                            style="position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 0.375rem; font-size: 0.6875rem; font-weight: 600; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 0.25rem 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap; z-index: 10; color: #374151;"
                        >
                            {{ $isDemo ? 'Rp ***' : 'Rp ' . number_format($trend['revenue'], 0, ',', '.') }}
                            <span style="color: #9ca3af; margin-left: 0.25rem;">({{ $trend['count'] }})</span>
                        </div>

                        <div style="width: 100%; max-width: 36px; margin: 0 auto; border-radius: 0.25rem 0.25rem 0 0; height: {{ $barHeight }}px; background-color: {{ $barColor }}; cursor: pointer; transition: opacity 0.2s; {{ $isCurrentMonth ? 'box-shadow: 0 -2px 6px rgba(58,122,53,0.3);' : '' }}"></div>
                    </div>
                @endforeach
            </div>

            {{-- Labels --}}
            <div style="display: flex; gap: 0.375rem; margin-top: 0.375rem; padding: 0 0.125rem;">
                @foreach ($trendData as $index => $trend)
                    @php $isCurrentMonth = $index === 5; @endphp
                    <div style="flex: 1; font-size: 0.5625rem; font-weight: 500; text-align: center; line-height: 1.2; {{ $isCurrentMonth ? 'color: #3a7a35; font-weight: 700;' : 'color: #6b7280;' }}">
                        {{ $trend['label'] }}
                    </div>
                @endforeach
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 0.75rem; padding-top: 0.625rem; border-top: 1px solid #f3f4f6;">
                @foreach ($trendData as $index => $trend)
                    <div style="display: flex; align-items: center; gap: 0.25rem; font-size: 0.5625rem; color: #6b7280;">
                        <div style="width: 0.5rem; height: 0.5rem; border-radius: 2px; background-color: {{ $barColors[$index] ?? '#87A878' }};"></div>
                        <span>{{ $trend['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>

    {{-- Breakdown per Layanan --}}
    <x-filament::section>
        <x-slot name="heading">
            Breakdown per Layanan
        </x-slot>
        <x-slot name="description">
            Rincian janji temu dan pendapatan berdasarkan jenis layanan.
        </x-slot>

        @if ($serviceBreakdown->isEmpty())
            <div style="text-align: center; padding: 2rem 0;">
                <x-heroicon-o-inbox style="width:40px;height:40px" class="mx-auto" />
                <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.75rem;">Tidak ada data untuk periode ini.</p>
            </div>
        @else
            <div style="margin-top: 0.5rem; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse; min-width: 400px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <th style="text-align: left; padding: 0.625rem 0.75rem; font-weight: 600; color: #4b5563; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">Layanan</th>
                            <th style="text-align: right; padding: 0.625rem 0.75rem; font-weight: 600; color: #4b5563; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">Total</th>
                            <th style="text-align: right; padding: 0.625rem 0.75rem; font-weight: 600; color: #4b5563; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">Selesai</th>
                            <th style="text-align: right; padding: 0.625rem 0.75rem; font-weight: 600; color: #4b5563; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceBreakdown as $service)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 0.625rem 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 0.375rem; height: 0.375rem; border-radius: 9999px; background-color: #87A878; flex-shrink: 0;"></div>
                                        <span style="font-weight: 500; color: #1f2937;">{{ $service['name'] }}</span>
                                    </div>
                                </td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right; font-weight: 500; color: #374151;">
                                    {{ $service['total'] }}
                                </td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right;">
                                    <span style="display: inline-flex; align-items: center; padding: 0.0625rem 0.375rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 500; background-color: #ecfdf5; color: #047857;">
                                        {{ $service['completed'] }}
                                    </span>
                                </td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right; font-weight: 500; color: #1f2937;">
                                    {{ $isDemo ? 'Rp ***' : 'Rp ' . number_format($service['revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if ($serviceBreakdown->count() > 1)
                        <tfoot>
                            <tr style="border-top: 2px solid #e5e7eb;">
                                <td style="padding: 0.625rem 0.75rem; font-weight: 700; color: #1f2937;">Total</td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right; font-weight: 700; color: #1f2937;">
                                    {{ $serviceBreakdown->sum('total') }}
                                </td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right;">
                                    <span style="display: inline-flex; align-items: center; padding: 0.0625rem 0.375rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; background-color: #d1fae5; color: #065f46;">
                                        {{ $serviceBreakdown->sum('completed') }}
                                    </span>
                                </td>
                                <td style="padding: 0.625rem 0.75rem; text-align: right; font-weight: 700; color: #1f2937;">
                                    {{ $isDemo ? 'Rp ***' : 'Rp ' . number_format($serviceBreakdown->sum('revenue'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
