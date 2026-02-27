<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmallPay')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('head')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40 md:hidden"></div>

        <aside id="sidebar" class="w-64 bg-gray-900 text-gray-100 fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-transform duration-200 md:translate-x-0 md:static md:flex md:flex-col">
            <div class="px-6 py-5 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="font-bold leading-tight">SmallPay</div>
                        <div class="text-xs text-gray-400">Panneau d'administration</div>
                    </div>
                    </div>
                    <button type="button" id="sidebar-close" class="md:hidden w-10 h-10 rounded-lg hover:bg-gray-800 flex items-center justify-center">
                        <i class="fas fa-times text-gray-200"></i>
                    </button>
                </div>
            </div>

            <nav class="flex-1 px-3 md:px-4 py-3 md:py-4 space-y-1" data-sidebar-links>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                    <i class="fas fa-chart-line w-5 text-center"></i>
                    <span class="text-sm md:text-base">Tableau de bord</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                    <i class="fas fa-box w-5 text-center"></i>
                    <span class="text-sm md:text-base">Produits</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                    <i class="fas fa-receipt w-5 text-center"></i>
                    <span class="text-sm md:text-base">Commandes</span>
                </a>
                <a href="{{ route('admin.notifications.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                    <i class="fas fa-bell w-5 text-center"></i>
                    <span class="text-sm md:text-base">Notifications</span>
                </a>
                @if ((auth()->user()->role ?? null) === 'super_admin')
                    <a href="{{ route('admin.kyc.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-id-card w-5 text-center"></i>
                        <span class="text-sm md:text-base">Vérifications (KYC)</span>
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-tags w-5 text-center"></i>
                        <span class="text-sm md:text-base">Catégories</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-users w-5 text-center"></i>
                        <span class="text-sm md:text-base">Utilisateurs</span>
                    </a>
                    <a href="{{ route('admin.merchants.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-store w-5 text-center"></i>
                        <span class="text-sm md:text-base">Marchands</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span class="text-sm md:text-base">Rapports</span>
                    </a>
                    <a href="{{ route('admin.activities.index') }}" class="flex items-center gap-3 px-3 py-3 md:py-2 rounded-lg hover:bg-gray-800 min-h-[44px]">
                        <i class="fas fa-history w-5 text-center"></i>
                        <span class="text-sm md:text-base">Activités</span>
                    </a>
                @endif
            </nav>

            <div class="px-3 md:px-4 py-3 md:py-4 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-3 md:py-2 rounded-lg bg-gray-800 hover:bg-gray-700 min-h-[44px]">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="text-sm md:text-base">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <header class="bg-white border-b border-gray-200">
                <div class="px-3 md:px-6 py-3 md:py-4 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 md:gap-3 min-w-0">
                        <button type="button" id="sidebar-open" class="md:hidden w-10 h-10 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 flex-shrink-0">
                            <i class="fas fa-bars text-gray-700"></i>
                        </button>
                        <div class="text-base md:text-xl font-bold text-gray-900 truncate">@yield('header', '')</div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $unreadCount = \App\Models\Notification::byUser(auth()->id())->unread()->count();
                            $recentNotifications = \App\Models\Notification::byUser(auth()->id())
                                ->orderByDesc('created_at')
                                ->limit(7)
                                ->get();
                        @endphp

                        <div class="relative">
                            <button type="button" id="notif-btn" class="relative w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50">
                                <i class="fas fa-bell text-gray-700"></i>
                                @if ($unreadCount > 0)
                                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-xs flex items-center justify-center">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="notif-menu" class="hidden absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden z-50">
                                <div class="px-4 py-3 border-b flex items-center justify-between">
                                    <div class="font-semibold text-gray-900">Notifications</div>
                                    <form method="POST" action="{{ route('admin.notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="text-sm text-blue-600 hover:underline">Tout marquer comme lu</button>
                                    </form>
                                </div>

                                <div class="max-h-96 overflow-y-auto">
                                    @forelse ($recentNotifications as $notification)
                                        <div class="px-4 py-3 border-b last:border-b-0 {{ $notification->is_read ? 'bg-white' : 'bg-blue-50' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $notification->title }}</div>
                                                    <div class="text-sm text-gray-600">{{ $notification->message }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">{{ optional($notification->created_at)->diffForHumans() }}</div>
                                                </div>
                                                @if (!$notification->is_read)
                                                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs text-blue-600 hover:underline">Marquer lu</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-6 text-center text-sm text-gray-500">Aucune notification</div>
                                    @endforelse
                                </div>

                                <a href="{{ route('admin.notifications.index') }}" class="block px-4 py-3 text-sm text-blue-600 hover:bg-gray-50">
                                    Voir toutes les notifications
                                </a>
                            </div>
                        </div>

                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? '') }}&size=64"
                            alt="{{ auth()->user()->name ?? '' }}"
                            class="w-9 h-9 rounded-full border border-gray-200"
                        >
                        <div class="leading-tight">
                            <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->name ?? '' }}</div>
                            <div class="text-xs text-gray-500">{{ strtoupper(auth()->user()->role ?? '') }}</div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-6">
                @if (session('success'))
                    <div id="toast-success" class="fixed top-5 right-5 z-50 max-w-md bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3">
                        <i class="fas fa-check-circle mt-0.5"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
                        <button type="button" class="text-white/90 hover:text-white" onclick="document.getElementById('toast-success')?.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div id="toast-error" class="fixed top-5 right-5 z-50 max-w-md bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
                        <button type="button" class="text-white/90 hover:text-white" onclick="document.getElementById('toast-error')?.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')

    <script>
        (function () {
            const success = document.getElementById('toast-success');
            const error = document.getElementById('toast-error');
            const el = error || success;
            if (!el) return;
            setTimeout(() => {
                el.remove();
            }, 4500);
        })();

        (function () {
            const btn = document.getElementById('notif-btn');
            const menu = document.getElementById('notif-menu');
            if (!btn || !menu) return;

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });

            document.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            });

            menu.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        })();

        (function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('sidebar-open');
            const closeBtn = document.getElementById('sidebar-close');
            const linksWrap = document.querySelector('[data-sidebar-links]');
            if (!sidebar || !overlay || !openBtn) return;

            const open = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            };

            const close = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            };

            openBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                open();
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', close);
            }

            if (linksWrap) {
                linksWrap.addEventListener('click', (e) => {
                    const target = e.target;
                    if (!(target instanceof Element)) return;
                    const link = target.closest('a');
                    if (!link) return;
                    if (window.innerWidth < 768) close();
                });
            }

            overlay.addEventListener('click', close);

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    overlay.classList.add('hidden');
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        })();

        // Gestion des onglets actifs
        (function() {
            const currentPath = window.location.pathname;
            const links = document.querySelectorAll('[data-sidebar-links] a');

            links.forEach(link => {
                const href = link.getAttribute('href');
                if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                    link.classList.add('bg-gray-800', 'text-white');
                    link.classList.remove('hover:bg-gray-800');
                } else {
                    link.classList.remove('bg-gray-800', 'text-white');
                    link.classList.add('hover:bg-gray-800');
                }
            });
        })();
    </script>
</body>
</html>
