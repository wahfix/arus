@php($loanStatus = App\Enums\LoanStatus::tryFrom($status ?? ''))
<flux:badge :color="match ($loanStatus) {
    App\Enums\LoanStatus::Draft => 'zinc',
    App\Enums\LoanStatus::Submitted => 'blue',
    App\Enums\LoanStatus::UnderReview => 'indigo',
    App\Enums\LoanStatus::Approved => 'green',
    App\Enums\LoanStatus::Rejected => 'red',
    App\Enums\LoanStatus::ReadyForDisbursement => 'teal',
    App\Enums\LoanStatus::Active => 'emerald',
    App\Enums\LoanStatus::Overdue => 'amber',
    App\Enums\LoanStatus::Completed => 'gray',
    App\Enums\LoanStatus::Defaulted => 'red',
    App\Enums\LoanStatus::Cancelled => 'zinc',
    default => 'zinc',
}">
    {{ $loanStatus?->label() ?? $status }}
</flux:badge>