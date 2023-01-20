<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所算定情報（障害・居宅介護）編集実装.
 */
final class EditHomeHelpServiceCalcSpecInteractor implements EditHomeHelpServiceCalcSpecUseCase
{
    use Logging;

    private LookupHomeHelpServiceCalcSpecUseCase $lookupHomeHelpServiceCalcSpecUseCase;
    private HomeHelpServiceCalcSpecRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase $lookupHomeHelpServiceCalcSpecUseCase
     * @param \Domain\Office\HomeHelpServiceCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupHomeHelpServiceCalcSpecUseCase $lookupHomeHelpServiceCalcSpecUseCase,
        HomeHelpServiceCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupHomeHelpServiceCalcSpecUseCase = $lookupHomeHelpServiceCalcSpecUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $id, array $values): HomeHelpServiceCalcSpec
    {
        $x = $this->transaction->run(function () use ($context, $officeId, $id, $values): HomeHelpServiceCalcSpec {
            $entity = $this->lookupHomeHelpServiceCalcSpecUseCase->handle($context, [Permission::updateInternalOffices()], $officeId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("HomeHelpServiceCalcSpec({$id}) not found");
                });
            return $this->repository->store($entity->copy($values + [
                'version' => $entity->version + 1,
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '事業所算定情報（障害・居宅介護）が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
