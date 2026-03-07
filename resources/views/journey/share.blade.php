<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900">旅の結果をシェア</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4">
            {{-- 御札カード --}}
            <div id="share-card" class="card mb-6 bg-gradient-to-b from-amber-50 to-white">
                <div class="p-8 text-center">
                    <p class="text-xs text-amber-500 mb-4 tracking-widest">おかげ犬 御札</p>

                    <img src="{{ asset('storage/' . $dog->photo_path) }}" alt="{{ $dog->name }}"
                        class="w-20 h-20 rounded-full object-cover mx-auto mb-3 border-3 border-amber-200">

                    <h3 class="text-lg font-bold text-amber-900 mb-1" style="font-family: serif;">{{ $dog->name }}</h3>
                    <p class="text-xs text-amber-600 mb-4">{{ $dog->breed }} / {{ $dog->age }}歳</p>

                    <div class="w-16 h-px bg-amber-200 mx-auto mb-4"></div>

                    <p class="text-sm text-amber-800 mb-4" style="font-family: serif;">「{{ $journey->wish }}」</p>

                    <div class="space-y-1 text-xs text-amber-600">
                        <p>{{ $journey->departure_name }} → 伊勢神宮</p>
                        <p>{{ number_format($journey->distance_km, 1) }}km / {{ $journey->estimated_days }}日間の旅</p>
                        <p>{{ $journey->departed_at->format('Y年m月d日') }} 出立</p>
                    </div>

                    <div class="mt-4 text-2xl">⛩️</div>
                    <p class="text-xs text-amber-500 mt-1">ぶじ おかげまいり できました</p>
                </div>
            </div>

            {{-- シェアボタン --}}
            <div class="space-y-3">
                @php
                    $shareText = "{$dog->name}が{$journey->departure_name}から伊勢神宮まで{$journey->estimated_days}日かけてお参りしてきました！ #おかげ犬";
                @endphp

                <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}"
                    target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-slate-800 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    Xでシェア
                </a>

                <a href="https://social-plugins.line.me/lineit/share?url={{ urlencode(route('journey.share', $journey)) }}&text={{ urlencode($shareText) }}"
                    target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-green-500 text-white font-semibold text-sm hover:bg-green-600 transition">
                    LINEでシェア
                </a>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('journey.story', $journey) }}" class="btn-secondary flex-1 justify-center">珍道中を読む</a>
                <a href="{{ route('journey.departure') }}" class="btn-primary flex-1 justify-center">もう一度出発</a>
            </div>
        </div>
    </div>
</x-app-layout>
