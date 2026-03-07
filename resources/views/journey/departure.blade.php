<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-bold text-xl text-slate-900">出発画面</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto px-4">
            {{-- 犬の情報カード --}}
            <div class="card mb-4">
                <div class="p-4 flex items-center gap-4">
                    <img src="{{ asset('storage/' . $dog->photo_path) }}" alt="{{ $dog->name }}"
                        class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100">
                    <div>
                        <p class="font-bold text-slate-900">{{ $dog->name }}</p>
                        <p class="text-xs text-slate-400">{{ $dog->breed }} / {{ $dog->height }}cm / {{ $dog->personality }}</p>
                    </div>
                    <a href="{{ route('dogs.create') }}" class="ml-auto text-xs text-indigo-500 hover:text-indigo-700">編集</a>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- お願い事 --}}
            <div class="card mb-4">
                <div class="p-5">
                    <label class="form-label">お願い事 <span class="text-red-500">*</span></label>
                    <input type="text" id="wish-input"
                        class="form-input" placeholder="例：家族の健康をお願いします"
                        maxlength="200">
                </div>
            </div>

            {{-- 地図 --}}
            <div class="card mb-4">
                <div class="p-5">
                    <label class="form-label mb-3">出発する場所を選んでね</label>
                    <div id="map" class="w-full h-64 rounded-xl overflow-hidden border border-slate-200"></div>
                    <p id="location-text" class="text-xs text-slate-400 mt-2">現在地を取得中...</p>
                </div>
            </div>

            {{-- 出発ボタン --}}
            <button id="start-btn" onclick="startJourney()" disabled
                class="btn-primary w-full justify-center py-3 text-base disabled:opacity-50 disabled:cursor-not-allowed">
                ここから出発
            </button>

            <form id="journey-form" action="{{ route('journey.start') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="lat" id="form-lat">
                <input type="hidden" name="lng" id="form-lng">
                <input type="hidden" name="wish" id="form-wish">
            </form>
        </div>
    </div>

    <script>
        let map, marker, selectedLat, selectedLng;

        function initMap() {
            const defaultPos = { lat: 35.6812, lng: 139.7671 };

            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultPos,
                zoom: 10,
                disableDefaultUI: true,
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
            });

            marker = new google.maps.Marker({
                position: defaultPos,
                map: map,
                draggable: true,
                title: '出発地点',
            });

            marker.addListener('dragend', function(e) {
                selectedLat = e.latLng.lat();
                selectedLng = e.latLng.lng();
                updateLocationText();
                enableStartButton();
            });

            map.addListener('click', function(e) {
                selectedLat = e.latLng.lat();
                selectedLng = e.latLng.lng();
                marker.setPosition(e.latLng);
                updateLocationText();
                enableStartButton();
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        selectedLat = pos.coords.latitude;
                        selectedLng = pos.coords.longitude;
                        const p = { lat: selectedLat, lng: selectedLng };
                        map.setCenter(p);
                        marker.setPosition(p);
                        updateLocationText();
                        enableStartButton();
                    },
                    function() {
                        document.getElementById('location-text').textContent =
                            'GPS取得できませんでした。地図をタップして出発地を選んでください。';
                        selectedLat = defaultPos.lat;
                        selectedLng = defaultPos.lng;
                        enableStartButton();
                    }
                );
            }
        }

        function updateLocationText() {
            document.getElementById('location-text').textContent =
                `出発地: ${selectedLat.toFixed(4)}, ${selectedLng.toFixed(4)}（ピンをドラッグで調整できます）`;
        }

        function enableStartButton() {
            document.getElementById('start-btn').disabled = false;
        }

        function startJourney() {
            const wish = document.getElementById('wish-input').value.trim();
            if (!wish) {
                alert('お願い事を入力してください');
                return;
            }

            document.getElementById('form-lat').value = selectedLat;
            document.getElementById('form-lng').value = selectedLng;
            document.getElementById('form-wish').value = wish;

            document.getElementById('start-btn').disabled = true;
            document.getElementById('start-btn').textContent = '旅支度中...';

            document.getElementById('journey-form').submit();
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initMap">
    </script>
</x-app-layout>
