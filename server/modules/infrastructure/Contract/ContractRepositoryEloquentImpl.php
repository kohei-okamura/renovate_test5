<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Contract;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Contract\Contract as DomainContract;
use Domain\Contract\ContractRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Contract\ContractRepository} Eloquent 実装.
 */
final class ContractRepositoryEloquentImpl extends EloquentRepository implements ContractRepository
{
    /**
     * {@inheritdoc}
     */
    public function lookupByUserId(int ...$ids): Map
    {
        $xs = Contract::whereIn('user_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (Contract $x): DomainContract => $x->toDomain())
            ->groupBy(fn (DomainContract $x): int => $x->userId);
    }

    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $xs = Contract::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Contract $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainContract
    {
        assert($entity instanceof DomainContract);

        $contract = Contract::fromDomain($entity);
        $contract->save();

        $attr = ContractAttr::fromDomain($entity);
        $contract->attr()->save($attr);

        $dwsPeriods = Arrays::generate(function () use ($entity, $attr): Generator {
            foreach ($entity->dwsPeriods as $key => $value) {
                $code = DwsServiceDivisionCode::from((string)$key);
                yield ContractAttrDwsPeriod::fromDomain($value, $attr->id, $code);
            }
        });
        $attr->dwsPeriods()->saveMany($dwsPeriods);

        return $contract->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        ContractAttr::whereIn('contract_id', $ids)->delete();
        Contract::destroy($ids);
    }
}
