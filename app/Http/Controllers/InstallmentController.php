<?php

namespace App\Http\Controllers;

use App\Actions\GetInstallmentsAction;
use App\Enums\InstallmentStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class InstallmentController extends Controller implements HasMiddleware
{
    public function __construct(protected GetInstallmentsAction $getInstallmentsAction) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:installment.view', only: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $installments = $this->getInstallmentsAction->handle($request->only(['q', 'status']));

        $statuses = InstallmentStatus::cases();
        $statusFilter = $request->string('status')->toString();

        return view('pages.installments.index', compact('installments', 'statuses', 'statusFilter'));
    }
}
