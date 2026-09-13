<flux:card>
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Data Pinjaman') }}</flux:heading>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @if ($showCustomerSelect)
                <flux:select name="customer_id" :label="__('Nasabah')" x-model="form.customer_id" required>
                    <flux:select.option value="">{{ __('Pilih nasabah…') }}</flux:select.option>
                    @foreach ($customers as $customer)
                        <flux:select.option :value="$customer->id">{{ $customer->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <flux:input
                name="principal_amount"
                :label="__('Pokok Pinjaman (Rp)')"
                type="number"
                min="1"
                x-model="form.principal_amount"
                x-on:input.debounce.300ms="updatePreview()"
                required
            />

            <flux:input
                name="interest_rate"
                :label="__('Bunga (% per periode)')"
                type="number"
                step="0.05"
                min="0"
                max="100"
                x-model="form.interest_rate"
                x-on:input.debounce.300ms="updatePreview()"
                required
            />

            <flux:select name="interest_method" :label="__('Metode Bunga')" x-model="form.interest_method"
                         x-on:change="updatePreview()" required>
                @foreach (App\Enums\InterestMethod::cases() as $method)
                    <flux:select.option :value="$method->value">{{ $method->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select name="tenor" :label="__('Tenor (jumlah periode)')" x-model="form.tenor"
                         x-on:change="updatePreview()" required>
                @foreach ([1, 2, 3, 6, 12] as $tenor)
                    <flux:select.option :value="$tenor">{{ $tenor }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select name="installment_frequency" :label="__('Frekuensi Angsuran')"
                         x-model="form.installment_frequency" x-on:change="updatePreview()" required>
                @foreach (App\Enums\InstallmentFrequency::cases() as $frequency)
                    <flux:select.option :value="$frequency->value">{{ $frequency->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                name="disbursement_date"
                :label="__('Tanggal Pencairan')"
                type="date"
                x-model="form.disbursement_date"
                x-on:change="updatePreview()"
            />

            <flux:input
                name="first_due_date"
                :label="__('Jatuh Tempo Pertama')"
                type="date"
                x-model="form.first_due_date"
                x-on:change="updatePreview()"
                required
            />
        </div>
    </div>
</flux:card>

<flux:card>
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Ringkasan Perhitungan') }}</flux:heading>

        <template x-if="loading">
            <flux:text>{{ __('Menghitung…') }}</flux:text>
        </template>

        <template x-if="!loading && error">
            <flux:callout color="red" icon="exclamation-triangle">
                <span x-text="error"></span>
            </flux:callout>
        </template>

        <template x-if="!loading && !error && preview">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pokok') }}</dt>
                    <dd class="text-sm" x-text="money(preview.principal_amount)"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Bunga total') }}</dt>
                    <dd class="text-sm" x-text="money(preview.total_interest)"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total yang harus dibayar') }}</dt>
                    <dd class="text-sm font-medium" x-text="money(preview.total_payable)"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Angsuran per periode') }}</dt>
                    <dd class="text-sm font-medium" x-text="money(preview.installment_amount)"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh tempo pertama') }}</dt>
                    <dd class="text-sm" x-text="preview.first_due_date ?? form.first_due_date"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Jatuh tempo akhir') }}</dt>
                    <dd class="text-sm" x-text="preview.maturity_date"></dd>
                </div>
            </dl>
        </template>

        <template x-if="!loading && !error && !preview">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Lengkapi data untuk melihat ringkasan perhitungan.') }}
            </flux:text>
        </template>
    </div>
</flux:card>

<script>
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('loanCreation', (options) => {
            const money = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');

            return {
                form: options.form,
                csrf: options.csrf,
                previewUrl: options.previewUrl,
                preview: null,
                error: null,
                loading: false,
                money(value) {
                    return money(value);
                },
                async updatePreview() {
                    const params = new URLSearchParams(Object.fromEntries(
                        Object.entries(this.form).filter(([, value]) => value !== null && value !== ''),
                    ));

                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch(this.previewUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                            body: params.toString(),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            this.error = payload.message ?? @json(__('Perhitungan gagal.'));
                        } else {
                            this.preview = payload;
                        }
                    } catch (exception) {
                        this.error = @json(__('Terjadi kesalahan saat menghitung.'));
                    } finally {
                        this.loading = false;
                    }
                },
            };
        });
    });
</script>