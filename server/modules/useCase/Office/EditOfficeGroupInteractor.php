<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeGroupRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所グループ編集実装.
 */
final class EditOfficeGroupInteractor implements EditOfficeGroupUseCase
{
    use Logging;

    private FindOfficeGroupUseCase $findUseCase;
    private LookupOfficeGroupUseCase $lookupUseCase;
    private OfficeGroupRepository $officeGroupRepository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Office\FindOfficeGroupUseCase $findUseCase
     * @param \UseCase\Office\LookupOfficeGroupUseCase $lookupUseCase
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        FindOfficeGroupUseCase $findUseCase,
        LookupOfficeGroupUseCase $lookupUseCase,
        OfficeGroupRepository $officeGroupRepository,
        TransactionManagerFactory $factory
    ) {
        $this->findUseCase = $findUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->officeGroupRepository = $officeGroupRepository;
        $this->transaction = $factory->factory($officeGroupRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): FinderResult
    {
        $entity = $this->lookupUseCase->handle($context, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("OfficeGroup({$id}) not found");
        });
        $x = $this->transaction->run(fn (): OfficeGroup => $this->officeGroupRepository->store($entity->copy(
            $values + [
                'organizationId' => $context->organization->id,
                'updatedAt' => Carbon::now(),
            ]
        )));
        $this->logger()->info(
            '事業所グループが更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        // フロントが一覧を求めているので、一覧を返却する.
        return $this->findUseCase->handle($context, [], ['all' => true]);
    }
}
