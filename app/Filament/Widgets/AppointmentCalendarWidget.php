<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Database\Eloquent\Model;

class AppointmentCalendarWidget extends FullCalendarWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected function headerActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Buat Jadwal Baru')
                ->url(fn () => \App\Filament\Resources\Appointments\AppointmentResource::getUrl('create')),
        ];
    }
    
    /**
     * Return events that should be rendered statically on calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $branchId = null;
        if (auth()->user()->hasRole('branch_admin')) {
            $branchId = auth()->user()->branch_id;
        } elseif (auth()->user()->hasRole('super_admin')) {
            $branchId = $this->pageFilters['branch_id'] ?? null;
        }

        $query = Appointment::query()
            ->with(['patient.user', 'service'])
            ->select([
                'id',
                'branch_id',
                'patient_id',
                'service_id',
                'status',
                'scheduled_at',
            ])
            ->where('scheduled_at', '>=', $fetchInfo['start'])
            ->where('scheduled_at', '<=', $fetchInfo['end']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()
            ->map(
                fn (Appointment $appointment) => [
                    'id' => $appointment->id,
                    'title' => ($appointment->patient?->user?->name ?? 'Unknown') . ' - ' . ($appointment->service?->name ?? 'Unknown Service'),
                    'start' => $appointment->scheduled_at->format('Y-m-d\TH:i:s'),
                    'end' => $appointment->scheduled_at->addMinutes(60)->format('Y-m-d\TH:i:s'),
                    'url' => \App\Filament\Resources\Appointments\AppointmentResource::getUrl('view', ['record' => $appointment]),
                    'shouldOpenUrlInNewTab' => false,
                    'backgroundColor' => match ($appointment->status) {
                        Appointment::STATUS_SCHEDULED => '#6b7280',
                        Appointment::STATUS_IN_PROGRESS => '#f59e0b',
                        Appointment::STATUS_COMPLETED => '#10b981',
                        Appointment::STATUS_CANCELLED => '#ef4444',
                        default => '#3b82f6',
                    },
                    'borderColor' => 'transparent',
                ]
            )
            ->all();
    }
}
