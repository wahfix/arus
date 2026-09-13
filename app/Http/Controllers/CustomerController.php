<?php

namespace App\Http\Controllers;

use App\Actions\CreateCustomerAction;
use App\Actions\DeleteCustomerAction;
use App\Actions\GetCustomersAction;
use App\Actions\UpdateCustomerAction;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CustomerController extends Controller implements HasMiddleware
{
    public function __construct(
        protected GetCustomersAction $getCustomersAction,
        protected CreateCustomerAction $createCustomerAction,
        protected UpdateCustomerAction $updateCustomerAction,
        protected DeleteCustomerAction $deleteCustomerAction,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:customer.view', only: ['index', 'show']),
            new Middleware('permission:customer.create', only: ['create', 'store']),
            new Middleware('permission:customer.update', only: ['edit', 'update']),
            new Middleware('permission:customer.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $customers = $this->getCustomersAction->handle($request->string('q')->toString());

        return view('pages.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('pages.customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $this->createCustomerAction->handle($request->all());

        session()->flash('status', __('Nasabah berhasil ditambahkan.'));

        return redirect()->route('customers.show', $customer);
    }

    public function show(Customer $customer, Request $request): View
    {
        $customer->load('employments');

        $tab = (string) $request->string('tab', 'overview');

        return view('pages.customers.show', compact('customer', 'tab'));
    }

    public function edit(Customer $customer): View
    {
        return view('pages.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->updateCustomerAction->handle(['customer_id' => $customer->id] + $request->all());

        session()->flash('status', __('Nasabah berhasil diperbarui.'));

        return redirect()->route('customers.show', $customer);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->deleteCustomerAction->handle($customer);

        session()->flash('status', __('Nasabah berhasil dihapus.'));

        return redirect()->route('customers.index');
    }
}
