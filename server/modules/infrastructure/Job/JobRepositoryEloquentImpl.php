<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Job;

use Domain\Job\Job as DomainJob;
use Domain\Job\JobRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Job Repository Eloquent Implementation.
 */
final class JobRepositoryEloquentImpl extends EloquentRepository implements JobRepository
{
    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = Job::query()->where('token', $token)->first();
        return Option::from($x)->map(fn (Job $job) => $job->toDomain());
    }

    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $x = Job::findMany($ids);
        return Seq::fromArray($x)->map(fn (Job $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainJob
    {
        assert($entity instanceof DomainJob);
        $job = Job::fromDomain($entity);
        $job->save();
        return $job->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        foreach ($ids as $id) {
            $job = Job::find($id);
            $job->delete();
        }
        Job::destroy($ids);
    }
}
