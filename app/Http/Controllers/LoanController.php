<?php

namespace App\Http\Controllers;

use App\Actions\ApproveLoanAction;
use App\Actions\CancelLoanAction;
use App\Actions\ClearLoanOverdueAction;
use App\Actions\CompleteLoanAction;
use App\Actions\CreateLoanAction;
use App\Actions\DefaultLoanAction;
use App\Actions\DisburseLoanAction;
use App\Actions\GetLoansAction;
use App\Actions\MarkLoanOverdueAction;
use App\Actions\PrepareLoanAction;
use App\Actions\RejectLoanAction;
use App\Actions\ReviewLoanAction;
use App\Actions\SubmitLoanAction;
use App\Actions\UpdateLoanAction;
use App\Enums\InstallmentFrequency;
use App\Enums\InterestMethod;
use App\Enums\LoanStatus;
use App\Models\Customer;
use App\Models\Loan;
use App\Services\LoanCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class LoanController extends Controller implements HasMiddleware
{
    public function __construct(
        protected GetLoansAction $getLoansAction,
        protected CreateLoanAction $createLoanAction,
        protected UpdateLoanAction $updateLoanAction,
        protected SubmitLoanAction $submitLoanAction,
        protected ReviewLoanAction $reviewLoanAction,
        protected ApproveLoanAction $approveLoanAction,
        protected PrepareLoanAction $prepareLoanAction,
        protected DisburseLoanAction $disburseLoanAction,
        protected RejectLoanAction $rejectLoanAction,
        protected CancelLoanAction $cancelLoanAction,
        protected MarkLoanOverdueAction $markLoanOverdueAction,
        protected ClearLoanOverdueAction $clearLoanOverdueAction,
        protected CompleteLoanAction $completeLoanAction,
        protected DefaultLoanAction $defaultLoanAction,
        protected LoanCalculationService $calculationService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:loan.view', only: ['index', 'show']),
            new Middleware('permission:loan.create', only: ['create', 'store', 'preview']),
            new Middleware('permission:loan.update', only: ['edit', 'update']),
            new Middleware('permission:loan.submit', only: ['submit']),
            new Middleware('permission:loan.approve', only: ['review', 'approve', 'prepare']),
            new Middleware('permission:loan.reject', only: ['reject']),
            new Middleware('permission:loan.disburse', only: ['disburse']),
            new Middleware('permission:loan.status', only: ['cancel', 'markOverdue', 'clearOverdue', 'complete', 'default']),
        ];
    }

    public function index(Request $request): View
    {
        $loans = $this->getLoansAction->handle($request->only(['q', 'status']));

        $statuses = LoanStatus::cases();
        $statusFilter = $request->string('status')->toString();

        return view('pages.loans.index', compact('loans', 'statuses', 'statusFilter'));
    }

    public function create(): View
    {
        $customers = Customer::query()->where('status', 'ACTIVE')->orderBy('full_name')->get();

        $form = [
            'customer_id' => '',
            'principal_amount' => '',
            'interest_rate' => '2',
            'interest_method' => InterestMethod::Flat->value,
            'tenor' => '3',
            'installment_frequency' => InstallmentFrequency::Monthly->value,
            'disbursement_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
        ];

        return view('pages.loans.create', compact('customers', 'form'));
    }

    public function store(Request $request): RedirectResponse
    {
        $loan = $this->createLoanAction->handle($request->all());

        session()->flash('status', __('Pinjaman dibuat dalam status Draf.'));

        return redirect()->route('loans.show', $loan);
    }

    public function preview(Request $request): JsonResponse
    {
        try {
            $plan = $this->calculationService->compute([
                'principal_amount' => (int) ($request->input('principal_amount') ?? 0),
                'interest_rate_basis_points' => $this->calculationService
                    ->percentageToBasisPoints((string) ($request->input('interest_rate') ?? '0')),
                'interest_method' => (string) $request->input('interest_method', 'FLAT'),
                'tenor' => (int) ($request->input('tenor') ?? 0),
                'installment_frequency' => (string) $request->input('installment_frequency', 'MONTHLY'),
                'first_due_date' => (string) ($request->input('first_due_date') ?? now()->toDateString()),
            ]);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($plan);
    }

    public function show(Loan $loan): View
    {
        $loan->load(['customer', 'installments', 'statusHistories.changedBy', 'createdBy', 'approvedBy']);

        return view('pages.loans.show', compact('loan'));
    }

    public function edit(Loan $loan): View|RedirectResponse
    {
        if ($loan->status !== LoanStatus::Draft->value) {
            session()->flash('error', __('Hanya pinjaman berstatus Draf yang dapat diedit.'));

            return redirect()->route('loans.show', $loan);
        }

        $customers = Customer::query()->where('status', 'ACTIVE')->orderBy('full_name')->get();

        $form = [
            'customer_id' => (string) $loan->customer_id,
            'principal_amount' => (string) $loan->principal_amount,
            'interest_rate' => $this->calculationService->percentageFromBasisPoints($loan->interest_rate_basis_points),
            'interest_method' => $loan->interest_method,
            'tenor' => (string) $loan->tenor,
            'installment_frequency' => $loan->installment_frequency,
            'disbursement_date' => $loan->disbursement_date?->toDateString() ?? now()->toDateString(),
            'first_due_date' => $loan->first_due_date?->toDateString() ?? now()->addMonth()->toDateString(),
        ];

        return view('pages.loans.edit', compact('loan', 'customers', 'form'));
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        try {
            $loan = $this->updateLoanAction->handle(['loan_id' => $loan->id] + $request->all());

            session()->flash('status', __('Pinjaman berhasil diperbarui.'));
        } catch (InvalidArgumentException $exception) {
            session()->flash('error', $exception->getMessage());

            return redirect()->route('loans.show', $loan);
        }

        return redirect()->route('loans.show', $loan);
    }

    public function submit(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->submitLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman diajukan.'),
            $loan,
        );
    }

    public function review(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->reviewLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman masuk dalam review.'),
            $loan,
        );
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->approveLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman disetujui.'),
            $loan,
        );
    }

    public function prepare(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->prepareLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman siap dicairkan.'),
            $loan,
        );
    }

    public function disburse(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->disburseLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman dicairkan dan jadwal angsuran dibuat.'),
            $loan,
        );
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->rejectLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman ditolak.'),
            $loan,
        );
    }

    public function cancel(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->cancelLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman dibatalkan.'),
            $loan,
        );
    }

    public function markOverdue(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->markLoanOverdueAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman ditandai menunggak.'),
            $loan,
        );
    }

    public function clearOverdue(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->clearLoanOverdueAction->handle(['loan_id' => $loan->id]),
            __('Status menunggak dilepas.'),
            $loan,
        );
    }

    public function complete(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->completeLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman ditandai lunas.'),
            $loan,
        );
    }

    public function default(Request $request, Loan $loan): RedirectResponse
    {
        return $this->changeStatus(
            fn () => $this->defaultLoanAction->handle(['loan_id' => $loan->id]),
            __('Pinjaman ditandai macet.'),
            $loan,
        );
    }

    /**
     * @param  callable(): mixed  $operation
     */
    private function changeStatus(callable $operation, string $successMessage, Loan $loan): RedirectResponse
    {
        try {
            $operation();

            session()->flash('status', $successMessage);
        } catch (InvalidArgumentException $exception) {
            session()->flash('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Loan status change failed.', ['exception' => $exception]);

            session()->flash('error', __('Terjadi kesalahan saat mengubah status pinjaman.'));
        }

        return redirect()->route('loans.show', $loan);
    }
}
