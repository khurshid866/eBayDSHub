<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'eBay Dropshipping Hub') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts & Tailwind CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js & Chart.js & FontAwesome -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        input[type="date"] {
            color-scheme: dark;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.8) sepia(100%) hue-rotate(190deg) saturate(500%);
            cursor: pointer;
            padding: 2px;
            opacity: 0.9;
        }
        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            filter: invert(1);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col md:flex-row min-h-screen">

    <!-- Mobile Header Navigation -->
    <div class="md:hidden bg-slate-900 border-b border-slate-800 p-4 flex items-center justify-between sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            @if(Auth::user()?->company?->logo)
                <img src="{{ asset(Auth::user()->company->logo) }}" alt="Logo" class="h-9 w-auto max-w-[120px] object-contain rounded-lg">
            @else
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
            @endif
            <span class="font-bold text-lg text-white tracking-wide">eBay Hub</span>
        </div>
        <button @click="openMobileMenu = !openMobileMenu" x-data="{ openMobileMenu: false }" class="text-slate-400 hover:text-white p-2">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-slate-900/90 backdrop-blur-xl border-r border-slate-800/80 flex flex-col justify-between shrink-0">
        <div>
            <!-- Brand Header -->
            <div class="p-6 hidden md:flex items-center space-x-3 border-b border-slate-800/60">
                @if(Auth::user()?->company?->logo)
                    <img src="{{ asset(Auth::user()->company->logo) }}" alt="{{ Auth::user()->company->name }}" class="h-10 w-auto max-w-[140px] object-contain rounded-lg">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/25">
                        <i class="fa-solid fa-bolt text-xl"></i>
                    </div>
                @endif
                <div>
                    <h1 class="font-extrabold text-white tracking-tight leading-none text-base">{{ Auth::user()?->company?->name ?: 'eBay Profit Hub' }}</h1>
                    <span class="text-xs font-medium text-slate-400">Dropshipping System</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                @if(Auth::user()?->hasPermission('nav_dashboard'))
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-blue-400"></i>
                    <span>Dashboard</span>
                </a>
                @endif

                @if(Auth::user()?->hasPermission('nav_orders'))
                <a href="{{ route('orders.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('orders.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-cart-shopping w-5 text-center text-indigo-400"></i>
                    <span>Orders</span>
                </a>
                @endif

                @if(Auth::user()?->hasPermission('nav_import'))
                <a href="{{ route('import.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('import.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-file-excel w-5 text-center text-emerald-400"></i>
                    <span>Import Excel</span>
                </a>
                @endif

                @if(Auth::user()?->hasPermission('nav_reports'))
                <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-chart-area w-5 text-center text-purple-400"></i>
                    <span>Reports & PDF</span>
                </a>
                @endif

                @if(Auth::user()?->hasPermission('nav_audit_logs'))
                <a href="{{ route('audit_logs.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('audit_logs.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-shield-halved w-5 text-center text-amber-400"></i>
                    <span>Audit Logs</span>
                </a>
                @endif

                @if(Auth::user()?->isSuperAdmin() || session('original_superadmin_id'))
                <div class="pt-4 pb-1">
                    <span class="px-4 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Super Admin Portal</span>
                </div>

                <a href="{{ route('companies.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('companies.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-building-user w-5 text-center text-amber-400"></i>
                    <span>Companies</span>
                </a>

                <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center text-teal-400"></i>
                    <span>All System Users</span>
                </a>
                @elseif(Auth::user()?->isCompanyAdmin())
                <div class="pt-4 pb-1">
                    <span class="px-4 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Company Admin</span>
                </div>

                <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-users w-5 text-center text-teal-400"></i>
                    <span>Manage Operators</span>
                </a>

                <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 {{ request()->routeIs('settings.*') ? 'bg-blue-600/15 text-blue-400 border border-blue-500/30 shadow-inner' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-rose-400"></i>
                    <span>Company Settings</span>
                </a>
                @endif
            </nav>
        </div>

        <!-- User Profile Card in Sidebar -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/50">
            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-lg bg-blue-600/20 border border-blue-500/40 flex items-center justify-center font-bold text-blue-400 shrink-0 text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-semibold text-white truncate leading-tight">{{ Auth::user()->name ?? 'User' }}</p>
                        <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">
                            {{ Auth::user()->role === 'CompanyAdmin' ? 'Admin' : (Auth::user()->role === 'SuperAdmin' ? 'Super Admin' : 'Operator') }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign Out" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700/50 rounded-lg transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 bg-slate-950 overflow-y-auto">

        <!-- Active Impersonation Alert Banner -->
        @if(session('original_superadmin_id'))
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 text-white px-6 py-2.5 flex items-center justify-between text-xs font-bold shadow-lg z-50">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-user-secret text-base"></i>
                <span>Currently accessing portal as <u>{{ Auth::user()->name }}</u> ({{ Auth::user()->role === 'CompanyAdmin' ? 'Admin' : Auth::user()->role }} - {{ Auth::user()->company?->name ?: 'Global' }})</span>
            </div>

            <form method="POST" action="{{ route('impersonate.leave') }}">
                @csrf
                <button type="submit" class="px-3 py-1 bg-slate-950 hover:bg-slate-900 text-amber-300 rounded-lg border border-amber-400/40 text-[11px] font-extrabold transition">
                    <i class="fa-solid fa-right-to-bracket"></i> Return to Super Admin Account
                </button>
            </form>
        </div>
        @endif

        <!-- Top App Bar -->
        <header class="h-16 bg-slate-900/40 border-b border-slate-800/60 backdrop-blur-md px-6 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-bold text-white tracking-tight">@yield('title', 'Dashboard')</h2>
                
                @if(Auth::user()?->company)
                <span class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <i class="fa-solid fa-building text-[10px]"></i>
                    <span>{{ Auth::user()->company->name }}</span>
                </span>
                @endif
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Super Admin Company Context Switcher Dropdown -->
                @if(Auth::user()?->isSuperAdmin())
                <form method="POST" action="{{ route('companies.switch') }}" class="flex items-center space-x-2">
                    @csrf
                    <span class="text-xs text-amber-400 font-bold uppercase tracking-wider hidden lg:inline"><i class="fa-solid fa-building text-xs"></i> Scope:</span>
                    <select name="company_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-slate-900 border border-amber-500/40 rounded-xl text-xs text-amber-300 font-semibold focus:ring-amber-500">
                        <option value="all" {{ !session('active_company_id') ? 'selected' : '' }}>🏢 All Companies (Global)</option>
                        @foreach(\App\Models\Company::where('status', 'active')->orderBy('name')->get() as $comp)
                            <option value="{{ $comp->id }}" {{ session('active_company_id') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                @if(Auth::user()?->hasPermission('action_create_order'))
                <a href="{{ route('orders.create') }}" class="px-3.5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>New Order</span>
                </a>
                @endif
            </div>
        </header>

        <!-- Flash Messages & Toasts -->
        <div class="p-6 max-w-7xl w-full mx-auto space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center space-x-3 shadow-lg shadow-emerald-500/5">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center space-x-3 shadow-lg shadow-rose-500/5">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
