<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\DwsAreaGrade\DwsAreaGradeRepository;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス地域区分ユースケース実装.
 */
final class LookupDwsAreaGradeInteractor implements LookupDwsAreaGradeUseCase
{
    private DwsAreaGradeRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeRepository $repository
     */
    public function __construct(DwsAreaGradeRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): Seq
    {
        return $this->repository->lookup(...$ids);
    }
}
