<aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col h-full shadow-2xl border-r border-slate-800">
    <!-- Brand / Logo -->
    <div class="px-6 py-8 flex items-center space-x-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <span class="text-xl font-bold text-white tracking-tight font-outfit uppercase tracking-widest">ARANCALO</span>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 space-y-2 mt-4">
        <!-- Inicio Link -->
        <a href="{{ route('inicio') }}" 
           class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('inicio') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('inicio') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400 transition-colors' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Inicio
        </a>

        <!-- Cuadrante Link -->
        <a href="{{ route('schedule') }}" 
           class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('schedule') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('schedule') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400 transition-colors' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            ARANCALO
        </a>

        <!-- Días Amarillos (ARANCALO) Link -->
        <a href="{{ route('amarillos.arancalo') }}" 
           class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('amarillos.arancalo') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('amarillos.arancalo') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400 transition-colors' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            DÍAS AMARILLOS (ARANCALO)
        </a>

        <!-- Cima Link -->
        <a href="{{ route('cima') }}" 
           class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('cima') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('cima') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400 transition-colors' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            CIMA CABLEADOS
        </a>

        <!-- Días Amarillos (CIMA) Link -->
        <a href="{{ route('amarillos.cima') }}" 
           class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 {{ request()->routeIs('amarillos.cima') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('amarillos.cima') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400 transition-colors' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            DÍAS AMARILLOS (CIMA)
        </a>
    </nav>

    <!-- App Info / Footer -->
    <div class="p-6 border-t border-slate-800">
        <div class="bg-slate-800/50 rounded-xl p-4">
            <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">Status</p>
            <div class="flex items-center">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                <span class="text-xs text-slate-400">Desktop Connected</span>
            </div>
        </div>
    </div>
</aside>
