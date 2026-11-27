<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TokoKita Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-stone-100 text-stone-800 min-h-screen flex flex-col">

    {{-- Navbar Atas Admin --}}
    <header class="bg-[#A8380F] text-white h-14 px-6 flex items-center justify-between shadow-sm sticky top-0 z-30">
        <div class="flex items-center gap-3">
            <span class="text-lg font-bold tracking-tight">TokoKita Admin</span>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('etalase.index') }}" class="hover:underline text-orange-100">Lihat Etalase</a>
            <span class="opacity-40">|</span>
            <span>Pemilik Toko</span>
        </div>
    </header>

    <div class="flex flex-1">
        {{-- Sidebar Navigasi --}}
        <aside class="w-56 bg-[#FAF3EF] border-r border-stone-200 p-4 shrink-0">
            <nav class="space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-[#A8380F] text-white' : 'text-stone-700 hover:bg-orange-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.pesanan.index') }}"
                   class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.pesanan.*') ? 'bg-[#A8380F] text-white' : 'text-stone-700 hover:bg-orange-100' }}">
                    Pesanan
                </a>
                <a href="{{ route('admin.pelanggan.index') }}"
                   class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.pelanggan.*') ? 'bg-[#A8380F] text-white' : 'text-stone-700 hover:bg-orange-100' }}">
                    Pelanggan
                </a>
                <a href="{{ route('admin.produk.index') }}"
                   class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.produk.*') ? 'bg-[#A8380F] text-white' : 'text-stone-700 hover:bg-orange-100' }}">
                    Produk
                </a>
            </nav>
        </aside>

        {{-- Konten Utama Admin --}}
        <main class="flex-1 p-6 overflow-x-auto">
            {{-- Notifikasi Sukses / Gagal --}}
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>