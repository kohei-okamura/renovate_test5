<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Billing\DwsVisitingCareForPwsdUnit;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス実績単位（重度訪問介護）一覧組み立てユースケース実装.
 */
final class BuildDwsVisitingCareForPwsdUnitListInteractor implements BuildDwsVisitingCareForPwsdUnitListUseCase
{
    private CreateDwsVisitingCareForPwsdChunkListUseCase $createChunkListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListInteractor} constructor.
     *
     * @param \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase $createChunkListUseCase
     */
    public function __construct(CreateDwsVisitingCareForPwsdChunkListUseCase $createChunkListUseCase)
    {
        $this->createChunkListUseCase = $createChunkListUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        bool $forPlan
    ): Seq {
        return $this->createChunkListUseCase
            ->handle($context, $certification, $report, $forPlan)
            ->sortBy(fn (Chunk $x): Carbon => $x->range->start)
            ->flatMap(fn (Chunk $x): iterable => DwsVisitingCareForPwsdUnit::fromVisitingCareForPwsdChunk($x))
            ->computed();
    }
}
