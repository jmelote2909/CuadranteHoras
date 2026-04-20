<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Models\Operator;
use App\Models\Shift;
use App\Models\Setting;

new #[Layout('layouts.app')] class extends Component
{
    public $month;
    public $year;
    public $shifts = [];
    public $shiftColors = [];
    public $company = 'arancalo';
    public $isAmarillosMode = false;
    public $activeColor = 'yellow';

    public $showAddModal = false;
    public $newOperatorName = '';

    public function rules()
    {
        return [
            'newOperatorName' => 'required|string|max:255',
        ];
    }

    public function saveOperator()
    {
        $this->validate();

        Operator::create([
            'name' => $this->newOperatorName,
            'rate_weekday' => 0,
            'rate_saturday' => 0,
            'rate_sunday' => 0,
            'company' => $this->company,
        ]);

        $this->reset(['newOperatorName', 'showAddModal']);
        $this->loadShifts();
    }

    public function mount()
    {
        $this->isAmarillosMode = str_contains(request()->route()->getName(), 'amarillos');
        $this->company = str_contains(request()->route()->getName(), 'cima') ? 'cima' : 'arancalo';
        $this->month = now()->month;
        $this->year = now()->year;
        $this->loadShifts();
    }

    public function loadShifts()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->format('Y-m-d');
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('Y-m-d');

        $allShifts = Shift::whereBetween('date', [$startDate, $endDate])
            ->when($this->isAmarillosMode, function($q) {
                return $q->whereIn('color', ['yellow', 'blue']);
            })
            ->when(!$this->isAmarillosMode, function($q) {
                return $q->whereNull('color');
            })
            ->get();
            
        $this->shifts = [];
        $this->shiftColors = [];
        
        $operators = Operator::where('company', $this->company)->get();
        foreach($operators as $operator) {
            $date = Carbon::createFromDate($this->year, $this->month, 1);
            for ($i = 0; $i < $date->daysInMonth; $i++) {
                $current = $date->copy()->addDays($i)->format('Y-m-d');
                $this->shifts[$operator->id][$current] = null;
                $this->shiftColors[$operator->id][$current] = null;
            }
        }

        foreach($allShifts as $shift) {
            $this->shifts[$shift->operator_id][$shift->date->format('Y-m-d')] = $shift->hours;
            $this->shiftColors[$shift->operator_id][$shift->date->format('Y-m-d')] = $shift->color;
        }
    }

    public function setColor($color)
    {
        $this->activeColor = $color;
    }

    public function updatedShifts($value, $name)
    {
        [$operatorId, $date] = explode('.', $name);

        if ($value === '' || $value === null) {
            Shift::where('operator_id', $operatorId)->whereDate('date', $date)->delete();
        } else {
            $shift = Shift::where('operator_id', $operatorId)->whereDate('date', $date)->first();
            
            if ($shift) {
                $updateData = ['hours' => $value];
                if ($this->isAmarillosMode) {
                    $updateData['color'] = $this->activeColor;
                    $this->shiftColors[$operatorId][$date] = $this->activeColor;
                }
                $shift->update($updateData);
            } else {
                $createData = [
                    'operator_id' => $operatorId, 
                    'date' => Carbon::parse($date)->startOfDay(), 
                    'hours' => $value
                ];
                if ($this->isAmarillosMode) {
                    $createData['color'] = $this->activeColor;
                    $this->shiftColors[$operatorId][$date] = $this->activeColor;
                }
                Shift::create($createData);
            }
        }
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadShifts();
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadShifts();
    }

    public function monthName()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F');
    }

    public function daysInMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1);
        $days = [];
        
        for ($i = 0; $i < $date->daysInMonth; $i++) {
            $current = $date->copy()->addDays($i);
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'name' => $current->shortLocaleDayOfWeek,
                'is_weekend' => $current->isWeekend(),
                'is_saturday' => $current->dayOfWeek === Carbon::SATURDAY,
                'is_sunday' => $current->dayOfWeek === Carbon::SUNDAY,
            ];
        }
        
        return $days;
    }

    public function with()
    {
        $operators = Operator::where('company', $this->company)->get();
        $days = $this->daysInMonth();
        
        $globalRateWeekday = Setting::getRate('extra_rate_weekday', 0);
        $globalRateSaturday = Setting::getRate('extra_rate_saturday', 0);
        $globalRateSunday = Setting::getRate('extra_rate_sunday', 0);
        
        $totals = [];
        
        foreach ($operators as $op) {
            $hLunesViernes = 0;
            $hSabados = 0;
            $hDomingos = 0;
            
            foreach ($days as $day) {
                // Fetch hour and color from Livewire state
                $hours = (float) ($this->shifts[$op->id][$day['date']] ?? 0);
                $color = $this->shiftColors[$op->id][$day['date']] ?? null;

                // Skip hours that don't belong to the current mode
                if ($this->isAmarillosMode && !in_array($color, ['yellow', 'blue'])) {
                    $hours = 0;
                } elseif (!$this->isAmarillosMode && $color !== null) {
                    $hours = 0;
                }
                
                if ($day['is_sunday']) {
                    $hDomingos += $hours;
                } elseif ($day['is_saturday']) {
                    $hSabados += $hours;
                } else {
                    $hLunesViernes += $hours;
                }
            }
            
            $totalHoras = $hLunesViernes + $hSabados + $hDomingos;
            
            $costeLV = $hLunesViernes * $globalRateWeekday;
            $costeSab = $hSabados * $globalRateSaturday;
            $costeDom = $hDomingos * $globalRateSunday;
            $totalCostes = $costeLV + $costeSab + $costeDom;
            
            // Días Amarillos Calculations
            $allTimeYellow = Shift::where('operator_id', $op->id)->where('color', 'yellow')->sum('hours');
            $allTimeBlue = Shift::where('operator_id', $op->id)->where('color', 'blue')->sum('hours');
            
            $monthYellow = Shift::where('operator_id', $op->id)
                ->whereMonth('date', $this->month)
                ->whereYear('date', $this->year)
                ->where('color', 'yellow')
                ->sum('hours');
                
            $monthBlue = Shift::where('operator_id', $op->id)
                ->whereMonth('date', $this->month)
                ->whereYear('date', $this->year)
                ->where('color', 'blue')
                ->sum('hours');
                
            $yearComposite = Shift::where('operator_id', $op->id)
                ->whereYear('date', $this->year)
                ->whereIn('color', ['yellow', 'blue'])
                ->sum('hours');

            $totals[$op->id] = [
                'horas_lv' => $hLunesViernes,
                'horas_sab' => $hSabados,
                'horas_dom' => $hDomingos,
                'horas_total' => $totalHoras,
                'coste_lv' => $costeLV,
                'coste_sab' => $costeSab,
                'coste_dom' => $costeDom,
                'coste_total' => $totalCostes,
                'amarillos_total_balance' => $allTimeYellow - $allTimeBlue,
                'amarillos_mes' => $monthYellow,
                'azules_mes' => $monthBlue,
                'año_compuesto' => $yearComposite,
            ];
        }

        return [
            'operatorsList' => $operators,
            'daysList' => $days,
            'totalsList' => $totals,
        ];
    }
};
?>

