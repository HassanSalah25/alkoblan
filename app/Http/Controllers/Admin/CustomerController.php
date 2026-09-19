<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::query()
            ->where('type', 'customer')
            ->withCount('orders')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_if($customer->type !== 'customer', 404);

        $orders = $customer->orders()->orderByDesc('placed_at')->get();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function edit(User $customer)
    {
        abort_if($customer->type !== 'customer', 404);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        abort_if($customer->type !== 'customer', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $customer->update($data);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function toggle(User $customer)
    {
        abort_if($customer->type !== 'customer', 404);

        $customer->update(['status' => $customer->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(User $customer)
    {
        abort_if($customer->type !== 'customer', 404);

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }
}
