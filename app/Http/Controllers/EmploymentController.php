<?php

namespace App\Http\Controllers;

use App\Actions\CreateEmploymentAction;
use App\Actions\DeleteEmploymentAction;
use App\Actions\UpdateEmploymentAction;
use App\Models\Customer;
use App\Models\Employment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class EmploymentController extends Controller implements HasMiddleware
{
    public function __construct(
        protected CreateEmploymentAction $createEmploymentAction,
        protected UpdateEmploymentAction $updateEmploymentAction,
        protected DeleteEmploymentAction $deleteEmploymentAction,
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

    public function store(Customer $customer, Request $request): RedirectResponse
    {
        $this->createEmploymentAction->handle(['customer_id' => $customer->id] + $request->all());

        session()->flash('status', __('Pekerjaan berhasil ditambahkan.'));

        return redirect()->route('customers.show', $customer);
    }

    public function edit(Customer $customer, Employment $employment): View
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        return view('pages.employments.edit', compact('customer', 'employment'));
    }

    public function update(Customer $customer, Request $request, Employment $employment): RedirectResponse
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        $this->updateEmploymentAction->handle(['employment_id' => $employment->id] + $request->all());

        session()->flash('status', __('Pekerjaan berhasil diperbarui.'));

        return redirect()->route('customers.show', $customer);
    }

    public function destroy(Customer $customer, Employment $employment): RedirectResponse
    {
        abort_unless($employment->customer_id === $customer->id, 404);

        $this->deleteEmploymentAction->handle($employment);

        session()->flash('status', __('Pekerjaan berhasil dihapus.'));

        return redirect()->route('customers.show', $customer);
    }
}
