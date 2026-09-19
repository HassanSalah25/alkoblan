<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — AL-KOBLAN Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 268px;
            --ak-navy: #101a35;
            --ak-navy-dark: #0a1226;
            --ak-navy-light: #172347;
            --ak-accent: #2f7de1;
            --ak-accent-light: #eaf2fe;
            --ak-success: #12b76a;
            --ak-warning: #f79009;
            --ak-danger: #f04438;
            --ak-border: #e9ecf3;
            --ak-text-muted: #6b7385;
            --ak-radius: 14px;
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { background: #f6f7fb; min-height: 100vh; color: #1f2536; }

        /* ---------- Sidebar ---------- */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--ak-navy) 0%, var(--ak-navy-dark) 100%);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            overflow-y: auto;
            z-index: 1030;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
        }
        #sidebar::-webkit-scrollbar { width: 6px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 10px; }
        #sidebar .brand {
            color: #fff; font-weight: 800; font-size: 1.05rem; padding: 1.1rem 1.25rem;
            display: flex; align-items: center; gap: .6rem; border-bottom: 1px solid rgba(255,255,255,.08);
            letter-spacing: .01em;
        }
        #sidebar .brand .brand-logo {
            height: 34px; width: auto; max-width: 170px; object-fit: contain; flex-shrink: 0;
        }
        #sidebar .brand small { display: block; font-size: .68rem; font-weight: 500; color: var(--ak-text-muted); }
        #sidebar .nav-section-title {
            color: rgba(255,255,255,.35); text-transform: uppercase; font-size: .68rem; font-weight: 700;
            letter-spacing: .08em; padding: 1.1rem 1.25rem .4rem;
        }
        #sidebar .nav { padding: .5rem .75rem 1.5rem; }
        #sidebar .nav-link {
            color: rgba(255,255,255,.72); padding: .55rem .75rem; font-size: .875rem; font-weight: 500;
            border-radius: 9px; margin: .1rem 0; display: flex; align-items: center; gap: .65rem;
            transition: background .15s ease, color .15s ease;
        }
        #sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.07); }
        #sidebar .nav-link.active {
            color: #fff; background: linear-gradient(90deg, rgba(47,125,225,.85), rgba(47,125,225,.35));
            font-weight: 600; box-shadow: inset 0 0 0 1px rgba(255,255,255,.06);
        }
        #sidebar .nav-link i { width: 1.15rem; text-align: center; font-size: 1rem; opacity: .95; }

        .content-wrap { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }

        /* ---------- Topbar ---------- */
        #topbar {
            background: #fff; border-bottom: 1px solid var(--ak-border); padding: .75rem 1.5rem;
            position: sticky; top: 0; z-index: 1020;
        }
        #topbar h5 { font-weight: 700; }
        .avatar-circle {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--ak-accent), #6aa6f2); color: #fff;
            display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem;
            box-shadow: 0 2px 6px rgba(47,125,225,.3);
        }
        .topbar-icon-btn {
            width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            color: var(--ak-text-muted); background: #f6f7fb; border: 1px solid var(--ak-border); position: relative;
            transition: background .15s ease;
        }
        .topbar-icon-btn:hover { background: var(--ak-accent-light); color: var(--ak-accent); }
        .topbar-icon-btn .badge-dot {
            position: absolute; top: -2px; right: -2px; width: 9px; height: 9px; border-radius: 50%;
            background: var(--ak-danger); border: 2px solid #fff;
        }

        /* ---------- Content ---------- */
        .page-body { padding: 1.75rem; flex: 1; }
        .card { border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: 0 1px 3px rgba(16,24,40,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--ak-border); font-weight: 700; border-radius: var(--ak-radius) var(--ak-radius) 0 0 !important; }
        .table thead th {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--ak-text-muted);
            border-bottom-width: 1px; font-weight: 700; background: #fafbfd;
        }
        .table td, .table th { vertical-align: middle; }
        .btn-primary { background: var(--ak-accent); border-color: var(--ak-accent); }
        .btn-primary:hover { background: #2568c2; border-color: #2568c2; }
        .alert { border-radius: 12px; border: 1px solid transparent; }

        /* ---------- Filter toolbars (card-header search/filter forms) ---------- */
        .card-header {
            padding: 1rem 1.25rem; background: #fbfcfe;
        }
        .card-header form { margin: 0; }
        /* The `d-flex` filter-form pattern: Bootstrap's .form-control/.form-select
           default to width:100%, which becomes each item's flex-basis and forces
           every field onto its own line. Overriding to width:auto lets them sit
           inline like the `.row/.col-auto` pattern already does. */
        .card-header form.d-flex .form-control,
        .card-header form.d-flex .form-select {
            width: auto;
            flex: 0 0 auto;
        }
        .card-header form .form-control,
        .card-header form .form-select {
            border-radius: 8px; border-color: var(--ak-border); font-size: .84rem;
        }
        .card-header form input[type=text].form-control,
        .card-header form input[type=email].form-control,
        .card-header form input[type=search].form-control {
            min-width: 230px;
        }
        .card-header form select.form-select { min-width: 150px; }
        .card-header form .form-control:focus,
        .card-header form .form-select:focus {
            border-color: var(--ak-accent); box-shadow: 0 0 0 3px var(--ak-accent-light);
        }
        /* Nearly every filter bar's search box uses name="q" — give it a
           magnifier icon consistently without touching each view. */
        .card-header form input[name="q"] {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7385' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: 10px center; padding-right: 32px;
        }
        [dir="rtl"] .card-header form input[name="q"] { background-position: right 10px center; padding-right: .75rem; padding-left: 32px; }
        .card-header .btn-outline-secondary {
            border-color: var(--ak-border); color: var(--ak-text-muted); border-radius: 8px; font-size: .84rem;
        }
        .card-header .btn-outline-secondary:hover { background: var(--ak-accent); border-color: var(--ak-accent); color: #fff; }
        .card-footer { background: #fff; border-top: 1px solid var(--ak-border); }

        /* ---------- Status badges (soft pill style, matches dashboard) ---------- */
        .badge {
            font-weight: 700; font-size: .72rem; padding: .42em .75em; border-radius: 20px;
            text-transform: capitalize; letter-spacing: .01em;
        }
        .badge.bg-success { background: #e7f8f0 !important; color: #067647 !important; }
        .badge.bg-warning { background: #fff4e5 !important; color: #b54708 !important; }
        .badge.bg-danger  { background: #fef3f2 !important; color: #b42318 !important; }
        .badge.bg-info    { background: #eaf2fe !important; color: #175cd3 !important; }
        .badge.bg-primary { background: #eaf2fe !important; color: #175cd3 !important; }
        .badge.bg-secondary { background: #f2f4f7 !important; color: #475467 !important; }
        .badge.bg-dark    { background: #eef0f4 !important; color: #1f2536 !important; }

        /* ---------- Tables ---------- */
        .table > :not(caption) > * > * { padding: .75rem 1rem; }
        .table tbody tr { transition: background .12s ease; }
        .table-hover tbody tr:hover { background: #f8fafd; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table a:not(.btn) { color: #1f2536; text-decoration: none; font-weight: 600; }
        .table a:not(.btn):hover { color: var(--ak-accent); text-decoration: underline; }
        .table td.text-center.text-muted {
            padding: 2.75rem 1rem !important; font-size: .875rem;
        }
        .table td.text-center.text-muted::before {
            content: ''; display: block; width: 40px; height: 40px; margin: 0 auto .6rem; opacity: .35;
            background: no-repeat center / contain url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='none' stroke='%236b7385' stroke-width='1.6' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M3 12h4l2 3h6l2-3h4M5 12 3 19h18l-2-7M5 12l2-7h10l2 7'/%3E%3C/svg%3E");
        }
        .table .btn-sm { border-radius: 7px; }

        /* ---------- Pagination ---------- */
        .pagination { gap: .25rem; }
        .page-link {
            border: 1px solid var(--ak-border); color: #1f2536; border-radius: 8px !important; font-size: .85rem;
            margin: 0; padding: .4rem .7rem;
        }
        .page-link:hover { background: var(--ak-accent-light); color: var(--ak-accent); border-color: var(--ak-accent-light); }
        .page-item.active .page-link { background: var(--ak-accent); border-color: var(--ak-accent); }
        .page-item.disabled .page-link { color: #c3c8d4; background: #fafbfd; }

        /* ---------- Dashboard widgets ---------- */
        .stat-card { border-radius: var(--ak-radius); overflow: hidden; position: relative; }
        .stat-card .stat-icon {
            width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-card .stat-value { font-size: 1.65rem; font-weight: 800; line-height: 1.1; }
        .stat-card .stat-label { font-size: .78rem; color: var(--ak-text-muted); font-weight: 600; }
        .stat-card .stat-trend { font-size: .75rem; font-weight: 700; }
        .welcome-banner {
            background: linear-gradient(120deg, var(--ak-navy) 0%, #1c3a73 60%, var(--ak-accent) 130%);
            border-radius: var(--ak-radius); color: #fff; padding: 1.75rem 2rem; position: relative; overflow: hidden;
        }
        .welcome-banner::after {
            content: ''; position: absolute; inset: 0; opacity: .5;
            background: radial-gradient(circle at 85% 20%, rgba(255,255,255,.18), transparent 45%);
        }
        .welcome-banner h4 { font-weight: 800; position: relative; }
        .welcome-banner p { color: rgba(255,255,255,.8); position: relative; }
        .status-badge { font-size: .72rem; font-weight: 700; padding: .35em .7em; border-radius: 20px; text-transform: capitalize; }
        .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--ak-text-muted); }
        .empty-state i { font-size: 2rem; opacity: .35; display: block; margin-bottom: .5rem; }
        .quick-action {
            display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-radius: 12px;
            border: 1px solid var(--ak-border); text-decoration: none; color: #1f2536; font-weight: 600; font-size: .875rem;
            transition: border-color .15s ease, background .15s ease;
        }
        .quick-action:hover { border-color: var(--ak-accent); background: var(--ak-accent-light); color: var(--ak-accent); }
        .quick-action .qa-icon {
            width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
            background: var(--ak-accent-light); color: var(--ak-accent); font-size: 1.05rem; flex-shrink: 0;
        }
        .list-row { padding: .65rem 0; border-bottom: 1px solid var(--ak-border); }
        .list-row:last-child { border-bottom: none; }

        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            #sidebar.show { transform: translateX(0); }
            .content-wrap { margin-left: 0; }
            #sidebar-backdrop.show { display: block; }
        }
        #sidebar-backdrop {
            display: none; position: fixed; inset: 0; background: rgba(10,18,38,.45); z-index: 1025;
        }
        .media-thumb { width: 100%; height: 110px; object-fit: cover; border-radius: 6px; background:#eef1f5; }
        .media-pick-thumb { width: 90px; height: 90px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e9f0; background:#f4f6f9; }
        .picker-grid-item { cursor: pointer; border: 2px solid transparent; border-radius: 8px; padding: 4px; }
        .picker-grid-item:hover { border-color: var(--ak-accent); }
        .picker-grid-item img, .picker-grid-item .file-icon { width: 100%; height: 90px; object-fit: cover; border-radius: 6px; background:#eef1f5; }
        .file-icon { display:flex; align-items:center; justify-content:center; font-size: 2rem; color:#6b7280; }
    </style>
    @stack('styles')
</head>
<body>

<div id="sidebar-backdrop" onclick="document.getElementById('sidebar').classList.remove('show'); this.classList.remove('show')"></div>

<nav id="sidebar">
    <div class="brand">
        <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="brand-logo">
        <small>Admin Panel</small>
    </div>
    <div class="nav flex-column">

        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section-title">Content</div>
        @can('pages.view')
        <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}"><i class="bi bi-file-earmark-text"></i> Pages</a>
        @endcan
        @can('homepage.view')
        <a class="nav-link {{ request()->routeIs('admin.hero-slides.*') ? 'active' : '' }}" href="{{ route('admin.hero-slides.index') }}"><i class="bi bi-images"></i> Hero Slides</a>
        <a class="nav-link {{ request()->routeIs('admin.content-blocks.*') ? 'active' : '' }}" href="{{ route('admin.content-blocks.index') }}"><i class="bi bi-layout-text-window"></i> Content Blocks</a>
        @endcan
        @can('testimonials.view')
        <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}"><i class="bi bi-chat-quote"></i> Testimonials</a>
        @endcan
        @can('homepage.view')
        <a class="nav-link {{ request()->routeIs('admin.famous-clients.*') ? 'active' : '' }}" href="{{ route('admin.famous-clients.index') }}"><i class="bi bi-building"></i> Famous Clients</a>
        @endcan
        @can('menus.view')
        <a class="nav-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ route('admin.menus.index') }}"><i class="bi bi-list-ul"></i> Menus</a>
        @endcan
        @can('faqs.view')
        <a class="nav-link {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.faq-categories.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}"><i class="bi bi-question-circle"></i> FAQs</a>
        @endcan
        @can('blogs.view')
        <a class="nav-link {{ request()->routeIs('admin.blog-posts.*') || request()->routeIs('admin.blog-categories.*') || request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}" href="{{ route('admin.blog-posts.index') }}"><i class="bi bi-journal-text"></i> Blogs</a>
        @endcan
        @can('events.view')
        <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}"><i class="bi bi-calendar-event"></i> Events</a>
        @endcan
        @can('careers.view')
        <a class="nav-link {{ request()->routeIs('admin.job-openings.*') ? 'active' : '' }}" href="{{ route('admin.job-openings.index') }}"><i class="bi bi-briefcase"></i> Job Openings</a>
        <a class="nav-link {{ request()->routeIs('admin.job-applications.*') ? 'active' : '' }}" href="{{ route('admin.job-applications.index') }}"><i class="bi bi-person-badge"></i> Applications</a>
        @endcan

        <div class="nav-section-title">Products</div>
        @can('categories.view')
        <a class="nav-link {{ request()->routeIs('admin.product-categories.*') ? 'active' : '' }}" href="{{ route('admin.product-categories.index') }}"><i class="bi bi-diagram-3"></i> Categories</a>
        @endcan
        @can('products.view')
        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-box-seam"></i> Products</a>
        @endcan
        @can('attributes.view')
        <a class="nav-link {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}"><i class="bi bi-sliders"></i> Attributes</a>
        @endcan

        <div class="nav-section-title">Commerce</div>
        @can('orders.view')
        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><i class="bi bi-receipt"></i> Orders</a>
        @endcan
        @can('customers.view')
        <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}"><i class="bi bi-people"></i> Customers</a>
        @endcan

        <div class="nav-section-title">Locations</div>
        @can('branches.view')
        <a class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}"><i class="bi bi-geo-alt"></i> Branches</a>
        @endcan

        <div class="nav-section-title">Media</div>
        @can('media.view')
        <a class="nav-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}"><i class="bi bi-folder2-open"></i> Media Library</a>
        @endcan

        <div class="nav-section-title">Communication</div>
        @can('contact_messages.view')
        <a class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}" href="{{ route('admin.contact-messages.index') }}"><i class="bi bi-envelope"></i> Contact Messages</a>
        @endcan
        @can('careers.view')
        <a class="nav-link {{ request()->routeIs('admin.job-applications.*') ? 'active' : '' }}" href="{{ route('admin.job-applications.index') }}"><i class="bi bi-file-earmark-person"></i> Career Applications</a>
        @endcan

        <div class="nav-section-title">Settings</div>
        @can('settings.view')
        <a class="nav-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}" href="{{ route('admin.settings.general') }}"><i class="bi bi-gear"></i> General</a>
        <a class="nav-link {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}" href="{{ route('admin.settings.seo') }}"><i class="bi bi-search"></i> SEO</a>
        <a class="nav-link {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}" href="{{ route('admin.settings.social') }}"><i class="bi bi-share"></i> Social Media</a>
        @endcan
        @can('users.view')
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-person-gear"></i> Users</a>
        @endcan
        @can('roles.view')
        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-lock"></i> Roles &amp; Permissions</a>
        @endcan
    </div>
</nav>

<div class="content-wrap">
    <div id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show'); document.getElementById('sidebar-backdrop').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 d-none d-md-block">@yield('page-title', 'Dashboard')</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.contact-messages.index') }}" class="topbar-icon-btn" title="Contact messages">
                <i class="bi bi-envelope"></i>
                @if(($unreadCount ?? 0) > 0)<span class="badge-dot"></span>@endif
            </a>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle ms-1" data-bs-toggle="dropdown">
                    <span class="avatar-circle me-2">{{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}</span>
                    <span class="text-dark small d-none d-sm-inline">{{ auth()->user()->name ?? '' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email ?? '' }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-body">
        @include('admin.partials.flash')
        @yield('content')
    </div>
</div>

@include('admin.partials.media-picker-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.AK_CSRF = document.querySelector('meta[name="csrf-token"]').content;
</script>
@stack('scripts')
</body>
</html>
