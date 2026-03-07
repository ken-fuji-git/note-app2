<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900">旅の概要</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4">
            {{-- 犬カード --}}
            <div class="card mb-6">
                <div class="p-5 text-center">
                    <img src="{{ asset('storage/' . $dog->photo_path) }}" alt="{{ $dog->name }}"
                        class="w-24 h-24 rounded-full object-cover mx-auto mb-3 border-4 border-amber-100">
                    <h3 class="font-bold text-lg text-slate-900">{{ $dog->name }}</h3>
                    <p class="text-sm text-slate-400">{{ $dog->breed }} / {{ $dog->age }}歳 / {{ $dog->gender }}</p>
                </div>
            </div>

            {{-- 旅の情報 --}}
            <div class="card mb-6">
                <div class="p-5 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">出発地</span>
                        <span class="text-sm font-medium text-slate-900">{{ $journey->departure_name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">目的地</span>
                        <span class="text-sm font-medium text-slate-900">伊勢神宮</span>
                    </div>
                    <div class="w-full h-px bg-slate-100"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">道のり</span>
                        <span class="text-lg font-bold text-indigo-600">{{ number_format($journey->distance_km, 1) }} km</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">推定日数</span>
                        <span class="text-lg font-bold text-indigo-600">{{ $journey->estimated_days }} 日間</span>
                    </div>
                    <div class="w-full h-px bg-slate-100"></div>
                    <div>
                        <span class="text-sm text-slate-500">お願い事</span>
                        <p class="text-sm font-medium text-slate-900 mt-1">「{{ $journey->wish }}」</p>
                    </div>
                </div>
            </div>

            {{-- 珍道中を見るボタン --}}
            <a href="{{ route('journey.story', $journey) }}"
                class="btn-primary w-full justify-center py-3 text-base">
                珍道中を見る
            </a>
        </div>
    </div>
</x-app-layout>
