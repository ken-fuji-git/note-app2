<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>記事一覧</title>
</head>
<body>
    <h1>記事一覧</h1>
    <a href="{{ route('posts.create') }}">新規投稿</a>

    @foreach($posts as $post)
        <div>
            <h2>
                <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
            </h2>
            <span>{{ $post->category }}</span>
            <span>{{ $post->created_at->format('Y/m/d') }}</span>
        </div>
    @endforeach
</body>
</html>