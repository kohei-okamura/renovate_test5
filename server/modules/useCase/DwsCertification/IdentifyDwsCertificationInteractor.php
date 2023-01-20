<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertificationFinder;
use Domain\DwsCertification\DwsCertificationStatus;
use ScalikePHP\Option;

/**
 * 障害福祉サービス受給者証特定ユースケース実装.
 */
final class IdentifyDwsCertificationInteractor implements IdentifyDwsCertificationUseCase
{
    private DwsCertificationFinder $finder;

    /**
     * {@link \UseCase\DwsCertification\IdentifyDwsCertificationInteractor} constructor.
     *
     * @param \Domain\DwsCertification\DwsCertificationFinder $finder
     */
    public function __construct(DwsCertificationFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, Carbon $targetDate): Option
    {
        $filterParams = [
            'userId' => $userId,
            'status' => DwsCertificationStatus::approved(),
            'effectivatedBefore' => $targetDate,
        ];
        $paginationParams = [
            'sortBy' => 'effectivatedOn',
            'desc' => true,
            'itemsPerPage' => 1,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}
