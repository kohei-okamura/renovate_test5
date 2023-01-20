<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\Calling as DomainCalling;
use Domain\Calling\CallingRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * CallingRepository eloquent implementation.
 */
final class CallingRepositoryEloquentImpl extends EloquentRepository implements CallingRepository
{
    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = Calling::query()->where('token', $token)->first();
        return Option::from($x)->map(fn (Calling $staffRememberToken) => $staffRememberToken->toDomain());
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Calling::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Calling $x): DomainCalling => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainCalling
    {
        assert($entity instanceof DomainCalling);
        $calling = Calling::fromDomain($entity)->saveIfNotExists();
        $this->storeShifts($entity, $calling);
        return $calling->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        CallingResponse::destroy($ids);
        CallingLog::destroy($ids);
        Calling::destroy($ids);
    }

    /**
     * 勤務シフト情報をデータベースに保管する.
     *
     * @param \Domain\Calling\Calling $entity
     * @param \Infrastructure\Calling\Calling $calling
     * @return void
     */
    private function storeShifts(DomainCalling $entity, Calling $calling): void
    {
        $calling->shifts()->sync($entity->shiftIds);
    }
}
