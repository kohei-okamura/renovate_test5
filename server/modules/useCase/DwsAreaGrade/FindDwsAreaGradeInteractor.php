<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGradeFinder;
use UseCase\FindInteractorFeature;

/**
 * 障害福祉サービス地域区分検索ユースケース実装.
 */
final class FindDwsAreaGradeInteractor implements FindDwsAreaGradeUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFinder $finder
     */
    public function __construct(DwsAreaGradeFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}
