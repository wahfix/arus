@props(['amount' => null])

@if ($amount !== null && $amount !== '')
    <span {{ $attributes }}>{{ App\Support\Money::formatRupiah((int) $amount) }}</span>
@else
    <span {{ $attributes }}>–</span>
@endif