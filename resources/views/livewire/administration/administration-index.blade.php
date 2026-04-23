<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Models\Operator;
use App\Traits\CalculatesMonthlyTotals;

new #[Layout('layouts.app')] class extends Component
{
    use CalculatesMonthlyTotals;

    public $month;
    public $year;
    public $company;
    public $zones = [];

    public function mount()
    {
        $this->company = request()->routeIs('administration.arancalo') ? 'arancalo' : 'cima';
        $this->month = now()->month;
        $this->year = now()->year;
        $this->loadZones();
    }

    public function loadZones()
    {
        $operators = Operator::where('company', $this->company)->get();
        foreach ($operators as $op) {
            $this->zones[$op->id] = $op->zone;
        }
    }

    public function updatedZones($value, $key)
    {
        $operator = Operator::find($key);
        if ($operator) {
            $operator->update(['zone' => $value]);
        }
    }

    public function deleteOperator($id)
    {
        $operator = Operator::find($id);
        if ($operator) {
            $operator->delete();
            $this->loadZones();
        }
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadZones();
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
        $this->loadZones();
    }

    public function monthName()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F');
    }

    public function exportExcel()
    {
        $fileName = 'Administracion_' . strtoupper($this->company) . '_' . $this->monthName() . '_' . $this->year . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AdministrationExport($this->month, $this->year, $this->company), 
            $fileName
        );
    }

    public function with()
    {
        $operators = Operator::where('company', $this->company)->get();
        $totals = $this->calculateTotals($operators, $this->month, $this->year, false);

        // Filter operators to only show those with cost > 0
        $filteredOperators = $operators->filter(function($op) use ($totals) {
            return ($totals[$op->id]['coste_total'] ?? 0) > 0;
        });

        return [
            'operators' => $filteredOperators,
            'totals' => $totals,
            'currentMonthName' => $this->monthName(),
            'startDate' => Carbon::createFromDate($this->year, $this->month, 1)->format('d/m/Y'),
            'endDate' => Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('d/m/Y'),
        ];
    }
};
?>

<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header / Nav -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 font-outfit uppercase tracking-tight">Administración ({{ strtoupper($company) }})</h1>
            <p class="text-slate-500 text-sm">Resumen mensual de horas y costes por operario.</p>
        </div>

        <div class="flex items-center space-x-4 bg-slate-50 p-2 rounded-xl border border-slate-200">
            <button wire:click="prevMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition-all">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <div class="px-4 text-center min-w-[140px]">
                <span class="block text-xs font-bold text-slate-400 uppercase leading-none">{{ $year }}</span>
                <span class="text-sm font-bold text-slate-900 capitalize">{{ $currentMonthName }}</span>
            </div>
            <button wire:click="nextMonth" class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition-all">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
        
        <div class="flex items-center gap-3">
            <button wire:click="exportExcel" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors shadow-sm text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Exportar Excel
            </button>
        </div>
    </div>

    <!-- Info Section (Excel Style) -->
    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="space-y-2 max-w-2xl">
            <div class="grid grid-cols-[120px_1fr] items-center">
                <span class="font-bold text-slate-900 uppercase">EMPRESA:</span>
                <span class="text-indigo-600 font-bold border-b border-indigo-100 uppercase">{{ $company }}</span>
            </div>
            <div class="grid grid-cols-[120px_1fr_120px_1fr] items-center gap-4">
                <span class="font-bold text-slate-900 uppercase">MES:</span>
                <span class="text-indigo-600 font-bold border-b border-indigo-100 capitalize">{{ $currentMonthName }}</span>
                <span class="font-bold text-slate-900 uppercase">AÑO:</span>
                <span class="text-indigo-600 font-bold border-b border-indigo-100">{{ $year }}</span>
            </div>
            <div class="grid grid-cols-[120px_auto_auto_auto_auto_1fr] items-center gap-2">
                <span class="font-bold text-slate-900 uppercase">PERIODO:</span>
                <span class="text-slate-400 italic">Del</span>
                <span class="text-indigo-600 font-bold border-b border-indigo-100">{{ $startDate }}</span>
                <span class="text-slate-400 italic">al</span>
                <span class="text-indigo-600 font-bold border-b border-indigo-100">{{ $endDate }}</span>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden print:shadow-none print:border">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="p-4 bg-[#C6E0B4] text-left border border-slate-300 font-bold uppercase text-slate-900 min-w-[280px]">Operario</th>
                        <th class="p-4 bg-[#E2EFDA] text-center border border-slate-300 font-bold uppercase text-slate-900">Total Horas Mes (L-V)</th>
                        <th class="p-4 bg-[#F4B084] text-center border border-slate-300 font-bold uppercase text-slate-900">Total Horas Sábado</th>
                        <th class="p-4 bg-[#FF7C80] text-center border border-slate-300 font-bold uppercase text-slate-900">Total Horas Domingo</th>
                        <th class="p-4 bg-[#B1A0C7] text-center border border-slate-300 font-bold uppercase text-slate-900">Total Horas Festivos</th>
                        <th class="p-4 bg-[#70AD47] text-center border border-slate-300 font-bold uppercase text-white">Coste Mensual</th>
                        <th class="p-4 bg-[#BDD7EE] text-center border border-slate-300 font-bold uppercase text-slate-900">Zona</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($operators as $operator)
                        <tr wire:key="admin-op-{{ $operator->id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 bg-[#C6E0B4]/10 border border-slate-200 font-bold text-slate-800">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate">{{ $operator->name }}</span>
                                    <button 
                                        wire:click="deleteOperator({{ $operator->id }})"
                                        wire:confirm="¿Estás seguro de que deseas eliminar a este empleado y todos sus registros?"
                                        class="text-slate-400 hover:text-red-500 transition-colors p-1 print:hidden"
                                        title="Eliminar Empleado"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="p-4 text-center border border-slate-200">{{ $totals[$operator->id]['horas_lv'] }} h</td>
                            <td class="p-4 text-center border border-slate-200">{{ $totals[$operator->id]['horas_sab'] }} h</td>
                            <td class="p-4 text-center border border-slate-200">{{ $totals[$operator->id]['horas_dom'] }} h</td>
                            <td class="p-4 text-center border border-slate-200">{{ $totals[$operator->id]['horas_fest'] }} h</td>
                            <td class="p-4 text-center border border-slate-200 font-bold text-slate-900 bg-slate-50/50">
                                {{ number_format($totals[$operator->id]['coste_total'], 2, ',', '.') }} €
                            </td>
                            <td class="p-4 text-center border border-slate-300 bg-[#BDD7EE]/10 p-0">
                                <input type="text" 
                                    wire:model.live.debounce.500ms="zones.{{ $operator->id }}"
                                    placeholder="Sin zona"
                                    class="w-full h-full p-4 border-none bg-transparent text-center focus:ring-2 focus:ring-indigo-500 font-medium placeholder:text-slate-400 uppercase text-xs">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    .bg-white { border: none !important; shadow: none !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
