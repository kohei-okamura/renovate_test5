<?php
/**
 * Copyright © 2020 EUSTYLE ABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\JobRepository;
use ScalikePHP\Seq;

final class LookupJobInteractor implements LookupJobUseCase
{
    private JobRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Job\JobRepository $repository
     */
    public function __construct(JobRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(fn (Job $x): bool => $x->organizationId === $context->organization->id)
            ? $xs
            : Seq::emptySeq();
    }
}
