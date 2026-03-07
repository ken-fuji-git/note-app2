<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900">新規投稿</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
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

                    <form id="post-form" action="{{ route('posts.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="form-label">タイトル</label>
                                <input type="text" name="title" value="{{ old('title') }}"
                                    class="form-input" placeholder="記事タイトルを入力">
                            </div>

                            <div>
                                <label class="form-label">カテゴリ</label>
                                <select name="category" class="form-input">
                                    <option value="">選択してください</option>
                                    <option value="tech" {{ old('category') == 'tech' ? 'selected' : '' }}>Tech</option>
                                    <option value="life" {{ old('category') == 'life' ? 'selected' : '' }}>Life</option>
                                    <option value="idea" {{ old('category') == 'idea' ? 'selected' : '' }}>Idea</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">本文</label>
                                <div id="editor" class="rounded-xl overflow-hidden border border-slate-200" style="height: 320px;"></div>
                                <input type="hidden" name="body" id="body">
                            </div>

                            <div>
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="is_published" value="1"
                                        {{ old('is_published') ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700">公開する</span>
                                </label>
                            </div>

                            <div class="flex gap-3 pt-2 border-t border-slate-100">
                                <button type="submit" class="btn-primary">投稿する</button>
                                <a href="{{ route('posts.index') }}" class="btn-secondary">キャンセル</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        const quill = new Quill('#editor', { theme: 'snow' });

        document.getElementById('post-form').addEventListener('submit', function() {
            document.getElementById('body').value = quill.root.innerHTML;
        });
    </script>
</x-app-layout>
