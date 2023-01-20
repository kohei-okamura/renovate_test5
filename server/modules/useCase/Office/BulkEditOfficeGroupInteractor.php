<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\OfficeGroupRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所グループ一括編集実装.
 */
final class BulkEditOfficeGroupInteractor implements BulkEditOfficeGroupUseCase
{
    use Logging;

    private LookupOfficeGroupUseCase $lookupUseCase;
    private OfficeGroupRepository $officeGroupRepository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\Office\LookupOfficeGroupUseCase $lookupUseCase
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LookupOfficeGroupUseCase $lookupUseCase,
        OfficeGroupRepository $officeGroupRepository,
        TransactionManagerFactory $factory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->officeGroupRepository = $officeGroupRepository;
        $this->transaction = $factory->factory($officeGroupRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $requestList): void
    {
        if (empty($requestList)) {
            throw new NotFoundException('input data not found');
        }

        $this->transaction->run(function () use ($context, $requestList): void {
            foreach ($requestList as $request) {
                $entity = $this->lookupUseCase->handle($context, $request['id'])->headOption()->getOrElse(
                    function () use ($request): void {
                        throw new NotFoundException("OfficeGroup({$request['id']}) not found");
                    }
                );
                $values = [
                    'parentOfficeGroupId' => $request['parentOfficeGroupId'],
                    'sortOrder' => $request['sortOrder'],
                    'organizationId' => $context->organization->id,
                    'updatedAt' => Carbon::now(),
                ];
                $this->officeGroupRepository->store($entity->copy($values));
            }
        });
        $this->logger()->info(
            '事業所グループが一括更新されました',
            // TODO DEV-1577 IDの複数出力方法は検討中なので暫定的に空文字
            ['id' => ''] + $context->logContext()
        );
    }
}
