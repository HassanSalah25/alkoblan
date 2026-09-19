<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use Illuminate\Http\Request;

class ContentBlockController extends Controller
{
    public function index()
    {
        $contentBlocks = ContentBlock::query()->orderBy('key')->get();

        return view('admin.content-blocks.index', compact('contentBlocks'));
    }

    public function edit(ContentBlock $contentBlock)
    {
        return view('admin.content-blocks.edit', compact('contentBlock'));
    }

    public function update(Request $request, ContentBlock $contentBlock)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'subtitle_ar' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
            'image_id' => ['nullable', 'exists:media,id'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_text_ar' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'extra' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (array_key_exists('extra', $data)) {
            $extraRaw = trim((string) $data['extra']);
            if ($extraRaw === '') {
                $data['extra'] = null;
            } else {
                $decoded = json_decode($extraRaw, true);
                $data['extra'] = json_last_error() === JSON_ERROR_NONE ? $decoded : $contentBlock->extra;
            }
        }

        $contentBlock->update($data);

        return redirect()->route('admin.content-blocks.index')->with('success', 'Content block updated.');
    }
}
