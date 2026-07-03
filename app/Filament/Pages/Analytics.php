<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Patient;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'Analitik';
    protected static ?string $title = 'Analitik';
    protected static ?string $slug = 'analytics';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.analytics';

    public int $selectedMonth;
    public int $selectedYear;
    public ?int $selectedBranchId = null;

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'developer']) ?? false;
    }

    public function applyFilters(): void
    {
        // Data re-renders via getViewData()
    }

    protected function getViewData(): array
    {
        $month = $this->selectedMonth;
        $year = $this->selectedYear;
        $branchId = $this->selectedBranchId;
        $isDemo = auth()->user()->isDemo();

        // --- Single query for all stats ---
        $stats = Appointment::query()
            ->whereMonth('scheduled_at', $month)
            ->whereYear('scheduled_at', $year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_appointments,
                SUM(CASE WHEN status = ? THEN final_price ELSE 0 END) as revenue
            ", [Appointment::STATUS_COMPLETED, Appointment::STATUS_COMPLETED])
            ->first();

        $totalAppointments = $stats->total_appointments ?? 0;
        $completedAppointments = $stats->completed_appointments ?? 0;
        $revenue = $stats->revenue ?? 0;

        // --- New patients ---
        $newPatients = Patient::query()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->when($branchId, fn ($q) => $q->whereHas('appointments', fn ($aq) => $aq->where('branch_id', $branchId)))
            ->count();

        // --- Service breakdown (single query) ---
        $serviceBreakdown = Appointment::query()
            ->whereMonth('scheduled_at', $month)
            ->whereYear('scheduled_at', $year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("
                service_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = ? THEN final_price ELSE 0 END) as revenue
            ", [Appointment::STATUS_COMPLETED, Appointment::STATUS_COMPLETED])
            ->groupBy('service_id')
            ->with('service:id,name')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->service?->name ?? 'Unknown',
                'total' => $item->total,
                'completed' => $item->completed,
                'revenue' => $item->revenue ?? 0,
            ]);

        // --- Trend 6 months (single query with GROUP BY) ---
        $startDate = Carbon::create($year, $month, 1)->subMonths(5)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $trendRows = Appointment::query()
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('scheduled_at', '>=', $startDate)
            ->where('scheduled_at', '<=', $endDate)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("
                EXTRACT(MONTH FROM scheduled_at) as m,
                EXTRACT(YEAR FROM scheduled_at) as y,
                COALESCE(SUM(final_price), 0) as revenue,
                COUNT(*) as count
            ")
            ->groupByRaw('EXTRACT(YEAR FROM scheduled_at), EXTRACT(MONTH FROM scheduled_at)')
            ->get()
            ->mapWithKeys(fn ($row) => [
                "{$row->y}-{$row->m}" => [
                    'revenue' => (int) $row->revenue,
                    'count' => (int) $row->count,
                ],
            ]);

        $trendData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $trendMonth = Carbon::create($year, $month, 1)->subMonths($i);
            $key = "{$trendMonth->year}-{$trendMonth->month}";
            $data = $trendRows->get($key, ['revenue' => 0, 'count' => 0]);
            $trendData->push([
                'label' => $trendMonth->translatedFormat('M Y'),
                'revenue' => $data['revenue'],
                'count' => $data['count'],
            ]);
        }

        // Chart scaling
        $maxRevenue = $trendData->max('revenue') ?: 1;

        // Branches for filter
        $branches = Branch::whereRaw('is_active = true')->pluck('nama_cabang', 'id');

        // Month names
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [
            $m => Carbon::create()->month($m)->translatedFormat('F'),
        ]);

        return [
            'totalAppointments' => $totalAppointments,
            'completedAppointments' => $completedAppointments,
            'revenue' => $revenue,
            'newPatients' => $newPatients,
            'serviceBreakdown' => $serviceBreakdown,
            'trendData' => $trendData,
            'maxRevenue' => $maxRevenue,
            'branches' => $branches,
            'months' => $months,
            'isDemo' => $isDemo,
            'selectedMonthName' => Carbon::create()->month($month)->translatedFormat('F'),
        ];
    }
}
