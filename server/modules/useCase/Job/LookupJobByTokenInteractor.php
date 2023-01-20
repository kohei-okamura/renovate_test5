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
use Lib\Exceptions\UnauthorizedException;
use ScalikePHP\Seq;
use UseCase\Staff\LookupStaffUseCase;

/**
 * トークンによるジョブ情報取得ユースケース実装.
 */
final class LookupJobByTokenInteractor implements LookupJobByTokenUseCase
{
    private LookupStaffUseCase $lookupStaffUseCase;
    private JobRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \Domain\Job\JobRepository $repository
     */
    public function __construct(LookupStaffUseCase $lookupStaffUseCase, JobRepository $repository)
    {
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): Seq
    {
        $x = $this->repository->lookupOptionByToken($token);
        /** @var \Domain\Staff\Staff $staff */
        $staff = $context->staff->getOrElse(function (): void {
            throw new UnauthorizedException();
        });
        return $x->filter(fn (Job $job): bool => $staff->id === $job->staffId)->nonEmpty()
            ? $x->toSeq()
            : Seq::emptySeq();
    }
}
