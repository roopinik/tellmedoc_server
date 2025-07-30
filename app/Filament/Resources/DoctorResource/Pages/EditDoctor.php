<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctor extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['appointment_slots']))
            $data['appointment_slots'] = $this->sortTimeRanges($data["appointment_slots"]);
        return $data;
    }

    public function sortTimeRanges($ranges)
    {
        $weekDaysOrder = [
            "Monday" => 1,
            "Tuesday" => 2,
            "Wednesday" => 3,
            "Thursday" => 4,
            "Friday" => 5,
            "Saturday" => 6,
            "Sunday" => 7
        ];

        usort($ranges, function ($a, $b) use ($weekDaysOrder) {
            return $weekDaysOrder[$a['weekDay']] <=> $weekDaysOrder[$b['weekDay']]
                ?: $a['hospital'] <=> $b['hospital']
                ?: $a['startTime'] <=> $b['startTime'];
        });
        return $ranges;
    }
}
