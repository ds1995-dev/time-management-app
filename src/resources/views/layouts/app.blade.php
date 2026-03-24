<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">

    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__inner-logo" href="/attendance">
                <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}">
            </a>
            @auth
            @if (session('ui_role') === 'admin')
            <!-- 管理者ヘッダー -->
            <div class="header__inner-href">
                <a class="header__inner-href__mypage" href="/admin/attendance/list">勤怠一覧</a>
                <a class="header__inner-href__mypage" href="/admin/staff/list">スタッフ一覧</a>
                <a class="header__inner-href__mypage" href="{{ route('change.request.list') }}">申請一覧</a>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="header__inner-href__logout" type="submit">ログアウト</button>
                </form>
            </div>
            @else
            <!-- 一般ユーザーヘッダー -->
            <div class="header__inner-href">
                <a class="header__inner-href__mypage" href="/">勤怠</a>
                <a class="header__inner-href__mypage" href="/attendance/list">勤怠一覧</a>
                <a class="header__inner-href__mypage" href="{{ route('change.request.list') }}">申請</a>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="header__inner-href__logout" type="submit">ログアウト</button>
                </form>
            </div>
            @endif
            @endauth
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>