<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'dashboard.view';

    case ViewCustomer = 'customer.view';
    case CreateCustomer = 'customer.create';
    case UpdateCustomer = 'customer.update';
    case DeleteCustomer = 'customer.delete';

    case ViewEmployment = 'employment.view';
    case ManageEmployment = 'employment.manage';

    case ViewLoan = 'loan.view';
    case CreateLoan = 'loan.create';
    case UpdateLoan = 'loan.update';
    case SubmitLoan = 'loan.submit';
    case ApproveLoan = 'loan.approve';
    case RejectLoan = 'loan.reject';
    case DisburseLoan = 'loan.disburse';
    case ManageLoanStatus = 'loan.status';

    case ViewInstallment = 'installment.view';

    case ViewPayment = 'payment.view';
    case CreatePayment = 'payment.create';
    case ReversePayment = 'payment.reverse';

    case ViewCollection = 'collection.view';
    case CreateCollection = 'collection.create';

    case ViewCollateral = 'collateral.view';
    case ReceiveCollateral = 'collateral.receive';
    case UpdateCollateralCustody = 'collateral.custody';
    case PrepareCollateralRelease = 'collateral.prepare_release';
    case ReleaseCollateral = 'collateral.release';

    case ViewIdentityVerification = 'identity.view';
    case VerifyIdentity = 'identity.verify';

    case ViewUser = 'user.view';
    case CreateUser = 'user.create';
    case UpdateUser = 'user.update';
    case DeleteUser = 'user.delete';
    case ManagePermissions = 'permission.manage';

    case ViewReport = 'report.view';
    case ViewAuditLog = 'audit.view';
    case ViewSetting = 'setting.view';
}
