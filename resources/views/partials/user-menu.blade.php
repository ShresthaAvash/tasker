<li class="nav-item dropdown user-menu">
    {{-- This is the menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        @if(Auth::user()->photo)
            {{-- If user has a photo, display it --}}
            <img src="{{ asset('storage/' . Auth::user()->photo) }}" class="user-image img-circle elevation-2" alt="{{ Auth::user()->name }}">
        @else
            {{-- Otherwise, display a generic user icon --}}
             <i class="fas fa-user-circle fa-lg"></i>
        @endif
    </a>

    {{-- This is the dropdown menu --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        {{-- Profile Link --}}
        <a href="{{ route('profile.edit') }}" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
        </a>
        <div class="dropdown-divider"></div>

        {{-- Activity Log Link --}}
        <a href="{{ route('profile.activity_log') }}" class="dropdown-item">
            <i class="fas fa-list mr-2"></i> Activity Log
        </a>
        <div class="dropdown-divider"></div>

        {{-- Logout Link --}}
        <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </ul>
</li>