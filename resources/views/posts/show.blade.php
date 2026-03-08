<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('posts.index') }}" class="text-slate-400 hover:text-slate-600 transition text-sm font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                一覧
            </a>
            <span class="text-slate-200">/</span>
            <h2 class="font-bold text-xl text-slate-900 truncate">{{ $post->title }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-8">
                    <!-- Meta -->
                    <div class="flex items-center gap-3 mb-6">
                        @if($post->category === 'tech')
                            <span class="badge-tech">Tech</span>
                        @elseif($post->category === 'life')
                            <span class="badge-life">Life</span>
                        @elseif($post->category === 'idea')
                            <span class="badge-idea">Idea</span>
                        @elseif($post->category === 'journey')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">🐕 珍道中</span>
                        @endif
                        <span class="text-sm text-slate-400">{{ $post->created_at->format('Y年m月d日') }}</span>
                        <span class="text-slate-200">·</span>
                        <span class="text-sm text-slate-400">{{ $post->author_name ?? $post->user->name }}</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-slate-900 leading-snug mb-8">{{ $post->title }}</h1>

                    <!-- Body -->
                    <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                        {!! $post->body !!}
                    </div>

                    <!-- Actions -->
                    @auth
                        @if($post->user_id === auth()->id() && $post->category !== 'journey')
                            <div class="flex gap-3 mt-10 pt-6 border-t border-slate-100">
                                <a href="{{ route('posts.edit', $post) }}" class="btn-secondary">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    編集
                                </a>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger"
                                        onclick="return confirm('この記事を削除しますか？')">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        削除
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
