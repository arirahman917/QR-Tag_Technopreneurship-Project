<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identitas Personal - {{ $data['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .theme-bg { background-color: #6ee7b7; } /* Green theme */
        .theme-btn { background-color: #059669; color: white; }
        .theme-btn:hover { background-color: #047857; }
        .theme-badge { background-color: #d1fae5; color: #065f46; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center">
    <!-- Header Area -->
    <div class="w-full theme-bg text-center py-4 mb-8 max-w-2xl rounded-b-[2rem]">
        <h1 class="text-sm font-semibold text-slate-800">Identitas Personal</h1>
    </div>

    <div class="w-full max-w-md px-6 flex flex-col items-center mb-10">
        
        <!-- Name -->
        <h2 class="text-3xl font-bold text-slate-900 mb-4 text-center">{{ $data['name'] }}</h2>

        <!-- Condition Badge -->
        @if(!empty($data['condition']))
        <div class="theme-badge px-4 py-1.5 rounded-full text-xs font-semibold mb-6 shadow-sm">
            {{ $data['condition'] }}
        </div>
        @endif

        <!-- Photo -->
        <div class="relative w-48 h-48 rounded-full border border-slate-100 shadow-lg overflow-hidden mb-8 bg-slate-100 flex items-center justify-center">
            @if($data['photo_url'])
                <img src="{{ $data['photo_url'] }}" alt="{{ $data['name'] }}" class="w-full h-full object-cover">
            @else
                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            @endif
        </div>

        <!-- Emergency Message Card -->
        @if(!empty($data['emergency_message']))
        <div class="w-full bg-slate-100 rounded-2xl p-5 mb-8 text-center border border-slate-200">
            <p class="text-sm font-medium text-slate-800 leading-relaxed">{{ $data['emergency_message'] }}</p>
        </div>
        @endif

        <!-- Details List -->
        <div class="w-full mb-8">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Detail Identifikasi:</h3>
            <ul class="space-y-2 text-sm text-slate-700 list-disc list-inside px-1 marker:text-slate-800">
                @if(!empty($data['physical_description']))
                    <li><strong class="text-slate-900">Ciri fisik singkat:</strong> {{ is_array($data['physical_description']) ? implode(', ', $data['physical_description']) : $data['physical_description'] }}</li>
                @endif
                @if(!empty($data['age']))
                    <li><strong class="text-slate-900">Usia perkiraan:</strong> {{ is_array($data['age']) ? implode(', ', $data['age']) : $data['age'] }}</li>
                @endif
                @if(!empty($data['languages']))
                    <li><strong class="text-slate-900">Bahasa yang dipahami:</strong> {{ is_array($data['languages']) ? implode(', ', $data['languages']) : $data['languages'] }}</li>
                @endif
                @if(!empty($data['general_area']))
                    <li><strong class="text-slate-900">Asal lokasi:</strong> {{ is_array($data['general_area']) ? implode(', ', $data['general_area']) : $data['general_area'] }}</li>
                @endif
                @if(!empty($data['notes']))
                    <li><strong class="text-slate-900">Catatan bantu:</strong> {{ is_array($data['notes']) ? implode(', ', $data['notes']) : $data['notes'] }}</li>
                @endif
            </ul>
        </div>

        <!-- Action Button -->
        <a href="{{ $data['whatsapp_url'] }}" target="_blank" class="w-full theme-btn py-3.5 rounded-full font-bold text-sm text-center shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            HUBUNGI KELUARGA
        </a>
    </div>
</body>
</html>
