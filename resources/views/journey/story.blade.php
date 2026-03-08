<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('journey.show', $journey) }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="font-bold text-xl text-slate-900">{{ $dog->name }}の珍道中</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4">
            {{-- 地図 --}}
            @if($journey->route_polyline)
            <div class="card mb-4 overflow-hidden">
                <div id="route-map" class="w-full h-48"></div>
            </div>
            @endif

            {{-- 旅の進行バー --}}
            <div class="mb-6">
                <div class="flex justify-between text-xs text-slate-400 mb-1">
                    <span>{{ $journey->departure_name }}</span>
                    <span>伊勢神宮</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-gradient-to-r from-indigo-500 to-amber-500 h-2 rounded-full transition-all duration-700" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-xs text-slate-400 mt-1 text-center"></p>
            </div>
            {{-- 物語コンテナ --}}
            <div id="story-container"></div>

            {{-- ローディング --}}
            <div id="loading" class="card p-8 text-center">
                <div class="animate-bounce text-4xl mb-3">🐕</div>
                <p class="text-sm text-slate-500" id="loading-text">{{ $dog->name }}が旅支度をしています...</p>
            </div>

            {{-- 到着演出（非表示） --}}
            <div id="arrival" class="hidden">
                <div class="card p-8 text-center mb-6 bg-gradient-to-b from-amber-50 to-white">
                    <div class="text-5xl mb-4">⛩️</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">伊勢神宮に到着！</h3>
                    <p class="text-sm text-slate-600 mb-4">{{ $dog->name }}は無事にお参りを果たしました</p>

                    {{-- 御札風 --}}
                    <div id="ofuda" class="mx-auto w-64 bg-amber-50 border-2 border-amber-300 rounded-lg p-6 mb-6"
                        style="background-image: linear-gradient(transparent 95%, rgba(139,69,19,0.1) 100%);">
                        <p class="text-xs text-amber-600 mb-2">御札</p>
                        <img src="{{ asset('storage/' . $dog->photo_path) }}" alt="{{ $dog->name }}"
                            class="w-16 h-16 rounded-full object-cover mx-auto mb-3 border-2 border-amber-200">
                        <p class="text-lg font-bold text-amber-900 mb-3" style="font-family: serif;">{{ $journey->wish }}</p>
                        <div class="w-px h-6 bg-amber-300 mx-auto mb-3"></div>
                        <p class="text-sm text-amber-700">{{ $dog->name }}</p>
                        <p class="text-xs text-amber-500 mt-1">{{ $journey->departed_at->format('Y年m月d日') }} 出立</p>
                    </div>

                    <p id="save-status" class="text-xs text-slate-400 mb-4"></p>
                    <div class="flex gap-3 justify-center flex-wrap">
                        <a href="{{ route('journey.departure') }}" class="btn-primary">もう一度出発させる</a>
                        <a id="diary-link" href="{{ route('posts.index') }}" class="btn-secondary">珍道中日記を見る</a>
                        <a href="{{ route('journey.share', $journey) }}" class="btn-secondary">シェアする</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const journeyId = {{ $journey->id }};
        const totalDays = {{ $journey->estimated_days }};
        const chaptersUrl = '{{ route("journey.generate-story", $journey) }}';
        const savePostUrl = '{{ route("journey.save-post", $journey) }}';
        const csrfToken = '{{ csrf_token() }}';
        const departureLat = {{ $journey->departure_lat }};
        const departureLng = {{ $journey->departure_lng }};
        const iseLat = 34.4551;
        const iseLng = 136.7256;
        const routePolyline = @json($journey->route_polyline);
        let currentDay = 1;
        let isGenerating = false;

        // 地図関連
        let routeMap, dogMarker, walkedPath, fullPath, routePoints;

        function initRouteMap() {
            if (!routePolyline || !document.getElementById('route-map')) return;

            routePoints = google.maps.geometry.encoding.decodePath(routePolyline);

            routeMap = new google.maps.Map(document.getElementById('route-map'), {
                disableDefaultUI: true,
                zoomControl: false,
                mapTypeControl: false,
                streetViewControl: false,
                gestureHandling: 'none',
            });

            // ルート全体（薄い線）
            fullPath = new google.maps.Polyline({
                path: routePoints,
                geodesic: true,
                strokeColor: '#cbd5e1',
                strokeOpacity: 0.8,
                strokeWeight: 3,
                map: routeMap,
            });

            // 歩いた部分（色付き線）
            walkedPath = new google.maps.Polyline({
                path: [routePoints[0]],
                geodesic: true,
                strokeColor: '#6366f1',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                map: routeMap,
            });

            // 犬マーカー
            dogMarker = new google.maps.Marker({
                position: routePoints[0],
                map: routeMap,
                label: { text: '🐕', fontSize: '20px' },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 0,
                },
            });

            // 出発地マーカー
            new google.maps.Marker({
                position: routePoints[0],
                map: routeMap,
                label: { text: '📍', fontSize: '16px' },
                icon: { path: google.maps.SymbolPath.CIRCLE, scale: 0 },
            });

            // 伊勢神宮マーカー
            new google.maps.Marker({
                position: routePoints[routePoints.length - 1],
                map: routeMap,
                label: { text: '⛩️', fontSize: '16px' },
                icon: { path: google.maps.SymbolPath.CIRCLE, scale: 0 },
            });

            // ルート全体が見えるようにフィット
            const bounds = new google.maps.LatLngBounds();
            routePoints.forEach(p => bounds.extend(p));
            routeMap.fitBounds(bounds, { top: 20, bottom: 20, left: 20, right: 20 });
        }

        function updateMapProgress(day) {
            if (!routePoints || !dogMarker) return;
            const pct = Math.min(1, day / totalDays);
            const idx = Math.min(Math.floor(pct * (routePoints.length - 1)), routePoints.length - 1);
            const walkedPts = routePoints.slice(0, idx + 1);

            walkedPath.setPath(walkedPts);
            dogMarker.setPosition(routePoints[idx]);
        }

        const eventIcons = {
            'normal': '🐾',
            'hardship': '😰',
            'festival': '🎉',
            'companion_join': '🤝',
            'companion_leave': '👋',
            'rest': '😴',
            'arrival': '⛩️',
        };

        const weatherIcons = {
            '晴れ': '☀️',
            '曇り': '☁️',
            '雨': '🌧️',
            '雪': '❄️',
            '風': '💨',
        };

        async function loadStoryChunk(startDay) {
            if (isGenerating) return;
            isGenerating = true;

            try {
                const res = await fetch(`${chaptersUrl}?start_day=${startDay}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) throw new Error('生成に失敗しました');

                const data = await res.json();
                renderDays(data.days);

                if (data.is_last) {
                    showArrival();
                } else {
                    currentDay = startDay + 5;
                    prefetchNext();
                }
            } catch (e) {
                document.getElementById('loading-text').textContent = 'エラーが発生しました。ページを再読み込みしてください。';
            } finally {
                isGenerating = false;
            }
        }

        let prefetchedData = null;

        async function prefetchNext() {
            if (currentDay > totalDays) return;

            try {
                const res = await fetch(`${chaptersUrl}?start_day=${currentDay}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    prefetchedData = await res.json();
                }
            } catch (e) {
                // プリフェッチ失敗は無視、スクロール時に再試行
            }
        }

        function renderDays(days) {
            const container = document.getElementById('story-container');
            document.getElementById('loading').classList.add('hidden');

            days.forEach((day, i) => {
                setTimeout(() => {
                    const el = document.createElement('div');
                    el.className = 'card mb-4 opacity-0 translate-y-4 transition-all duration-500';

                    const icon = eventIcons[day.event_type] || '🐾';
                    const wIcon = weatherIcons[day.weather] || '';
                    const companionBadge = day.companion
                        ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 ml-2">🤝 ${day.companion}</span>`
                        : '';

                    el.innerHTML = `
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">${icon}</span>
                                <span class="font-bold text-slate-900">【${day.day}日目】${day.location}</span>
                                <span class="text-sm ml-auto">${wIcon}</span>
                            </div>
                            <div class="flex items-center gap-1 mb-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">${day.weather}</span>
                                ${companionBadge}
                            </div>
                            <p class="text-sm text-slate-700 leading-relaxed">${day.text}</p>
                        </div>
                    `;

                    container.appendChild(el);

                    requestAnimationFrame(() => {
                        el.classList.remove('opacity-0', 'translate-y-4');
                    });

                    updateProgress(day.day);
                    updateMapProgress(day.day);
                }, i * 600);
            });
        }

        function updateProgress(day) {
            const pct = Math.min(100, Math.round((day / totalDays) * 100));
            document.getElementById('progress-bar').style.width = pct + '%';
            document.getElementById('progress-text').textContent = `${day}日目 / ${totalDays}日間`;
        }

        function showArrival() {
            setTimeout(() => {
                updateMapProgress(totalDays);
                document.getElementById('arrival').classList.remove('hidden');
                document.getElementById('arrival').scrollIntoView({ behavior: 'smooth' });
                updateProgress(totalDays);
                saveAsPost();
            }, 1000);
        }

        async function saveAsPost() {
            const statusEl = document.getElementById('save-status');
            try {
                statusEl.textContent = '珍道中日記に保存中...';
                const res = await fetch(savePostUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) throw new Error('保存に失敗しました');
                const data = await res.json();
                const postUrl = '{{ url("posts") }}/' + data.post_id;
                document.getElementById('diary-link').href = postUrl;
                statusEl.textContent = data.already_saved ? '✓ 珍道中日記に保存済み' : '✓ 珍道中日記に保存しました！';
            } catch (e) {
                statusEl.textContent = '日記の保存に失敗しました';
            }
        }

        // スクロール監視でプリフェッチデータを表示
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && prefetchedData) {
                    const data = prefetchedData;
                    prefetchedData = null;
                    renderDays(data.days);

                    if (data.is_last) {
                        showArrival();
                    } else {
                        currentDay += 5;
                        prefetchNext();
                    }
                }
            });
        }, { rootMargin: '200px' });

        // 最後のカードを監視するためのセンチネル
        const sentinel = document.createElement('div');
        sentinel.id = 'sentinel';
        document.getElementById('story-container').after(sentinel);
        observer.observe(sentinel);

        // 初期ロード
        loadStoryChunk(1);
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=geometry&callback=initRouteMap">
    </script>
</x-app-layout>
