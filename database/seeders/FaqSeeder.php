<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $general = FaqCategory::query()->updateOrCreate(['slug' => 'general'], ['name' => 'General', 'name_ar' => 'عام', 'sort_order' => 1, 'is_active' => true]);
        $products = FaqCategory::query()->updateOrCreate(['slug' => 'products'], ['name' => 'Products', 'name_ar' => 'المنتجات', 'sort_order' => 2, 'is_active' => true]);
        $orders = FaqCategory::query()->updateOrCreate(['slug' => 'orders-shipping'], ['name' => 'Orders & Shipping', 'name_ar' => 'الطلبات والشحن', 'sort_order' => 3, 'is_active' => true]);
        $warranty = FaqCategory::query()->updateOrCreate(['slug' => 'warranty'], ['name' => 'Warranty & Support', 'name_ar' => 'الضمان والدعم', 'sort_order' => 4, 'is_active' => true]);

        $faqs = [
            [$general, 'When was AL-KOBLAN founded?', 'متى تأسس مصنع الكبلان؟', 'AL-KOBLAN was founded in 1995 and has been a leading manufacturer of thermal water pipes in Saudi Arabia ever since.', 'تأسس مصنع الكبلان عام 1995 وهو منذ ذلك الحين من الشركات الرائدة في تصنيع مواسير المياه الحرارية بالمملكة.'],
            [$general, 'Where are your factories located?', 'أين تقع مصانعكم؟', 'Our head office and main factory are located in the Second Industrial City in Riyadh, with branches in Jeddah and Dammam.', 'يقع مقرنا الرئيسي ومصنعنا في المنطقة الصناعية الثانية بالرياض، مع فروع في جدة والدمام.'],
            [$products, 'What pipe sizes do you offer?', 'ما هي أحجام المواسير المتوفرة؟', 'We offer pipes in diameters ranging from 20mm to 110mm, covering PN10 to PN25 pressure ratings.', 'نقدم مواسير بأقطار تتراوح من 20 مم إلى 110 مم، وتصنيفات ضغط من PN10 إلى PN25.'],
            [$products, 'Are your products certified?', 'هل منتجاتكم معتمدة؟', 'Yes, all our products are certified to SASO and ISO 9001 international quality standards.', 'نعم، جميع منتجاتنا معتمدة وفق معايير سابر ومعايير الجودة العالمية ISO 9001.'],
            [$orders, 'Do you offer bulk/wholesale pricing?', 'هل تقدمون أسعار جملة؟', 'Yes, please contact our sales team directly for bulk order pricing and project quotes.', 'نعم، يرجى التواصل مع فريق المبيعات مباشرة للحصول على أسعار الجملة وعروض المشاريع.'],
            [$orders, 'How can I place an order?', 'كيف يمكنني تقديم طلب؟', 'You can browse our shop online and submit an order request, or contact any of our branches directly.', 'يمكنك تصفح المتجر الإلكتروني وتقديم طلب، أو التواصل مع أي من فروعنا مباشرة.'],
            [$warranty, 'Do you provide installation supervision?', 'هل تقدمون إشراف على التركيب؟', 'Yes, we offer free installation supervision for qualifying projects. Contact us for eligibility details.', 'نعم، نقدم إشراف تركيب مجاني للمشاريع المؤهلة. تواصل معنا لمعرفة تفاصيل الأهلية.'],
            [$warranty, 'What is your product warranty period?', 'ما هي مدة ضمان المنتجات؟', 'Our pipes and fittings carry a manufacturer warranty of up to 10 years when installed according to our guidelines.', 'تحمل مواسيرنا ووصلاتنا ضمان تصنيع يصل إلى 10 سنوات عند التركيب وفق تعليماتنا.'],
        ];

        foreach ($faqs as $i => [$cat, $q, $qAr, $a, $aAr]) {
            Faq::query()->updateOrCreate(['question' => $q], [
                'faq_category_id' => $cat->id, 'question_ar' => $qAr, 'answer' => $a, 'answer_ar' => $aAr,
                'sort_order' => $i, 'is_active' => true,
            ]);
        }
    }
}
