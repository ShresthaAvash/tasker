<li class="nav-item dropdown user-menu">
    {{-- This is the part you click on, which shows the user's name --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
    </a>

    {{-- --- THIS IS THE FIX --- --}}
    {{-- This new structure creates a simple list instead of a blue header --}}
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