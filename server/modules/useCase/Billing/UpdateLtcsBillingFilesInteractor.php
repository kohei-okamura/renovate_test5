<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Arrays;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：ファイル更新ユースケース実装.
 */
final class UpdateLtcsBillingFilesInteractor implements UpdateLtcsBillingFilesUseCase
{
    use Logging;

    private CreateLtcsBillingInvoiceCsvUseCase $createCsvUseCase;
    private CreateLtcsBillingInvoicePdfUseCase $createPdfUseCase;
    private LookupLtcsBillingUseCase $lookupUseCase;
    private LtcsBillingBundleRepository $bundleRepository;
    private LtcsBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\CreateLtcsBillingInvoiceCsvUseCase $createCsvUseCase
     * @param \UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase $createPdfUseCase
     * @param \UseCase\Billing\LookupLtcsBillingUseCase $lookupUseCase
     * @param \Domain\Billing\LtcsBillingBundleRepository $bundleRepository
     * @param \Domain\Billing\LtcsBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        CreateLtcsBillingInvoiceCsvUseCase $createCsvUseCase,
        CreateLtcsBillingInvoicePdfUseCase $createPdfUseCase,
        LookupLtcsBillingUseCase $lookupUseCase,
        LtcsBillingBundleRepository $bundleRepository,
        LtcsBillingRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->createCsvUseCase = $createCsvUseCase;
        $this->createPdfUseCase = $createPdfUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->bundleRepository = $bundleRepository;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): LtcsBilling
    {
        $billing = $this->lookupBilling($context, $id);
        $bundles = $this->findBundles($id);
        $files = $bundles->map(function (LtcsBillingBundle $bundle) use ($context, $billing) {
            return Arrays::generate(function () use ($context, $billing, $bundle) {
                yield $this->createCsvUseCase->handle($context, $billing, $bundle);
                yield $this->createPdfUseCase->handle($context, $billing, $bundle);
            });
        })->flatten()->toArray();

        $updatedAt = Carbon::now();

        $overwrites = compact('files', 'updatedAt');

        $x = $this->transaction->run(fn (): LtcsBilling => $this->repository->store(
            $billing->copy($overwrites)
        ));

        $this->logger()->info(
            '介護保険サービス請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 介護保険サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Billing\LtcsBilling
     */
    private function lookupBilling(Context $context, int $id): LtcsBilling
    {
        return $this->lookupUseCase
            ->handle($context, Permission::updateBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsBilling({$id}) not found");
            });
    }

    /**
     * 介護保険サービス：請求単位の一覧を取得する.
     *
     * @param int $billingId
     * @return \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq
     */
    private function findBundles(int $billingId): Seq
    {
        return $this->bundleRepository
            ->lookupByBillingId($billingId)
            ->values()
            ->flatten();
    }
}
