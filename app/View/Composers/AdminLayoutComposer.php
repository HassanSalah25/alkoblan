<?php

namespace App\View\Composers;

use App\Models\ContactMessage;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $view->with('unreadCount', ContactMessage::where('status', 'new')->count());
    }
}
