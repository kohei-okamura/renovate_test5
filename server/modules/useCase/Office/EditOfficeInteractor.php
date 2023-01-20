<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Support\Arr;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業所編集実装.
 */
final class EditOfficeInteractor implements EditOfficeUseCase
{
    use Logging;

    private LookupOfficeUseCase $lookupUseCase;
    private OfficeRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\LookupOfficeUseCase $lookupUseCase
     * @param \Domain\Office\OfficeRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupOfficeUseCase $lookupUseCase,
        OfficeRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values, callable $f): Office
    {
        $x = $this->transaction->run(function () use ($context, $id, $values, $f): Office {
            $entity = $this->lookupUseCase
                ->handle($context, [Permission::updateInternalOffices(), Permission::updateExternalOffices()], $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("Office({$id}) not found");
                });

            if (Arr::exists($values, 'addr') && !$entity->addr->equals($values['addr'])) {
                $storedEntity = $this->repository->store(
                    $entity->copy(
                        $values + [
                            'location' => Location::create(['lat' => 0, 'lng' => 0]),
                            'version' => $entity->version + 1,
                            'updatedAt' => Carbon::now(),
                        ]
                    )
                );
                $f($storedEntity);
                return $storedEntity;
            }

            return $this->repository->store(
                $entity->copy(
                    $values + [
                        'version' => $entity->version + 1,
                        'updatedAt' => Carbon::now(),
                    ]
                )
            );
        });
        $this->logger()->info(
            '事業所が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
