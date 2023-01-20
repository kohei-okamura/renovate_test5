<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\UserBilling\UserBillingFinder;
use Domain\UserBilling\UserBillingRepository;
use Domain\UserBilling\WithdrawalTransactionFinder;
use Domain\UserBilling\WithdrawalTransactionRepository;
use Infrastructure\UserBilling\UserBillingFinderEloquentImpl;
use Infrastructure\UserBilling\UserBillingRepositoryEloquentImpl;
use Infrastructure\UserBilling\WithdrawalTransactionFinderEloquentImpl;
use Infrastructure\UserBilling\WithdrawalTransactionRepositoryEloquentImpl;
use UseCase\UserBilling\BuildUserBillingInteractor;
use UseCase\UserBilling\BuildUserBillingInvoicePdfParamInteractor;
use UseCase\UserBilling\BuildUserBillingInvoicePdfParamUseCase;
use UseCase\UserBilling\BuildUserBillingNoticePdfParamInteractor;
use UseCase\UserBilling\BuildUserBillingNoticePdfParamUseCase;
use UseCase\UserBilling\BuildUserBillingReceiptPdfParamInteractor;
use UseCase\UserBilling\BuildUserBillingReceiptPdfParamUseCase;
use UseCase\UserBilling\BuildUserBillingStatementPdfParamInteractor;
use UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase;
use UseCase\UserBilling\BuildUserBillingUseCase;
use UseCase\UserBilling\CreateUserBillingInteractor;
use UseCase\UserBilling\CreateUserBillingListInteractor;
use UseCase\UserBilling\CreateUserBillingListUseCase;
use UseCase\UserBilling\CreateUserBillingUseCase;
use UseCase\UserBilling\CreateWithdrawalTransactionInteractor;
use UseCase\UserBilling\CreateWithdrawalTransactionUseCase;
use UseCase\UserBilling\DeleteUserBillingDepositInteractor;
use UseCase\UserBilling\DeleteUserBillingDepositUseCase;
use UseCase\UserBilling\FindUserBillingInteractor;
use UseCase\UserBilling\FindUserBillingUseCase;
use UseCase\UserBilling\FindWithdrawalTransactionInteractor;
use UseCase\UserBilling\FindWithdrawalTransactionUseCase;
use UseCase\UserBilling\GenerateUserBillingInvoicePdfInteractor;
use UseCase\UserBilling\GenerateUserBillingInvoicePdfUseCase;
use UseCase\UserBilling\GenerateUserBillingNoticePdfInteractor;
use UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase;
use UseCase\UserBilling\GenerateUserBillingReceiptPdfInteractor;
use UseCase\UserBilling\GenerateUserBillingReceiptPdfUseCase;
use UseCase\UserBilling\GenerateUserBillingStatementPdfInteractor;
use UseCase\UserBilling\GenerateUserBillingStatementPdfUseCase;
use UseCase\UserBilling\GenerateWithdrawalTransactionFileInteractor;
use UseCase\UserBilling\GenerateWithdrawalTransactionFileUseCase;
use UseCase\UserBilling\ImportWithdrawalTransactionFileInteractor;
use UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase;
use UseCase\UserBilling\LookupUserBillingInteractor;
use UseCase\UserBilling\LookupUserBillingUseCase;
use UseCase\UserBilling\LookupWithdrawalTransactionInteractor;
use UseCase\UserBilling\LookupWithdrawalTransactionUseCase;
use UseCase\UserBilling\ParseZenginFormatInteractor;
use UseCase\UserBilling\ParseZenginFormatUseCase;
use UseCase\UserBilling\ResolveUserBillingsFromZenginFormatInteractor;
use UseCase\UserBilling\ResolveUserBillingsFromZenginFormatUseCase;
use UseCase\UserBilling\RunCreateUserBillingInvoiceJobInteractor;
use UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase;
use UseCase\UserBilling\RunCreateUserBillingNoticeJobInteractor;
use UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase;
use UseCase\UserBilling\RunCreateUserBillingReceiptJobInteractor;
use UseCase\UserBilling\RunCreateUserBillingReceiptJobUseCase;
use UseCase\UserBilling\RunCreateUserBillingStatementJobInteractor;
use UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase;
use UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobInteractor;
use UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase;
use UseCase\UserBilling\RunCreateWithdrawalTransactionJobInteractor;
use UseCase\UserBilling\RunCreateWithdrawalTransactionJobUseCase;
use UseCase\UserBilling\RunDeleteUserBillingDepositJobInteractor;
use UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase;
use UseCase\UserBilling\RunImportWithdrawalTransactionFileJobInteractor;
use UseCase\UserBilling\RunImportWithdrawalTransactionFileJobUseCase;
use UseCase\UserBilling\RunUpdateUserBillingDepositJobInteractor;
use UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase;
use UseCase\UserBilling\UpdateUserBillingDepositInteractor;
use UseCase\UserBilling\UpdateUserBillingDepositUseCase;
use UseCase\UserBilling\UpdateUserBillingInteractor;
use UseCase\UserBilling\UpdateUserBillingUseCase;

