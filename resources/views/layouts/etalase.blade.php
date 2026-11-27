<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TokoKita — Etalase Belanja')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 text-stone-800 min-h-screen flex flex-col">

    {{-- Navbar Atas --}}
    <header class="bg-[#A8380F] text-white shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('etalase.index') }}" class="text-xl font-bold tracking-tight shrink-0">
                TokoKita
            </a>

            {{-- Pencarian Produk --}}
            <form action="{{ route('etalase.index') }}" method="GET" class="flex-1 max-w-xl">
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                @if(request('min'))
                    <input type="hidden" name="min" value="{{ request('min') }}">
                @endif
                @if(request('max'))
                    <input type="hidden" name="max" value="{{ request('max') }}">
                @endif
                <input
                    type="text"
                    name="cari"
                    value="{{ request('cari') }}"
                    placeholder="Cari produk..."
                    class="w-full px-4 py-1.5 rounded-md bg-white text-stone-800 placeholder-stone-400 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                >
            </form>

            {{-- Navigasi Kanan --}}
            <div class="flex items-center gap-4 text-sm font-medium shrink-0">
                @php
                    $totalUnitKeranjang = array_sum(array_column(session('keranjang', []), 'jumlah'));
                @endphp
                <a href="#keranjang" class="hover:text-orange-200 transition">
                    Keranjang ({{ $totalUnitKeranjang }})
                </a>
                <span class="opacity-40">|</span>
                <a href="{{ route('admin.dashboard') }}" class="bg-white/15 hover:bg-white/25 px-3 py-1.5 rounded transition">
                    Panel Admin
                </a>
            </div>
        </div>
    </header>

    {{-- Notifikasi Sukses / Gagal --}}
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 mt-4">
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
    </div>

    {{-- Area Konten Utama --}}
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-4 flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white py-4 mt-10 text-center text-xs text-stone-500">
        TokoKita &copy; 2026 — Mini Proyek Basis Data Lanjut (Kelompok 3)
    </footer>

    @stack('scripts')
</body>
</html>