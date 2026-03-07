<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900">記事編集</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-6 items-start">

                {{-- 左：記事編集エリア --}}
                <div class="flex-1 min-w-0">
                    <div class="card">
                        <div class="p-8">
                            @if($errors->any())
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                                    <ul class="space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li class="text-sm text-red-600">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form id="post-form" action="{{ route('posts.update', $post) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="space-y-6">
                                    <div>
                                        <label class="form-label">タイトル</label>
                                        <input type="text" name="title"
                                            value="{{ old('title', $post->title) }}"
                                            class="form-input" placeholder="記事タイトルを入力">
                                    </div>

                                    <div>
                                        <label class="form-label">カテゴリ</label>
                                        <select name="category" class="form-input">
                                            <option value="">選択してください</option>
                                            <option value="tech" {{ old('category', $post->category) == 'tech' ? 'selected' : '' }}>Tech</option>
                                            <option value="life" {{ old('category', $post->category) == 'life' ? 'selected' : '' }}>Life</option>
                                            <option value="idea" {{ old('category', $post->category) == 'idea' ? 'selected' : '' }}>Idea</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="is_published" value="1"
                                                {{ old('is_published', $post->is_published) ? 'checked' : '' }}
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm font-medium text-slate-700">公開する</span>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="form-label">本文</label>
                                        <div id="editor" class="rounded-xl overflow-hidden border border-slate-200" style="height: 320px;"></div>
                                        <input type="hidden" name="body" id="body">
                                    </div>

                                    <div class="flex gap-3 pt-2 border-t border-slate-100">
                                        <button type="submit" class="btn-primary">更新する</button>
                                        <a href="{{ route('posts.show', $post) }}" class="btn-secondary">キャンセル</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 右：AI相談エリア --}}
                <div class="w-80 shrink-0">
                    <div class="card p-6 sticky top-6">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h3 class="font-semibold text-slate-900">AI相談</h3>
                        </div>
                        <p class="text-sm text-slate-500 mb-4">記事の内容についてAIに相談できます。</p>

                        <textarea id="ai-input" rows="4"
                            class="form-input resize-none mb-3 text-sm"
                            placeholder="例：この記事のタイトルを3つ提案してください"></textarea>

                        <button onclick="askAI()" class="btn-primary w-full justify-center">
                            AIに聞く
                        </button>

                        <div id="ai-response"
                            class="mt-4 p-4 bg-slate-50 rounded-xl text-sm text-slate-700 min-h-[80px] whitespace-pre-wrap leading-relaxed"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        const quill = new Quill('#editor', { theme: 'snow' });
        quill.root.innerHTML = `{!! $post->body !!}`;

        document.getElementById('post-form').addEventListener('submit', function() {
            document.getElementById('body').value = quill.root.innerHTML;
        });

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
</x-app-layout>
