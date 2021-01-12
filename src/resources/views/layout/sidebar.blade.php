<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand">
            Face<span>AI</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item nav-category">Main</li>

            <li class="nav-item {{ active_class(['/']) }}">
                <a href="{{ url('/') }}" class="nav-link">
                    <i class="link-icon" data-feather="box"></i>
                    <span class="link-title">Trang chủ</span>
                </a>
            </li>
            {{--<li class="nav-item nav-category">Application</li>--}}

            <li class="nav-item {{ active_class(['users', 'users/*']) }}">
                <a class="nav-link" href="{{ route('users') }}">
                    <i class="link-icon" data-feather="users"></i>
                    <span class="link-title">Quản lý người dùng</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['identities', 'identities/*']) }}">
                <a class="nav-link" href="{{ route('identities') }}">
                    <i class="link-icon" data-feather="user"></i>
                    <span class="link-title">Quản lý định danh</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['cameras', 'cameras/*']) }}">
                <a class="nav-link" href="{{ route('cameras') }}">
                    <i class="mdi mdi-cctv link-icon"></i>
                    <span class="link-title">Quản lý camera</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['monitors', 'monitors/*']) }}">
                <a class="nav-link" href="{{ route('monitors') }}">
                    <i class="mdi mdi-monitor-eye link-icon"></i>
                    <span class="link-title">Màn hình giám sát</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['processes', 'processes/*']) }}">
                <a class="nav-link" href="{{ route('processes') }}">
                    <i class="link-icon" data-feather="activity"></i>
                    <span class="link-title">Thực thi nhận diện</span>
                </a>
            </li>

            <li class="nav-item {{ active_class(['settings', 'settings/*']) }}">
                <a class="nav-link" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="link-icon" data-feather="log-out"></i>
                    <span class="link-title">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
