<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\MediaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $productsCat = MediaCategory::query()->updateOrCreate(['slug' => 'products'], ['name' => 'Products', 'name_ar' => 'المنتجات']);
        MediaCategory::query()->updateOrCreate(['slug' => 'general'], ['name' => 'General', 'name_ar' => 'عام']);
        MediaCategory::query()->updateOrCreate(['slug' => 'certificates'], ['name' => 'Certificates', 'name_ar' => 'الشهادات']);
        MediaCategory::query()->updateOrCreate(['slug' => 'catalogs'], ['name' => 'Catalogs & Documents', 'name_ar' => 'الكتالوجات والوثائق']);

        $files = [
            'pipe_product.jpg' => ['title' => 'Pipe Product', 'title_ar' => 'منتج مواسير'],
            'fitting_product.jpg' => ['title' => 'Fitting Product', 'title_ar' => 'منتج وصلات'],
            'valve_product.jpg' => ['title' => 'Valve Product', 'title_ar' => 'منتج صمامات'],
            'accessories_product.jpg' => ['title' => 'Accessories Product', 'title_ar' => 'منتج ملحقات'],
            'factory_about.jpg' => ['title' => 'Factory', 'title_ar' => 'المصنع'],
            'hero_banner.jpg' => ['title' => 'Hero Banner', 'title_ar' => 'بانر رئيسي'],
        ];

        foreach ($files as $filename => $meta) {
            $path = 'site/'.$filename;
            $fullPath = storage_path('app/public/'.$path);
            $size = File::exists($fullPath) ? File::size($fullPath) : 0;

            Media::query()->updateOrCreate(['path' => $path], [
                'media_category_id' => $productsCat->id,
                'disk' => 'public',
                'original_name' => $filename,
                'mime_type' => 'image/jpeg',
                'type' => 'image',
                'size' => $size,
                'title' => $meta['title'],
                'title_ar' => $meta['title_ar'],
                'alt_text' => $meta['title_ar'],
                'visibility' => 'public',
            ]);
        }
    }
}
