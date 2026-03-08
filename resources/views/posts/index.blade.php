<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-900">珍道中日記</h2>
            <a href="{{ route('posts.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                新規投稿
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @forelse($posts as $post)
                <a href="{{ route('posts.show', $post) }}"
                    class="card mb-3 flex items-center justify-between p-5 hover:shadow-md hover:border-indigo-100 transition-all duration-200 group block">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            @if($post->category === 'tech')
                                <span class="badge-tech">Tech</span>
                            @elseif($post->category === 'life')
                                <span class="badge-life">Life</span>
                            @elseif($post->category === 'idea')
                                <span class="badge-idea">Idea</span>
                            @elseif($post->category === 'journey')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">🐕 珍道中</span>
                            @endif
                            <span class="text-xs text-slate-400">{{ $post->created_at->format('Y/m/d') }}</span>
                        </div>
                        <h2 class="text-base font-semibold text-slate-900 group-hover:text-indigo-600 transition truncate">
                            {{ $post->title }}
                        </h2>
                    </div>
                    <svg class="h-5 w-5 text-slate-300 group-hover:text-indigo-400 transition ml-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @empty
                <div class="card p-16 text-center">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-slate-400 text-sm mb-5">まだ投稿がありません</p>
                    <a href="{{ route('posts.create') }}" class="btn-primary">最初の投稿を作成する</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
