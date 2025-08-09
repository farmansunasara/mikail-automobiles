<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::withCount('invoices');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('mobile', 'like', '%' . $request->search . '%')
                  ->orWhere('gstin', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'mobile' => 'nullable|string|max:15|unique:customers,mobile',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:15|unique:customers,gstin',
        ]);

        $customer = Customer::create($request->only(['name','email','mobile','address','state','gstin']));

        // Handle AJAX requests (for quick customer creation in invoice form)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'mobile' => $customer->mobile,
                    'address' => $customer->address,
                    'state' => $customer->state,
                    'gstin' => $customer->gstin,
                ]
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load('invoices.items');
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable','email','max:255', Rule::unique('customers')->ignore($customer->id)],
            'mobile' => ['nullable','string','max:15', Rule::unique('customers')->ignore($customer->id)],
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'gstin' => ['nullable','string','max:15', Rule::unique('customers')->ignore($customer->id)],
        ]);

        $customer->update($request->only(['name','email','mobile','address','state','gstin']));

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->exists()) {
            return redirect()->route('customers.index')->with('error', 'Cannot delete customer with associated invoices.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    /**
     * Search for customers via API.
     */
    public function search(Request $request)
    {
        $query = Customer::query();
        if ($request->filled('term')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->term . '%')
                  ->orWhere('mobile', 'like', '%' . $request->term . '%');
            });
        }
        $customers = $query->take(10)->get(['id', 'name', 'mobile', 'address', 'state', 'gstin']);
        return response()->json($customers);
    }
}
