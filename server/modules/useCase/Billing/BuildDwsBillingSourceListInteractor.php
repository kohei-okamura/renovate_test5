<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingSource;
use Domain\Context\Context;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * 障害福祉サービス：請求算定元データ一覧組み立てユースケース実装.
 */
final class BuildDwsBillingSourceListInteractor implements BuildDwsBillingSourceListUseCase
{
    private IdentifyDwsCertificationUseCase $identifyCertificationUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingSourceListInteractor} constructor.
     *
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyCertificationUseCase
     */
    public function __construct(IdentifyDwsCertificationUseCase $identifyCertificationUseCase)
    {
        $this->identifyCertificationUseCase = $identifyCertificationUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $provisionReports, Seq $previousProvisionReports): Seq
    {
        return $provisionReports
            ->map(fn (DwsProvisionReport $x): DwsBillingSource => DwsBillingSource::create([
                'certification' => $this->identifyCertification($context, $x),
                'provisionReport' => $x,
                'previousProvisionReport' => $previousProvisionReports
                    ->find(fn (DwsProvisionReport $y): bool => $y->userId === $x->userId),
            ]))
            ->filter(function (DwsBillingSource $x) {
                // 自費サービス以外の実績が存在している、もしくは自社事業所で上限管理を行うものが対象
                return Seq::fromArray($x->provisionReport->results)
                    ->filter(fn (DwsProvisionReportItem $y) => !$y->isOwnExpense())
                    ->nonEmpty()
                    || $x->certification->copayCoordination->copayCoordinationType === CopayCoordinationType::internal();
            })
            ->computed();
    }

    /**
     * 予実に対応する受給者証を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyCertification(Context $context, DwsProvisionReport $provisionReport): DwsCertification
    {
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn;
        return $this->identifyCertificationUseCase
            ->handle($context, $userId, $providedIn)
            ->getOrElse(function () use ($userId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new NotFoundException("DwsCertification for User({$userId}) at {$date} not found");
            });
    }
}
