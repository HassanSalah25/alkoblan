<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function store(Request $request, Attribute $attribute)
    {
        $data = $this->validated($request);
        $data['attribute_id'] = $attribute->id;
        AttributeValue::create($data);

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Value added.');
    }

    public function update(Request $request, AttributeValue $attribute_value)
    {
        $data = $this->validated($request);
        $attribute_value->update($data);

        return redirect()->route('admin.attributes.edit', $attribute_value->attribute_id)->with('success', 'Value updated.');
    }

    public function destroy(AttributeValue $attribute_value)
    {
        if ($attribute_value->products()->exists() || $attribute_value->variants()->exists()) {
            return back()->with('error', 'Cannot delete: this value is assigned to products/variants.');
        }

        $attributeId = $attribute_value->attribute_id;
        $attribute_value->delete();

        return redirect()->route('admin.attributes.edit', $attributeId)->with('success', 'Value deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'value_ar' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
