<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.guest')] class extends Component 
{
    public $username = '';
    public $password = '';
    public $remember = false;

    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'username' => 'Las credenciales proporcionadas son incorrectas.',
            ]);
        }

        session()->regenerate();

        return redirect()->intended(route('inicio', absolute: false));
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="w-full max-w-md animate-in fade-in zoom-in duration-500">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl shadow-xl shadow-indigo-500/20 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 font-outfit uppercase tracking-wider">ARANCALO</h1>
            <p class="text-slate-500 text-sm mt-1">Sistemas de Gestión de Horas</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6">Iniciar Sesión</h2>

                <form wire:submit="login" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Usuario</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </span>
                            <input wire:model="username" type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all text-sm" placeholder="Ej: rrhh" required autofocus>
                        </div>
                        @error('username') <span class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </span>
                            <input wire:model="password" type="password" class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all text-sm" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input wire:model="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-slate-500">Recordarme</span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2 group">
                            <span>Ingresar al Sistema</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400 italic">Acceso restringido a personal autorizado.</p>
            </div>
        </div>
        
        <p class="text-center mt-8 text-slate-400 text-xs">© {{ date('Y') }} ARANCALO Desktop App</p>
    </div>
</div>
