<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - CoachTech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="header">
        <div class="header__logo">
            <img class="header__logo-image" src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="CoachTech">
        </div>
        <nav class="header__nav">
            @section('nav')
            <a class="header__nav-link" href="/attendance">勤怠</a>
            <a class="header__nav-link" href="/attendance/list">勤怠一覧</a>
            <a class="header__nav-link" href="/stamp_correction_request/list">申請</a>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="header__nav-link header__nav-button">ログアウト</button>
            </form>
            @show
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
