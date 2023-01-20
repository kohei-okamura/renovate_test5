<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingRepository;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Lib\Exceptions\LogicException;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\File\DownloadStorageUseCase;

/**
 * {@link \UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase} の実装.
 */
final class ImportWithdrawalTransactionFileInteractor implements ImportWithdrawalTransactionFileUseCase
{
    use Logging;

    private DownloadStorageUseCase $downloadStorageUseCase;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private ResolveUserBillingsFromZenginFormatUseCase $resolveUserBillingsFromZenginFormatUseCase;
    private UserBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\File\DownloadStorageUseCase $downloadStorageUseCase
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     * @param \UseCase\UserBilling\ResolveUserBillingsFromZenginFormatUseCase $resolveUserBillingsFromZenginFormatUseCase
     * @param \Domain\UserBilling\UserBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DownloadStorageUseCase $downloadStorageUseCase,
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        ResolveUserBillingsFromZenginFormatUseCase $resolveUserBillingsFromZenginFormatUseCase,
        UserBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->downloadStorageUseCase = $downloadStorageUseCase;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->resolveUserBillingsFromZenginFormatUseCase = $resolveUserBillingsFromZenginFormatUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $path): void
    {
        $file = $this->downloadStorageUseCase->handle($context, $path);

        $userBillingIdToResultCodeMap = $this->resolveUserBillingsFromZenginFormatUseCase->handle($context, $file);
        $xs = $this->transaction->run(function () use ($context, $userBillingIdToResultCodeMap): Seq {
            $userBillings = $this->lookupUserBilling($context, ...$userBillingIdToResultCodeMap->keys());
            return $userBillings->map(function (UserBilling $x) use ($userBillingIdToResultCodeMap): UserBilling {
                /** @var \Domain\UserBilling\WithdrawalResultCode $withdrawalResultCode */
                [$withdrawalResultCode, $deductedOn] = $userBillingIdToResultCodeMap->get($x->id)->getOrElse(function () use ($x): void {
                    throw new LogicException("cannot find userBilling with the id({$x->id})");
                });
                return $this->repository->store($x->copy([
                    'depositedAt' => $withdrawalResultCode === WithdrawalResultCode::done()
                        ? $deductedOn
                        : null,
                    'result' => $withdrawalResultCode === WithdrawalResultCode::done()
                        ? UserBillingResult::paid()
                        : UserBillingResult::unpaid(),
                    'withdrawalResultCode' => $withdrawalResultCode,
                    'transactedAt' => Carbon::now(),
                ]));
            });
        });

        $this->logger()->info(
            '利用者請求が更新されました',
            ['ids' => implode(',', $xs->map(fn (UserBilling $x) => $x->id)->toArray())] + $context->logContext() // TODO DEV-1577 複数IDのログ出力
        );
    }

    /**
     * 利用者請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     * @return \Domain\UserBilling\UserBilling[]|\ScalikePHP\Seq
     */
    private function lookupUserBilling(Context $context, int ...$ids): Seq
    {
        return $this->lookupUserBillingUseCase->handle($context, Permission::downloadWithdrawalTransactions(), ...$ids);
    }
}
