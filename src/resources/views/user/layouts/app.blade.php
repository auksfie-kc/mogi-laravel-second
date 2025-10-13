<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @stack('styles')

    <title>Coachtech
    </title>
</head>

<body class="{{ (auth()->check() && auth()->user()->hasVerifiedEmail()) ? 'bg-beige' : 'bg-white' }}">
    <header class="header">
        <div class="header-inner">

            <div class="logo-area">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="logo">
            </div>

            <nav class="nav-menu">
                <ul>
                    @if (Auth::check()&& Auth::user()->hasVerifiedEmail())
                    <li>
                        <form action="{{ route('attendance.list')}}" method="get">
                            @csrf
                            <button class="header-nav__button">勤怠一覧</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('attendance.index')}}" method="get">
                            @csrf
                            <button class="header-nav__button">勤怠</button>
                        </form>
                    </li>

                    <li>
                        <form action="{{ route('requests.index')}}" method="get">
                            @csrf
                            <button class="header-nav__button">申請</button>
                        </form>
                    </li>

                    <li>
                        <form action="/logout" method="post">
                            @csrf
                            <button class="header-nav__button">ログアウト</button>
                        </form>
                    </li>

                    @endif
                </ul>
            </nav>
        </div>

    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>