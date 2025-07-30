<?php

namespace App\Models;

use Chiiya\FilamentAccessControl\Models\FilamentUser;
use Chiiya\FilamentAccessControl\Contracts\AccessControlUser;
use Filament\Models\Contracts\FilamentUser as FilamentUserInterface;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;
use App\Models\WhatsAppAppointment;
use \Carbon\Carbon;
use Filament\Panel;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class HealthCareUser extends FilamentUser
{

    use HasTranslations;
    use HasApiTokens;
    use HasRoles;
    public $translatable = ['name_translated'];
    protected $table = 'filament_users';
    protected $casts = [
        'appointment_slots' => 'array',
        'television_configuration' => 'array',
    ];
    protected $hidden = [
        "date_of_birth",
        "two_factor_expires_at",
        "two_factor_code",
        "expires_at",
        "uuid",
        "otp",
        "otp_expires_at",
        "remember_token",
        "password"
    ];

    protected $appends = [
        "full_name"
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        $user = auth('filament')->user();
        if (!($user->hasRole('healthcare.admin') || $user->hasRole('doctor') || $user->hasRole('super-admin')))
            return false;
        return $this->disabled != true;
    }

    protected function getDefaultGuardName(): string
    {
        return 'filament';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, "doctor_specialization", "doctor_id", "specialization_id");
    }

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, "doctor_hospitals", "doctor_id", "hospital_id");
    }
    
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function educations(): BelongsToMany
    {
        return $this->belongsToMany(Education::class, "doctor_education", "doctor_id", "education_id");
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, "doctor_language", "doctor_id", "language_id");
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(HealthCareUser::class, "receptionist_id", "id");
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getFullName()
    {
        return $this->first_name . " " . $this->last_name;
    }

    public function getExperience()
    {
        if ($this->working_since != null)
            return Carbon::createFromFormat("Y-m-d", $this->working_since)->diff(now())->format('%y ' . __("whatsapp.years_experience"));
        return "";
    }

    public function availableDates($mode, $hospitalId, $format = "Y-m-d")
    {
        $mode = "appointment_slots";
        $weekDays = collect($this->$mode)->pluck("weekDay")->unique();
        $currentDay = date("l");
        $dates = [];
        $now = new \DateTime();
        if ($weekDays->contains($currentDay)) {
            $dates[] = date($format, strtotime('today'));
        }
        for ($i = 1; $i <= 15; $i++) {
            $now->modify("+1 day");
            if (
                $weekDays->contains($now->format('l'))
                && $this->appointment_slots[$i - 1]["hospital"] == $hospitalId
            ) {
                $dates[] = $now->format($format);
            }
        }
        return $dates;
    }

    public function getSlotTypeCount($date)
    {
        $weekDay = date('l', strtotime($date));
        $weekDayInfo = collect($this->appointment_slots)->where("weekDay", $weekDay);
        return count($weekDayInfo->unique("slot_type")->pluck("slot_type"));
    }

    public function getAvailableTimeSlots($date, $mode, $format = "H:i", $slotType = null)
    {
        $mode = "appointment_slots";
        $d = $mode;
        $dur = "appointment_slot_duration";
        $weekDay = date('l', strtotime($date));
        $weekDayInfo = collect($this->$d)->where("weekDay", $weekDay);
        $availableTimeSlots = [];
        date_default_timezone_set("Asia/Calcutta");
        $leaves = \App\Models\DoctorLeave::where("leave_start", ">=", "$date 00:00")->where("leave_start", "<=", "$date 23:59")->get();
        foreach ($weekDayInfo as $info) {

            if ($slotType != null) {
                if (!isset($info["slot_type"]))
                    continue;
                if ($info["slot_type"] != $slotType)
                    continue;
            }
            $startTime = $date . " " . $info["startTime"];
            $endTime = $date . " " . $info["endTime"];
            $duration = $this->$dur;
            $availableTimeSlots = array_merge($availableTimeSlots, $this->splitTime($startTime, $endTime, $duration, $leaves, $format));
        }
        $availableTimeSlots = collect($availableTimeSlots)->unique();
        $dt = date("Y-m-d", strtotime($date));
        $bookedTimeSlots = WhatsAppAppointment::select("appointment_time")
            ->where("doctor_id", $this->id)
            ->where("appointment_date", $dt)
            ->where("payment_status", "PAYMENT_COMPLETED")
            ->whereNot("status", "Cancelled")
            ->get()
            ->pluck("appointment_time");
        $filtered = $availableTimeSlots->reject(function ($value, $key) use ($bookedTimeSlots, $format) {
            if ($format == "h:i A") {
                $value = Carbon::createFromFormat($format, $value)->format("H:i");
            }
            return $bookedTimeSlots->contains($value);
        });
        $availableTimeSlots = $filtered->all();
        return array_values($availableTimeSlots);
    }

    public function splitTime($StartTime, $EndTime, $Duration = "60", $leaves, $format)
    {
        $ReturnArray = array(); // Define output
        $StartTime = strtotime($StartTime); //Get Timestamp
        $EndTime = strtotime($EndTime); //Get Timestamp
        $AddMins = $Duration * 60;
        $from = now()->addMinutes(30)->timestamp;
        while ($StartTime <= $EndTime) //Run loop
        {
            $onleave = false;
            foreach ($leaves as $leave) {
                if ($StartTime >= strtotime($leave->leave_start) && $StartTime <= strtotime($leave->leave_end)) {
                    $onleave = true;
                }
            }
            if ($StartTime >= $from && $onleave == false)
                $ReturnArray[] = date($format, $StartTime);
            $StartTime += $AddMins; //Endtime check
        }
        return $ReturnArray;
    }

    public function getAppointmentFees($mode)
    {
        $m = $mode . "_appointment_cost";
        $rpayCommission = $this->$m * 0.03;
        return ceil(($this->$m + $rpayCommission));
    }

    public function getAvailableTimeRanges($date, $hospitalId, $showCount = false)
    {
        $timeranges = collect($this->appointment_slots);
        $weekday = date("l", strtotime($date));
        $ct = date('H:i');
        $timeranges = $timeranges
            ->filter(
                fn($range)
                => $range["weekDay"] == $weekday
                && $hospitalId == $range["hospital"]
            );
        if ($date == date("Y-m-d")) {
            $timeranges = $timeranges->filter(fn($range) => $range["endTime"] > $ct);
        }

        $startTimes = $timeranges->map(fn($range) => substr($range["startTime"], 0, -3));

        $dbCounts = null;
        if ($showCount) {
            $dbCounts = WhatsAppAppointment::select(["appointment_time", DB::raw('count(*) as total')])
                ->where("doctor_id", $this->id)
                ->where("hospital_id", $hospitalId)
                ->where("appointment_date", $date)
                ->whereIn("appointment_time", $startTimes)
                ->groupBy("appointment_time")
                ->get();
        }

        $timeranges = $timeranges->map(
            function ($range) use ($dbCounts, $showCount) {
                $st = date("h:i A", strtotime($range["startTime"]));
                $et = date("h:i A", strtotime($range["endTime"]));

                if ($showCount) {
                    $c = $dbCounts->filter(fn($record) => $record["appointment_time"] == substr($range["startTime"], 0, -3))->first();
                    $count = $c ? $c->total : 0;
                    return $st . " - " . $et . " ($count)";
                }

                return $st . " - " . $et;
            }
        )->values();
        return $timeranges;
    }
}