<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Filters / Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-outfit uppercase tracking-tight">
                {{ $isAmarillosMode ? 'DÍAS AMARILLOS (' . strtoupper($company) . ')' : strtoupper($company) . ' EMPLEADOS' }}
            </h1>
            <p class="text-slate-500 text-sm">
                {{ $isAmarillosMode ? 'Gestión de bolsa de horas y comodines' : 'Control de horas y periodos mensuales' }}
            </p>
        </div>

        <div class="flex items-center gap-4">
            @if($isAmarillosMode)
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button wire:click="setColor('yellow')" 
                        class="px-5 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-2 {{ $activeColor === 'yellow' ? 'bg-yellow-400 text-slate-900 shadow-sm border border-yellow-500' : 'text-slate-500 hover:bg-white border border-transparent' }}">
                        <div class="w-3 h-3 rounded-full bg-yellow-600"></div>
                        MARCAR AMARILLO
                    </button>
                    <button wire:click="setColor('blue')" 
                        class="px-5 py-2 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-2 {{ $activeColor === 'blue' ? 'bg-blue-600 text-white shadow-sm border border-blue-700' : 'text-slate-500 hover:bg-white border border-transparent' }}">
                        <div class="w-3 h-3 rounded-full bg-white"></div>
                        MARCAR AZUL
                    </button>
                </div>
            @endif

            <button wire:click="$set('showAddModal', true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors shadow-sm text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Añadir Empleado
            </button>

            <div class="flex items-center space-x-4 bg-slate-50 p-2 rounded-xl border border-slate-200">
                <button wire:click="prevMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition-all">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <div class="px-4 text-center min-w-[140px]">
                    <span class="block text-xs font-bold text-slate-400 uppercase leading-none">{{ $year }}</span>
                    <span class="text-sm font-bold text-slate-900 capitalize">{{ $this->monthName() }}</span>
                </div>
                <button wire:click="nextMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition-all">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>
    </div>

    @if($operatorsList->isEmpty())
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-8 text-center text-indigo-800">
            <svg class="w-12 h-12 mx-auto text-indigo-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <h3 class="text-xl font-bold mb-2">Aún no hay Empleados</h3>
            <p class="text-indigo-600/80 max-w-sm mx-auto">Debes crear empleados en la base de datos para ver la cuadrícula y registrar horas.</p>
        </div>
    @else
        <!-- The Grid Container -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xl overflow-hidden pb-[1px]">
            <div class="overflow-x-auto overflow-y-hidden">
                <table class="w-full border-collapse text-[11px] font-medium tracking-tighter" style="min-width: 1400px;">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <!-- Sticky First Column -->
                            <th class="sticky left-0 z-20 bg-slate-50 p-3 border-r border-slate-200 text-left min-w-[220px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] uppercase">
                                Empleado
                            </th>
                            
                            <!-- Day Headers -->
                            @foreach($daysList as $day)
                                <th class="p-2 border-r border-slate-200 text-center w-[40px] uppercase {{ $day['is_sunday'] ? 'bg-rose-100 text-rose-700' : ($day['is_saturday'] ? 'bg-orange-50 text-orange-600' : 'text-slate-500') }}">
                                    <div class="text-[9px] mb-0.5">{{ $day['name'] }}</div>
                                    <div class="text-sm font-bold">{{ $day['day'] }}</div>
                                </th>
                            @endforeach

                            @if($isAmarillosMode)
                                <!-- Summary Headers for Amarillos Mode -->
                                <th class="p-2 border-r border-slate-200 text-center min-w-[120px] bg-yellow-400 text-slate-900 uppercase font-bold leading-tight">
                                    Días Totales<br>en Amarillo
                                </th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[100px] bg-yellow-200 text-yellow-900 uppercase font-bold leading-tight">
                                    Suma Horas<br>Amarillas Mes
                                </th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[100px] bg-blue-100 text-blue-900 uppercase font-bold leading-tight">
                                    Suma Horas<br>Azules Mes
                                </th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[120px] bg-green-500 text-white uppercase font-bold leading-tight">
                                    Total Horas Año<br>Amarillo-Azul
                                </th>
                            @else
                                <!-- Summary Headers: Hours -->
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-slate-100 text-slate-700 uppercase">Horas<br>L-V</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-orange-100 text-orange-800 uppercase">Horas<br>Sábados</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-rose-100 text-rose-800 uppercase">Horas<br>Domingos</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-yellow-400 text-black uppercase font-bold">Total<br>Horas</th>

                                <!-- Summary Headers: Costs -->
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-green-50 text-green-700 uppercase">Coste<br>L-V</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-green-100 text-green-800 uppercase">Coste<br>Sábados</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-green-200 text-green-900 uppercase">Coste<br>Domingos</th>
                                <th class="p-2 border-r border-slate-200 text-center min-w-[70px] bg-green-500 text-white uppercase font-bold">Total<br>Costes</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($operatorsList as $op)
                            <tr class="hover:bg-indigo-50/20 transition-colors group">
                                <!-- Sticky Column Data -->
                                <td class="sticky left-0 z-10 bg-white group-hover:bg-indigo-50 p-3 border-r border-slate-200 font-bold text-slate-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                    {{ $op->name }}
                                </td>

                                <!-- Day Cells (Inputs) -->
                                @foreach($daysList as $day)
                                    @php
                                        $hourValue = $shifts[$op->id][$day['date']] ?? null;
                                        $shiftColor = $shiftColors[$op->id][$day['date']] ?? null;
                                        
                                        $cellBg = '';
                                        $textColor = 'text-slate-700';
                                        if ($isAmarillosMode && $hourValue !== null && $hourValue !== '') {
                                            if ($shiftColor === 'yellow') {
                                                $cellBg = 'bg-yellow-400';
                                                $textColor = 'text-slate-900';
                                            } elseif ($shiftColor === 'blue') {
                                                $cellBg = 'bg-blue-600';
                                                $textColor = 'text-white';
                                            }
                                        } elseif ($day['is_sunday']) {
                                            $cellBg = 'bg-rose-50/30';
                                        } elseif ($day['is_saturday']) {
                                            $cellBg = 'bg-orange-50/20';
                                        }
                                    @endphp
                                    <td class="border-r border-slate-100 text-center p-0 align-middle {{ $cellBg }}">
                                        <input type="number" step="0.5" min="0" max="24"
                                            wire:model.live.debounce.1000ms="shifts.{{ $op->id }}.{{ $day['date'] }}"
                                            class="w-full h-full min-h-[46px] text-center bg-transparent border-none focus:ring-0 text-sm font-bold p-0 m-0 {{ $textColor }}"
                                            placeholder="-">
                                    </td>
                                @endforeach

                                @if($isAmarillosMode)
                                    <!-- Summary Data: Amarillos Mode -->
                                    <td class="p-2 border-r border-slate-200 text-center bg-yellow-400/20 text-slate-900 font-bold text-sm">
                                        {{ $totalsList[$op->id]['amarillos_total_balance'] }}
                                    </td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-yellow-100/50 text-yellow-700 font-bold text-sm">
                                        {{ $totalsList[$op->id]['amarillos_mes'] }}
                                    </td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-blue-50 text-blue-700 font-bold text-sm">
                                        {{ $totalsList[$op->id]['azules_mes'] }}
                                    </td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-green-500 text-white font-bold text-sm">
                                        {{ $totalsList[$op->id]['año_compuesto'] }}
                                    </td>
                                @else
                                    <!-- Summary Data: Hours -->
                                    <td class="p-2 border-r border-slate-200 text-center bg-slate-50 text-slate-700 font-bold text-sm">{{ $totalsList[$op->id]['horas_lv'] }}</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-orange-50/50 text-orange-700 font-bold text-sm">{{ $totalsList[$op->id]['horas_sab'] }}</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-rose-50/50 text-rose-700 font-bold text-sm">{{ $totalsList[$op->id]['horas_dom'] }}</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-yellow-50 text-slate-900 font-bold text-sm">{{ $totalsList[$op->id]['horas_total'] }}</td>

                                    <!-- Summary Data: Costs -->
                                    <td class="p-2 border-r border-slate-200 text-center bg-green-50/30 text-green-700 font-medium whitespace-nowrap">{{ number_format($totalsList[$op->id]['coste_lv'], 2, ',', '.') }} €</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-green-100/30 text-green-700 font-medium whitespace-nowrap">{{ number_format($totalsList[$op->id]['coste_sab'], 2, ',', '.') }} €</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-green-200/30 text-green-800 font-medium whitespace-nowrap">{{ number_format($totalsList[$op->id]['coste_dom'], 2, ',', '.') }} €</td>
                                    <td class="p-2 border-r border-slate-200 text-center bg-green-100 text-green-900 font-bold text-sm whitespace-nowrap">{{ number_format($totalsList[$op->id]['coste_total'], 2, ',', '.') }} €</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend / Hints -->
        <div class="flex items-center space-x-6 text-xs text-slate-400 px-2 mt-4">
            <div class="flex items-center"><span class="w-3 h-3 bg-rose-200 rounded-sm mr-2"></span> Domingos</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-orange-100 rounded-sm mr-2"></span> Sábados</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-slate-50 border border-slate-200 rounded-sm mr-2"></span> Días Laborables</div>
            <div class="ml-auto text-slate-500 itlaic">(*) Introduce horas, los cálculos se guardan automáticamente.</div>
        </div>
    @endif

    <!-- Add Operator Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-bold text-slate-900">Añadir Nuevo Empleado</h3>
                <button wire:click="$set('showAddModal', false)" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit="saveOperator" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo</label>
                    <input type="text" wire:model="newOperatorName" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" required autofocus>
                    @error('newOperatorName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="pt-4 flex justify-end gap-3 mt-2">
                    <button type="button" wire:click="$set('showAddModal', false)" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">Guardar Empleado</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>