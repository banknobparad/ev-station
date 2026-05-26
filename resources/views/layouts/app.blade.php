<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'EV Station') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>

<body>

    @auth
        @if (auth()->user()->role === 'driver')
            <main class="driver-content">
                @yield('content')
            </main>

            <nav class="bottom-nav">
                <a href="{{ route('driver.map') }}"
                    class="bottom-nav-item {{ request()->routeIs('driver.map') ? 'active' : '' }}">
                    <i class="bi bi-map-fill"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('driver.favorites') }}"
                    class="bottom-nav-item {{ request()->routeIs('driver.favorites') ? 'active' : '' }}">
                    <i class="bi bi-bookmark-fill"></i>
                    <span>Saved</span>
                </a>
                <a href="#" class="bottom-nav-item">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>My Booking</span>
                </a>
                <a href="#" class="bottom-nav-item">
                    <i class="bi bi-person-fill"></i>
                    <span>Account</span>
                </a>
            </nav>
        @else
            <nav class="navbar-ev d-flex align-items-center justify-content-between flex-wrap gap-2">
                <a class="navbar-brand-ev" href="/">
                    <i class="bi bi-lightning-charge-fill"></i>
                    EV<span>Station</span>
                </a>

                <div class="d-flex align-items-center gap-1 flex-wrap">
                    @if (auth()->user()->role === 'provider')
                        <a href="{{ route('provider.dashboard') }}" class="nav-link-ev">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a>
                        <a href="{{ route('provider.stations.index') }}" class="nav-link-ev">
                            <i class="bi bi-ev-station"></i>สถานีของฉัน
                        </a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link-ev">
                            <i class="bi bi-grid"></i>Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="nav-link-ev">
                            <i class="bi bi-people"></i>Provider
                        </a>
                        <a href="{{ route('admin.reviews.index') }}" class="nav-link-ev">
                            <i class="bi bi-chat-dots"></i>Comment
                        </a>
                    @endif

                    <div class="dropdown ms-2">
                        <button class="btn-ev-outline dropdown-toggle d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <span class="role-badge role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted small px-3">
                                    {{ auth()->user()->email ?? '-' }}
                                </span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="py-4">
                @yield('content')
            </main>
        @endif
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Flash messages — ต้องอยู่ที่นี่เพราะใช้ Blade --}}
    <script>
        @if (session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        @if (session('error'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>
