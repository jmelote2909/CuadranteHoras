<?php

namespace App\Traits;

use App\Models\Shift;
use App\Models\Setting;
use App\Models\ExternalOperation;
use App\Models\Holiday;
use Carbon\Carbon;

trait CalculatesMonthlyTotals
{
    public function calculateTotals($operators, $month, $year, $isAmarillosMode = false, $days = null)
    {
        if ($days === null) {
            $days = $this->getDaysInMonth($month, $year);
        }

        $globalRateWeekday  = Setting::getRate('extra_rate_weekday', 0);
        $globalRateSaturday = Setting::getRate('extra_rate_saturday', 0);
        $globalRateSunday   = Setting::getRate('extra_rate_sunday', 0);
        
        $operatorIds = $operators->pluck('id')->toArray();
        
        // Batch fetch external operations
        $externalOperations = ExternalOperation::where('month', $month)
            ->where('year', $year)
            ->whereIn('operator_id', $operatorIds)
            ->pluck('amount', 'operator_id')
            ->toArray();

        // Batch fetch shifts for the whole month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $allShifts = Shift::whereIn('operator_id', $operatorIds)
            ->whereBetween('date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d') . ' 23:59:59',  // include last day (stored as datetime)
            ])
            ->get()
            ->groupBy('operator_id');

        // Batch fetch all-time totals (amarillos)
        $allTimeYellowTotals = Shift::whereIn('operator_id', $operatorIds)
            ->where('color', 'yellow')
            ->groupBy('operator_id')
            ->selectRaw('operator_id, sum(hours) as sum')
            ->pluck('sum', 'operator_id')
            ->toArray();

        $allTimeBlueTotals = Shift::whereIn('operator_id', $operatorIds)
            ->where('color', 'blue')
            ->groupBy('operator_id')
            ->selectRaw('operator_id, sum(hours) as sum')
            ->pluck('sum', 'operator_id')
            ->toArray();

        // Batch fetch monthly totals for amarillos
        $monthYellowTotals = Shift::whereIn('operator_id', $operatorIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('color', 'yellow')
            ->groupBy('operator_id')
            ->selectRaw('operator_id, sum(hours) as sum')
            ->pluck('sum', 'operator_id')
            ->toArray();
            
        $monthBlueTotals = Shift::whereIn('operator_id', $operatorIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('color', 'blue')
            ->groupBy('operator_id')
            ->selectRaw('operator_id, sum(hours) as sum')
            ->pluck('sum', 'operator_id')
            ->toArray();

        $totals = [];
        
        foreach ($operators as $op) {
            $hLunesViernes = 0;
            $hSabados      = 0;
            $hDomingos     = 0;
            $hFestivos     = 0;
            
            $opShifts = $allShifts->get($op->id, collect())->keyBy(fn($s) => $s->date->format('Y-m-d'));

            foreach ($days as $day) {
                $shift = $opShifts->get($day['date']);
                $hours = $shift ? (float) $shift->hours : 0;
                $color = $shift ? $shift->color : null;

                // Skip hours that don't belong to the current mode
                if ($isAmarillosMode && !in_array($color, ['yellow', 'blue'])) {
                    $hours = 0;
                } elseif (!$isAmarillosMode && $color !== null) {
                    $hours = 0;
                }
                
                if ($day['is_holiday']) {
                    $hFestivos += $hours;
                } elseif ($day['is_sunday']) {
                    $hDomingos += $hours;
                } elseif ($day['is_saturday']) {
                    $hSabados += $hours;
                } else {
                    $hLunesViernes += $hours;
                }
            }
            
            $totalHoras = $hLunesViernes + $hSabados + $hDomingos + $hFestivos;
            
            $costeLV   = $hLunesViernes * $globalRateWeekday;
            $costeSab  = $hSabados      * $globalRateSaturday;
            $costeDom  = $hDomingos     * $globalRateSunday;
            $costeFest = $hFestivos     * $globalRateSaturday;
            $extOpAmount  = (float)($externalOperations[$op->id] ?? 0);
            $totalCostes  = $costeLV + $costeSab + $costeDom + $costeFest + $extOpAmount;
            
            // Amarillos Calculations
            $allTimeYellow = (float)($allTimeYellowTotals[$op->id] ?? 0);
            $allTimeBlue   = (float)($allTimeBlueTotals[$op->id]   ?? 0);
            $monthYellow   = (float)($monthYellowTotals[$op->id]   ?? 0);
            $monthBlue     = (float)($monthBlueTotals[$op->id]     ?? 0);

            $totals[$op->id] = [
                'horas_lv'              => $hLunesViernes,
                'horas_sab'             => $hSabados,
                'horas_dom'             => $hDomingos,
                'horas_fest'            => $hFestivos,
                'horas_total'           => $totalHoras,
                'coste_lv'              => $costeLV,
                'coste_sab'             => $costeSab,
                'coste_dom'             => $costeDom,
                'coste_fest'            => $costeFest,
                'coste_total'           => $totalCostes,
                'amarillos_total_balance' => $allTimeYellow - $allTimeBlue,
                'amarillos_mes'         => $monthYellow,
                'azules_mes'            => $monthBlue,
            ];
        }

        return $totals;
    }

    public function getDaysInMonth($month, $year)
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $days = [];
        
        $holidays = Holiday::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        for ($i = 0; $i < $date->daysInMonth; $i++) {
            $current = $date->copy()->addDays($i);
            $days[] = [
                'date'       => $current->format('Y-m-d'),
                'day'        => $current->day,
                'name'       => $current->shortLocaleDayOfWeek,
                'is_weekend' => $current->isWeekend(),
                'is_saturday'=> $current->dayOfWeek === Carbon::SATURDAY,
                'is_sunday'  => $current->dayOfWeek === Carbon::SUNDAY,
                'is_holiday' => in_array($current->format('Y-m-d'), $holidays),
            ];
        }
        
        return $days;
    }
}
