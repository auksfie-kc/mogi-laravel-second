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


<body class="{{ (auth()->check()) ? 'bg-beige' : 'bg-white' }}">

        <header class="header">
            <div class="header-inner">

                <div class="header__nav">
                    <a>
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" height="35">
                    </a>

                </div>

                <div class="nav-menu">
                    <ul>
                        @if (Auth::guard('admin')->check())

                        <li>
                            <form action="{{ route('admin.top') }}" method="get">
                                @csrf
                                <button class="header-nav__button">勤怠一覧</button>
                            </form>
                        </li>

                        <li>
                            <form action="{{ route('admin.staff.list') }}" method="get">
                                @csrf
                                <button class="header-nav__button">スタッフ一覧</button>
                            </form>
                        </li>

                        <li>
                            <form action="{{ route('admin.request.index') }}" method="get">
                                @csrf
                                <button class="header-nav__button">申請一覧</button>
                            </form>
                        </li>

                        <li>
                            <form action="{{ route('admin.login.destroy') }}" method="post">
                                @method('DELETE')
                                @csrf
                                <button class="header-nav__button">ログアウト</button>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

        </header>
        <main>
            @yield('content')
        </main>
    </body>

</html>