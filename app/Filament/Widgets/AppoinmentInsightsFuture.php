<?php

namespace App\Filament\Widgets;

use App\Models\WhatsAppAppointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use \Carbon\Carbon;

class AppoinmentInsightsFuture extends BaseWidget
{
    public $title = "Next Appointments";
    protected function getStats(): array
    {
        $date = Carbon::today();
        $today = $date->toDateString();
        $todayr = $date->format('M d Y');
        $tommorow = $date->addDay()->toDateString();
        $tommorowr = $date->format('M d Y');
        $dayaftom = $date->addDay()->toDateString();
        $dayaftomr = $date->format('M d Y');
        $next7thDay = $date->addDays(5)->toDateString();
        $date->subDays(8);
        $prevDay = $date->toDateString();
        $prevDayr = $date->format('M d Y');
        $date->subDays(6);
        $prev7thDay = $date->toDateString();

        $date->addDays(3);
        $todaysCount = WhatsAppAppointment::where("appointment_date", $today)->stats()->count();
        // $todaysOlCount = WhatsAppAppointment::where("appointment_date",$today)->where("appointment_mode","online")->stats()->count();
        $tommorowsCount = WhatsAppAppointment::where("appointment_date", $tommorow)->stats()->count();
        // $tommorowsOlCount = WhatsAppAppointment::where("appointment_date",$tommorow)->where("appointment_mode","online")->stats()->count();
        $dayaftommorowsCount = WhatsAppAppointment::where("appointment_date", $dayaftom)->stats()->count();
        // $dayaftommorowsOlCount = WhatsAppAppointment::where("appointment_date",$dayaftom)->where("appointment_mode","online")->stats()->count();
        $prevDaysCount = WhatsAppAppointment::where("appointment_date", $prevDay)->stats()->count();
        $next7DaysCount = WhatsAppAppointment::where("appointment_date", ">=", $today)->stats()->where("appointment_date", "<=", $next7thDay)->count();
        $prev7DaysCount = WhatsAppAppointment::where("appointment_date", ">=", $prev7thDay)->stats()->where("appointment_date", "<=", $today)->count();
        return [
            Stat::make("Total Appointments ($todayr)", "Today, " . $todaysCount)->extraAttributes([])->color("success"),
            Stat::make("Total Appointments ($tommorowr)", "Tommorow, " . $tommorowsCount)
                ->color("success"),
            Stat::make("Total Appointments ($dayaftomr)", "Day After Tom, " . $dayaftommorowsCount)
                ->color("success"),
            Stat::make("Total Appointments ($prevDayr)", "Yesterday, " . $prevDaysCount),
            Stat::make("Total Appointments", "Last Seven Days, " . $prev7DaysCount),
            Stat::make("Total Appointments", "Next Seven Days, " . $next7DaysCount),
        ];
    }
}
