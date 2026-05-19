<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $branchId = null;
        if (auth()->user()->hasRole('branch_admin')) {
            $branchId = auth()->user()->branch_id;
        } elseif (auth()->user()->hasRole('super_admin')) {
            $branchId = $this->pageFilters['branch_id'] ?? null;
        }

        // Appointments Today
        $appointmentsQuery = Appointment::whereDate('scheduled_at', Carbon::today());
        if ($branchId) {
            $appointmentsQuery->where('branch_id', $branchId);
        }
        $appointmentsToday = $appointmentsQuery->count();

        // New Patients this month
        $patientsQuery = Patient::whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year);
        // Note: Patient doesn't strictly belong to a branch in our schema unless we join appointments. 
        // We'll just show all new patients, or filter by patients who had an appointment at this branch.
        if ($branchId) {
            $patientsQuery->whereHas('appointments', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $newPatients = $patientsQuery->count();

        // Revenue this month (Completed appointments)
        $revenueQuery = Appointment::where('status', Appointment::STATUS_COMPLETED)
                                ->whereMonth('scheduled_at', Carbon::now()->month)
                                ->whereYear('scheduled_at', Carbon::now()->year);
        if ($branchId) {
            $revenueQuery->where('branch_id', $branchId);
        }
        $revenue = $revenueQuery->sum('final_price') ?? 0;

        return [
            Stat::make('Jadwal Hari Ini', $appointmentsToday)
                ->description('Total kunjungan pasien hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Pasien Baru', $newPatients)
                ->description('Pasien mendaftar bulan ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Pendapatan', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description('Dari jadwal selesai bulan ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
