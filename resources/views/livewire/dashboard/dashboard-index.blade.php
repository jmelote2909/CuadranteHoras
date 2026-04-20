<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Setting;

new #[Layout('layouts.app')] class extends Component
{
    public $extraRateWeekday = 0;
    public $extraRateSaturday = 0;
    public $extraRateSunday = 0;

    public function mount()
    {
        $this->extraRateWeekday = Setting::getRate('extra_rate_weekday', 0);
        $this->extraRateSaturday = Setting::getRate('extra_rate_saturday', 0);
        $this->extraRateSunday = Setting::getRate('extra_rate_sunday', 0);
    }

    public function saveSettings()
    {
        $this->validate([
            'extraRateWeekday' => 'required|numeric|min:0',
            'extraRateSaturday' => 'required|numeric|min:0',
            'extraRateSunday' => 'required|numeric|min:0',
        ]);

        Setting::updateOrCreate(['key' => 'extra_rate_weekday'], ['value' => $this->extraRateWeekday]);
        Setting::updateOrCreate(['key' => 'extra_rate_saturday'], ['value' => $this->extraRateSaturday]);
        Setting::updateOrCreate(['key' => 'extra_rate_sunday'], ['value' => $this->extraRateSunday]);

        session()->flash('status', 'Configuración de horas extras guardada correctamente.');
    }
};
?>

<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header -->
    <div>
        <h1 class="text-4xl font-bold text-slate-900 font-outfit tracking-tight">Inicio</h1>
        <p class="text-slate-500 mt-2 text-lg">Resumen y configuraciones globales del sistema.</p>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Welcome Card -->
        <div class="bg-slate-900 rounded-3xl p-10 text-white relative overflow-hidden shadow-2xl h-full flex flex-col justify-center">
            <div class="relative z-10 max-w-lg">
                <h2 class="text-3xl font-bold font-outfit mb-4">Gestión de Empleados</h2>
                <p class="text-slate-400 text-lg mb-8">Navega a la sección de ARANCALO para registrar las horas trabajadas diarias, añadir nuevos empleados y ver los costes totales automáticos.</p>
                <a href="{{ route('schedule') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-bold transition-all duration-200 shadow-lg shadow-indigo-600/30">
                    Ir a ARANCALO
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            
            <!-- Abstract Decoration -->
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
            <div class="absolute right-20 bottom-0 w-32 h-32 bg-indigo-400/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Settings Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center text-orange-600 mr-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900 font-outfit">Coste de Horas Extras</h3>
                    <p class="text-sm text-slate-500">Configura el precio base global aplicable.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl text-sm font-medium border border-green-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="saveSettings" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Lunes a Viernes</label>
                    <div class="relative">
                        <input type="number" step="0.01" wire:model="extraRateWeekday" class="w-full rounded-xl border-slate-200 focus:ring-orange-500 focus:border-orange-500 pl-4 pr-10 shadow-sm" required>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 font-medium">€ / h</div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-2">Sábados</label>
                    <div class="relative">
                        <input type="number" step="0.01" wire:model="extraRateSaturday" class="w-full rounded-xl border-orange-200 focus:ring-orange-500 focus:border-orange-500 bg-orange-50/30 pl-4 pr-10 shadow-sm" required>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 font-medium">€ / h</div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-rose-600 uppercase tracking-wider mb-2">Domingos y Festivos</label>
                    <div class="relative">
                        <input type="number" step="0.01" wire:model="extraRateSunday" class="w-full rounded-xl border-rose-200 focus:ring-rose-500 focus:border-rose-500 bg-rose-50/30 pl-4 pr-10 shadow-sm" required>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 font-medium">€ / h</div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition-colors shadow-sm">
                        Guardar Precios Globales
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>