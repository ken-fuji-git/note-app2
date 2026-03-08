<?php

namespace App\Http\Controllers;

use App\Models\Dog;
use App\Models\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JourneyController extends Controller
{
    private const ISE_LAT = 34.4551;
    private const ISE_LNG = 136.7256;

    public function departure()
    {
        $dog = auth()->user()->dogs()->first();

        if (!$dog) {
            return redirect()->route('dogs.create');
        }

        return view('journey.departure', [
            'dog' => $dog,
            'googleMapsKey' => config('services.google_maps.key'),
        ]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'wish' => 'required|string|max:200',
        ]);

        $dog = auth()->user()->dogs()->firstOrFail();

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $directions = $this->getDirections($lat, $lng);

        if (!$directions) {
            return back()->withErrors(['departure' => '指定した場所から伊勢神宮への道路ルートが見つかりませんでした。本州内の場所を指定してください。']);
        }

        $distanceKm = $directions['distance_km'];
        $estimatedDays = $dog->estimateDays($distanceKm);
        $places = $directions['places'];

        $journey = Journey::create([
            'dog_id' => $dog->id,
            'wish' => $request->input('wish'),
            'departure_lat' => $lat,
            'departure_lng' => $lng,
            'departure_name' => $directions['start_address'],
            'distance_km' => $distanceKm,
            'estimated_days' => $estimatedDays,
            'departed_at' => now()->toDateString(),
            'route_places' => $places,
            'route_polyline' => $directions['polyline'],
            'story' => ['days' => []],
        ]);

        return redirect()->route('journey.show', $journey);
    }

    public function show(Journey $journey)
    {
        abort_if($journey->dog->user_id !== auth()->id(), 403);

        return view('journey.show', [
            'journey' => $journey,
            'dog' => $journey->dog,
        ]);
    }

    public function story(Journey $journey)
    {
        abort_if($journey->dog->user_id !== auth()->id(), 403);

        return view('journey.story', [
            'journey' => $journey,
            'dog' => $journey->dog,
            'googleMapsKey' => config('services.google_maps.key'),
        ]);
    }

    public function generateStory(Request $request, Journey $journey)
    {
        abort_if($journey->dog->user_id !== auth()->id(), 403);

        $startDay = (int) $request->query('start_day', 1);
        if ($startDay < 1) $startDay = 1;
        $endDay = min($startDay + 4, $journey->estimated_days);
        $dog = $journey->dog;

        $existingStory = $journey->story ?? ['days' => []];
        $previousDays = array_filter($existingStory['days'], fn($d) => $d['day'] < $startDay);

        $previousSummary = '';
        if (!empty($previousDays)) {
            $previousSummary = "これまでの旅の要約:\n";
            foreach ($previousDays as $d) {
                $previousSummary .= "【{$d['day']}日目】{$d['location']} - {$d['event_type']}: {$d['summary']}\n";
                if (!empty($d['companion'])) {
                    $previousSummary .= "  道連れ: {$d['companion']}\n";
                }
            }
        }

        $routePlaces = $journey->route_places ?? [];
        $totalPlaces = count($routePlaces);
        $placesForChunk = [];
        if ($totalPlaces > 0 && $journey->estimated_days > 0) {
            $startIdx = (int) floor(($startDay - 1) / $journey->estimated_days * $totalPlaces);
            $endIdx = (int) floor($endDay / $journey->estimated_days * $totalPlaces);
            $placesForChunk = array_slice($routePlaces, $startIdx, max(1, $endIdx - $startIdx));
        }

        $season = $this->getSeason($journey->departed_at);
        $isLastChunk = $endDay >= $journey->estimated_days;

        $prompt = $this->buildStoryPrompt($dog, $journey, $startDay, $endDay, $season, $placesForChunk, $previousSummary, $isLastChunk);

        $geminiKey = config('services.gemini.key');
        \Log::info('Gemini request start', ['key_set' => !empty($geminiKey), 'start_day' => $startDay]);

        $response = null;
        $maxRetries = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(60)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $geminiKey,
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                        ],
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Gemini HTTP exception', ['message' => $e->getMessage(), 'attempt' => $attempt]);
                if ($attempt === $maxRetries) {
                    return response()->json(['error' => '通信エラー'], 500);
                }
                sleep(10);
                continue;
            }

            if ($response->status() === 429 && $attempt < $maxRetries) {
                \Log::info('Gemini rate limited, retrying', ['attempt' => $attempt]);
                sleep(15);
                continue;
            }

            break;
        }

        if (!$response->ok()) {
            \Log::error('Gemini API error', ['status' => $response->status()]);
            return response()->json(['error' => '物語の生成に失敗しました'], 500);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        $generated = json_decode($text, true);

        \Log::info('Gemini parsed', ['has_days' => isset($generated['days'])]);

        if (!$generated || !isset($generated['days'])) {
            \Log::error('Gemini bad response', ['text' => $text]);
            return response()->json(['error' => '物語の生成に失敗しました'], 500);
        }

        $existingStory['days'] = array_merge($existingStory['days'], $generated['days']);
        $journey->update(['story' => $existingStory]);

        return response()->json([
            'days' => $generated['days'],
            'is_last' => $isLastChunk,
        ]);
    }

    public function share(Journey $journey)
    {
        return view('journey.share', [
            'journey' => $journey,
            'dog' => $journey->dog,
        ]);
    }

    private function getDirections(float $lat, float $lng): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => "{$lat},{$lng}",
            'destination' => self::ISE_LAT . ',' . self::ISE_LNG,
            'language' => 'ja',
            'key' => config('services.google_maps.directions_key'),
        ]);

        $data = $response->json();

        if ($data['status'] !== 'OK') {
            \Log::error('Directions API error', [
                'status' => $data['status'] ?? 'unknown',
                'error_message' => $data['error_message'] ?? 'none',
            ]);
            return null;
        }

        $route = $data['routes'][0];
        $leg = $route['legs'][0];

        $distanceMeters = $leg['distance']['value'];
        $distanceKm = round($distanceMeters / 1000, 1);

        $places = [];
        foreach ($leg['steps'] as $step) {
            $instruction = strip_tags($step['html_instructions'] ?? '');
            if (preg_match('/([\p{Han}\p{Hiragana}\p{Katakana}]+(?:市|町|村|区|宿|峠|橋|川|山|島|港|駅|IC))/u', $instruction, $m)) {
                $place = $m[1];
                if (!in_array($place, $places)) {
                    $places[] = $place;
                }
            }
        }

        if (empty($places)) {
            $places[] = $leg['start_address'];
        }

        return [
            'distance_km' => $distanceKm,
            'start_address' => $leg['start_address'],
            'places' => $places,
            'polyline' => $route['overview_polyline']['points'] ?? null,
        ];
    }

    private function getSeason(\Carbon\Carbon $date): string
    {
        $month = $date->month;
        if (in_array($month, [3, 4, 5])) return '春';
        if (in_array($month, [6, 7, 8])) return '夏';
        if (in_array($month, [9, 10, 11])) return '秋';
        return '冬';
    }

    private function buildStoryPrompt(Dog $dog, Journey $journey, int $startDay, int $endDay, string $season, array $places, string $previousSummary, bool $isLastChunk): string
    {
        $placesStr = !empty($places) ? implode('、', $places) : '道中の宿場町';
        $lastDayNote = $isLastChunk ? "\n最終日({$endDay}日目)は伊勢神宮に到着する感動的なシーンで締めくくってください。お願い事「{$journey->wish}」にも触れてください。" : '';

        return <<<PROMPT
あなたは江戸時代の「おかげ犬」の珍道中を語る、ユーモラスな語り部です。
以下の犬が飼い主の代わりに伊勢神宮へお参りに向かう旅の物語を書いてください。

【犬の情報】
名前: {$dog->name}
犬種: {$dog->breed}
体高: {$dog->height}cm
年齢: {$dog->age}歳
性格: {$dog->personality}
性別: {$dog->gender}

【旅の情報】
出発地: {$journey->departure_name}
伊勢神宮までの距離: {$journey->distance_km}km
全行程: {$journey->estimated_days}日間
お願い事: {$journey->wish}
季節: {$season}
今回書く日: {$startDay}日目〜{$endDay}日目
通過する場所: {$placesStr}

{$previousSummary}

【ルール】
- {$startDay}日目から{$endDay}日目までの物語を書いてください
- 各日は実在の地名を使い、その土地の名物や名所を絡めてください
- 犬種特有の面白い行動を入れてください
- 季節（{$season}）の天気や風物詩を反映してください
- たまに以下のイベントを混ぜてください: 体調不良で休養、祭りに参加、道連れ（他の動物）との出会い、天候不良による足止め
- 1日あたり3〜4文の短い物語にしてください
- ユーモラスで温かい文体で書いてください
{$lastDayNote}

【出力形式】以下のJSON形式で出力してください:
{
  "days": [
    {
      "day": 日数(整数),
      "location": "地名",
      "weather": "天気",
      "event_type": "normal|hardship|festival|companion_join|companion_leave|rest|arrival",
      "companion": "道連れの名前（いない場合はnull）",
      "summary": "10文字以内の要約",
      "text": "その日の物語本文"
    }
  ]
}
PROMPT;
    }
}
