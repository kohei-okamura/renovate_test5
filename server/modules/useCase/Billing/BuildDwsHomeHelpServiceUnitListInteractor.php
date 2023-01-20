<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk as Chunk;
use Domain\Billing\DwsHomeHelpServiceUnit;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス実績単位（居宅介護）一覧組み立てユースケース実装.
 */
final class BuildDwsHomeHelpServiceUnitListInteractor implements BuildDwsHomeHelpServiceUnitListUseCase
{
    private CreateDwsHomeHelpServiceChunkListUseCase $createChunkListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsHomeHelpServiceUnitListInteractor} constructor.
     *
     * @param \UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase $createChunkListUseCase
     */
    public function __construct(CreateDwsHomeHelpServiceChunkListUseCase $createChunkListUseCase)
    {
        $this->createChunkListUseCase = $createChunkListUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        Option $previousReport,
        bool $forPlan
    ): Seq {
        $providedIn = $report->providedIn->startOfMonth();
        return $this->createChunkListUseCase
            ->handle($context, $certification, $report, $previousReport, $forPlan)
            // サービスの終了がサービス提供月の Chunk のみサービス提供票に載せる
            ->filterNot(fn (Chunk $x): bool => $x->range->end <= $providedIn)
            ->sortBy(fn (Chunk $x): Carbon => $x->range->start)
            ->flatMap(fn (Chunk $x): iterable => DwsHomeHelpServiceUnit::fromHomeHelpServiceChunk($x, $providedIn))
            ->computed();
    }
}
