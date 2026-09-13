<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmploymentRequest;
use App\Http\Requests\UpdateEmploymentRequest;
use App\Models\Customer;
use App\Models\Employment;
use App\Repositories\EmploymentRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class EmploymentController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly EmploymentRepository $employments,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:employment.manage', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function create(Customer $customer): View
    {
        return view('pages.employments.create', compact('customer'));
    }

    public function store(Customer $customer, StoreEmploymentRequest $request): RedirectResponse
    {
        $this->employments->createForCustomer($customer, $request->validated());

        session()->flash('status', __('Pekerjaan berhasil ditambahkan.'));

        return redirect()->route('customers.show', $customer);
    }

    public function edit(Customer $customer, Employment $employment): View
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        return view('pages.employments.edit', compact('customer', 'employment'));
    }

    public function update(Customer $customer, UpdateEmploymentRequest $request, Employment $employment): RedirectResponse
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        $this->employments->update($employment, $request->validated());

        session()->flash('status', __('Pekerjaan berhasil diperbarui.'));

        return redirect()->route('customers.show', $customer);
    }

    public function destroy(Customer $customer, Employment $employment): RedirectResponse
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        $this->employments->delete($employment);

        session()->flash('status', __('Pekerjaan berhasil dihapus.'));

        return redirect()->route('customers.show', $customer);
    }
}
