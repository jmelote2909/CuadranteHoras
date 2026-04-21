<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Setting;
use App\Models\Holiday;

new #[Layout('layouts.app')] class extends Component
{
    public $extraRateWeekday = 0;
    public $extraRateSaturday = 0;
    public $extraRateSunday = 0;
    public $newHolidayDate;
    public $newHolidayName;

    public function mount()
    {
        $this->extraRateWeekday = Setting::getRate('extra_rate_weekday', 0);
        $this->extraRateSaturday = Setting::getRate('extra_rate_saturday', 0);
        $this->extraRateSunday = Setting::getRate('extra_rate_sunday', 0);
    }

    public function saveHoliday()
    {
        $this->validate([
            'newHolidayDate' => 'required|date|unique:holidays,date',
            'newHolidayName' => 'nullable|string|max:255',
        ]);

        Holiday::create([
            'date' => $this->newHolidayDate,
            'name' => $this->newHolidayName,
        ]);

        $this->reset(['newHolidayDate', 'newHolidayName']);
        session()->flash('status_holiday', 'Festivo añadido correctamente.');
    }

    public function deleteHoliday($id)
    {
        Holiday::findOrFail($id)->delete();
        session()->flash('status_holiday', 'Festivo eliminado correctamente.');
    }

    public function getHolidaysProperty()
    {
        return Holiday::orderBy('date')->get();
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

        <!-- Holidays Management Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 flex flex-col h-full">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 font-outfit">Gestión de Festivos</h3>
                        <p class="text-sm text-slate-500">Añade o elimina días no laborables.</p>
                    </div>
                </div>
            </div>

            @if (session('status_holiday'))
                <div class="mb-6 bg-indigo-50 text-indigo-700 p-4 rounded-xl text-sm font-medium border border-indigo-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    {{ session('status_holiday') }}
                </div>
            @endif

            <form wire:submit="saveHoliday" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Fecha</label>
                    <input type="date" wire:model="newHolidayDate" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" required>
                    @error('newHolidayDate') <span class="text-rose-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nombre (Opcional)</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="newHolidayName" placeholder="P. ej. Navidad" class="w-full rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                        <button type="submit" class="p-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </button>
                    </div>
                </div>
            </form>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar" style="max-height: 250px;">
                @if($this->holidays->isEmpty())
                    <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-slate-400 text-sm">No hay festivos registrados.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($this->holidays as $holiday)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-slate-200 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="bg-white p-2.5 rounded-xl shadow-sm text-center min-w-[50px]">
                                        <span class="block text-[10px] font-bold text-indigo-600 uppercase">{{ $holiday->date->translatedFormat('M') }}</span>
                                        <span class="block text-lg font-bold text-slate-900 leading-none">{{ $holiday->date->format('d') }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $holiday->name ?: 'Festivo' }}</p>
                                        <p class="text-xs text-slate-500">{{ $holiday->date->translatedFormat('l, Y') }}</p>
                                    </div>
                                </div>
                                <button wire:click="deleteHoliday({{ $holiday->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
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
                    <label class="block text-xs font-bold text-rose-600 uppercase tracking-wider mb-2">Domingos</label>
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