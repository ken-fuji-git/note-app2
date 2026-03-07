<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ $post->title }}</title>
</head>
<body>
    <a href="{{ route('posts.index') }}">← 一覧に戻る</a>
    <h1>{{ $post->title }}</h1>
    <span>{{ $post->category }}</span>
    <span>{{ $post->created_at->format('Y/m/d') }}</span>
    <span>投稿者：{{ $post->user->name }}</span>
    <div>{!! $post->body !!}</div>

    @auth
        @if($post->user_id === auth()->id())
            <a href="{{ route('posts.edit', $post) }}">編集</a>
            <form action="{{ route('posts.destroy', $post) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit">削除</button>
            </form>
        @endif
    @endauth
</body>
</html>