/**
 * UserBilling Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class UserBillingDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            BuildUserBillingInvoicePdfParamUseCase::class => BuildUserBillingInvoicePdfParamInteractor::class,
            BuildUserBillingNoticePdfParamUseCase::class => BuildUserBillingNoticePdfParamInteractor::class,
            BuildUserBillingReceiptPdfParamUseCase::class => BuildUserBillingReceiptPdfParamInteractor::class,
            BuildUserBillingStatementPdfParamUseCase::class => BuildUserBillingStatementPdfParamInteractor::class,
            BuildUserBillingUseCase::class => BuildUserBillingInteractor::class,
            CreateUserBillingListUseCase::class => CreateUserBillingListInteractor::class,
            CreateUserBillingUseCase::class => CreateUserBillingInteractor::class,
            CreateWithdrawalTransactionUseCase::class => CreateWithdrawalTransactionInteractor::class,
            DeleteUserBillingDepositUseCase::class => DeleteUserBillingDepositInteractor::class,
            UpdateUserBillingUseCase::class => UpdateUserBillingInteractor::class,
            FindUserBillingUseCase::class => FindUserBillingInteractor::class,
            FindWithdrawalTransactionUseCase::class => FindWithdrawalTransactionInteractor::class,
            GenerateUserBillingInvoicePdfUseCase::class => GenerateUserBillingInvoicePdfInteractor::class,
            GenerateUserBillingNoticePdfUseCase::class => GenerateUserBillingNoticePdfInteractor::class,
            GenerateUserBillingReceiptPdfUseCase::class => GenerateUserBillingReceiptPdfInteractor::class,
            GenerateUserBillingStatementPdfUseCase::class => GenerateUserBillingStatementPdfInteractor::class,
            GenerateWithdrawalTransactionFileUseCase::class => GenerateWithdrawalTransactionFileInteractor::class,
            ImportWithdrawalTransactionFileUseCase::class => ImportWithdrawalTransactionFileInteractor::class,
            LookupUserBillingUseCase::class => LookupUserBillingInteractor::class,
            LookupWithdrawalTransactionUseCase::class => LookupWithdrawalTransactionInteractor::class,
            ParseZenginFormatUseCase::class => ParseZenginFormatInteractor::class,
            ResolveUserBillingsFromZenginFormatUseCase::class => ResolveUserBillingsFromZenginFormatInteractor::class,
            RunCreateUserBillingInvoiceJobUseCase::class => RunCreateUserBillingInvoiceJobInteractor::class,
            RunCreateUserBillingNoticeJobUseCase::class => RunCreateUserBillingNoticeJobInteractor::class,
            RunCreateUserBillingReceiptJobUseCase::class => RunCreateUserBillingReceiptJobInteractor::class,
            RunCreateUserBillingStatementJobUseCase::class => RunCreateUserBillingStatementJobInteractor::class,
            RunCreateWithdrawalTransactionFileJobUseCase::class => RunCreateWithdrawalTransactionFileJobInteractor::class,
            RunCreateWithdrawalTransactionJobUseCase::class => RunCreateWithdrawalTransactionJobInteractor::class,
            RunDeleteUserBillingDepositJobUseCase::class => RunDeleteUserBillingDepositJobInteractor::class,
            RunImportWithdrawalTransactionFileJobUseCase::class => RunImportWithdrawalTransactionFileJobInteractor::class,
            RunUpdateUserBillingDepositJobUseCase::class => RunUpdateUserBillingDepositJobInteractor::class,
            UpdateUserBillingDepositUseCase::class => UpdateUserBillingDepositInteractor::class,
            UserBillingFinder::class => UserBillingFinderEloquentImpl::class,
            UserBillingRepository::class => UserBillingRepositoryEloquentImpl::class,
            WithdrawalTransactionFinder::class => WithdrawalTransactionFinderEloquentImpl::class,
            WithdrawalTransactionRepository::class => WithdrawalTransactionRepositoryEloquentImpl::class,
        ];
    }
}
