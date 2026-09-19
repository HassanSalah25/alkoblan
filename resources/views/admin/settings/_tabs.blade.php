<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}" href="{{ route('admin.settings.general') }}">General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}" href="{{ route('admin.settings.seo') }}">SEO</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}" href="{{ route('admin.settings.social') }}">Social Media</a>
    </li>
</ul>
