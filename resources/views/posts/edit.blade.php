<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>記事編集</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .editor-layout {
            display: flex;
            gap: 24px;
        }
        .editor-area {
            flex: 1;
        }
        .ai-area {
            width: 320px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
        }
        #ai-response {
            min-height: 120px;
            background: #f9f9f9;
            border-radius: 4px;
            padding: 12px;
            margin-top: 8px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>記事編集</h1>

    @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <div class="editor-layout">

        {{-- 左：記事編集エリア --}}
        <div class="editor-area">
            <form action="{{ route('posts.update', $post) }}" method="POST">
                @csrf
                @method('PUT')

                <div>
                    <label>タイトル</label>
                    <input type="text" name="title" value="{{ old('title', $post->title) }}">
                </div>

                <div>
                    <label>カテゴリ</label>
                    <select name="category">
                        <option value="">選択してください</option>
                        <option value="tech" {{ old('category', $post->category) == 'tech' ? 'selected' : '' }}>Tech</option>
                        <option value="life" {{ old('category', $post->category) == 'life' ? 'selected' : '' }}>Life</option>
                        <option value="idea" {{ old('category', $post->category) == 'idea' ? 'selected' : '' }}>Idea</option>
                    </select>
                </div>

                <div>
                    <label>
                        <input type="checkbox" name="is_published" value="1"
                            {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                        公開する
                    </label>
                </div>

                <div>
                    <label>本文</label>
                    <div id="editor" style="height: 300px;"></div>
                    <input type="hidden" name="body" id="body">
                </div>

                <button type="submit">更新する</button>
            </form>
        </div>

        {{-- 右：AI相談エリア --}}
        <div class="ai-area">
            <h3>AI相談</h3>
            <p>記事の内容についてAIに相談できます。</p>

            <textarea id="ai-input" rows="4" style="width: 100%;"
                placeholder="例：この記事のタイトルを3つ提案してください"></textarea>

            <button onclick="askAI()" style="margin-top: 8px;">AIに聞く</button>

            <div id="ai-response"></div>
        </div>

    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        // Quill初期化
        const quill = new Quill('#editor', { theme: 'snow' });
        quill.root.innerHTML = `{!! $post->body !!}`;

        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('body').value = quill.root.innerHTML;
        });

        // AI相談機能
        async function askAI() {
            const message = document.getElementById('ai-input').value;
            if (!message) return;

            document.getElementById('ai-response').textContent = '考え中...';

            const res = await fetch('/ai/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message })
            });

            const data = await res.json();
            typeWriter(data.reply);
        }

        function typeWriter(text) {
            const el = document.getElementById('ai-response');
            el.textContent = '';
            let i = 0;
            const timer = setInterval(() => {
                el.textContent += text[i];
                i++;
                if (i >= text.length) clearInterval(timer);
            }, 30);
        }
    </script>
</body>
</html>