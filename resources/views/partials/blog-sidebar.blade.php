{{-- expects: $recentPosts, $categories --}}
<aside class="sidebar">
    <div class="widget" style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h4 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #2c3e50; border-bottom: 2px solid var(--primary); padding-bottom: 10px; display: inline-block;">بحث</h4>
        <form action="{{ route('blog.index') }}" style="display: flex; gap: 10px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في المدونة..." style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; outline: none; font-family: inherit;">
            <button type="submit" style="background: var(--primary); color: white; border: none; border-radius: 6px; padding: 0 15px; cursor: pointer;"><i class="bi bi-search"></i></button>
        </form>
    </div>

    @if($recentPosts->count())
    <div class="widget" style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h4 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #2c3e50; border-bottom: 2px solid var(--primary); padding-bottom: 10px; display: inline-block;">أحدث المقالات</h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px;">
            @foreach($recentPosts as $rp)
                <li style="display: flex; gap: 15px; align-items: center;">
                    <img src="{{ $rp->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($rp, 'title') }}" style="width: 70px; height: 70px; object-fit: cover; border-radius: 6px;">
                    <div>
                        <h5 style="margin: 0 0 5px 0; font-size: 1rem;"><a href="{{ route('blog.show', $rp->slug) }}" style="color: #2c3e50; text-decoration: none;">{{ \Illuminate\Support\Str::limit(trans_field($rp, 'title'), 40) }}</a></h5>
                        <span style="font-size: 0.8rem; color: #7f8c8d;"><i class="bi bi-calendar3"></i> {{ $rp->published_at?->translatedFormat('d M Y') }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($categories->count())
    <div class="widget" style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h4 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #2c3e50; border-bottom: 2px solid var(--primary); padding-bottom: 10px; display: inline-block;">التصنيفات</h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
            @foreach($categories as $cat)
                <li><a href="{{ route('blog.index', ['category' => $cat->slug]) }}" style="display: flex; justify-content: space-between; color: #555; text-decoration: none; padding: 5px 0; border-bottom: 1px dashed #eee;"><span>{{ trans_field($cat, 'name') }}</span> <span style="background: var(--light-gray); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">{{ $cat->posts_count }}</span></a></li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="widget" style="background: var(--primary); color: white; padding: 30px 25px; border-radius: 12px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <i class="bi bi-envelope-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
        <h4 style="margin: 0 0 10px 0; font-size: 1.4rem;">اشترك في النشرة</h4>
        <p style="margin-bottom: 20px; font-size: 0.95rem; opacity: 0.9;">احصل على أحدث الأخبار والمقالات مباشرة في بريدك الإلكتروني</p>
        <form action="{{ route('newsletter.subscribe') }}" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
            @csrf
            <input type="email" name="email" required placeholder="البريد الإلكتروني" style="width: 100%; padding: 12px; border: none; border-radius: 6px; outline: none; font-family: inherit; text-align: center;">
            <button type="submit" class="btn btn-outline-white" style="width: 100%; justify-content: center;">اشتراك</button>
        </form>
    </div>
</aside>
