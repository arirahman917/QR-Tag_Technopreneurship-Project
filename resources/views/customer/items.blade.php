<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Pribadi - {{ $data['item_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .theme-bg { background-color: #93c5fd; } /* Blue theme */
        .theme-btn { background-color: #334155; color: white; } /* Dark slate blue */
        .theme-btn:hover { background-color: #1e293b; }
        .theme-badge { background-color: #dbeafe; color: #1e3a8a; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center">
    <!-- Header Area -->
    <div class="w-full theme-bg text-center rounded-b-[2rem] py-4 mb-8 max-w-2xl">
        <h1 class="text-sm font-semibold text-slate-800">Barang Pribadi</h1>
    </div>

    <div class="w-full max-w-md px-6 flex flex-col items-center mb-10">
        
        <!-- Name -->
        <h2 class="text-3xl font-bold text-slate-900 mb-4 text-center">{{ $data['item_name'] }}</h2>

        <!-- Description Badge -->
        @if(!empty($data['description']))
        <div class="theme-badge px-4 py-1.5 rounded-full text-xs font-medium mb-6 shadow-sm text-center max-w-xs">
            {{ $data['description'] }}
        </div>
        @endif

        <!-- Photo -->
        <div class="relative w-48 h-48 rounded-full shadow-md overflow-hidden mb-8 bg-slate-50 border border-slate-100 flex items-center justify-center">
            @if($data['photo_url'])
                <img src="{{ $data['photo_url'] }}" alt="{{ $data['item_name'] }}" class="w-full h-full object-cover">
            @else
                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            @endif
        </div>

        <!-- Emergency Message Card -->
        @if(!empty($data['emergency_message']))
        <div class="w-full bg-slate-50 rounded-2xl p-5 mb-8 text-center border border-slate-200 shadow-sm">
            <p class="text-sm font-medium text-slate-800 leading-relaxed">{{ $data['emergency_message'] }}</p>
        </div>
        @endif

        <!-- Details List -->
        <div class="w-full mb-8">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Detail Identifikasi:</h3>
            <ul class="space-y-2 text-sm text-slate-700 list-disc list-inside px-1 marker:text-slate-800">
                @if(!empty($data['distinctive_mark']))
                    <li><strong class="text-slate-900">Ciri unik:</strong> {{ is_array($data['distinctive_mark']) ? implode(', ', $data['distinctive_mark']) : $data['distinctive_mark'] }}</li>
                @endif
                @if(!empty($data['important_contents']))
                    <li><strong class="text-slate-900">Catatan isi barang:</strong> {{ is_array($data['important_contents']) ? implode(', ', $data['important_contents']) : $data['important_contents'] }}</li>
                @endif
                @if(!empty($data['owner_area']))
                    <li><strong class="text-slate-900">Lokasi pemilik:</strong> {{ is_array($data['owner_area']) ? implode(', ', $data['owner_area']) : $data['owner_area'] }}</li>
                @endif
                @if(!empty($data['reward']))
                    <li><strong class="text-slate-900">Reward:</strong> {{ is_array($data['reward']) ? implode(', ', $data['reward']) : $data['reward'] }}</li>
                @endif
            </ul>
        </div>

        <!-- Action Button -->
        <a href="{{ $data['whatsapp_url'] }}" target="_blank" class="w-full theme-btn py-3.5 rounded-3xl font-bold text-sm text-center shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            HUBUNGI PEMILIK
        </a>
    </div>
</body>
</html>
