<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmallPay Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar-open .fixed-sidebar {
            transform: translateX(0);
        }
    </style>
    @stack('head')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Mobile Toggle Button -->
        <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 bg-gray-900 text-white p-2 rounded-lg">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <aside class="fixed-sidebar md:relative w-64 bg-gray-900 text-gray-100 transform -translate-x-full md:translate-x-0 transition-transform duration-300 h-screen md:h-auto md:flex md:flex-col z-40">
            <div class="px-6 py-5 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold leading-tight truncate">SmallPay</div>
                        <div class="text-xs text-gray-400">Admin Panel</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 transition whitespace-nowrap">
                    <i class="fas fa-chart-line w-5 flex-shrink-0"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 transition whitespace-nowrap">
                    <i class="fas fa-box w-5 flex-shrink-0"></i>
                    <span>Produits</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 transition whitespace-nowrap">
                    <i class="fas fa-receipt w-5 flex-shrink-0"></i>
                    <span>Commandes</span>
                </a>
                @if(auth()->user() && auth()->user()->role === 'super_admin')
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 transition whitespace-nowrap">
                    <i class="fas fa-users w-5 flex-shrink-0"></i>
                    <span>Utilisateurs</span>
                </a>
                @endif
            </nav>

            <div class="px-4 py-4 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition text-sm md:text-base">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col w-full">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
                <div class="px-4 md:px-6 py-4 flex items-center justify-between gap-4">
                    <div class="text-lg md:text-xl font-bold text-gray-900 truncate">@yield('header', 'Dashboard')</div>
                    
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Notifications -->
                        <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 relative transition">
                            <i class="fas fa-bell"></i>
                            <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>

                        <!-- User Menu (hide on small screens) -->
                        <div class="hidden sm:flex items-center space-x-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name ?? '' }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium flex-shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-auto p-4 md:p-6">
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm md:text-base">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm md:text-base">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden z-30"></div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.fixed-sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        sidebarToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('sidebar-open');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            document.documentElement.classList.remove('sidebar-open');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        // Close sidebar when clicking a link
        document.querySelectorAll('.fixed-sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                document.documentElement.classList.remove('sidebar-open');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
