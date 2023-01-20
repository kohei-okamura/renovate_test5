<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGradeFinder;
use UseCase\FindInteractorFeature;

/**
 * 介保地域区分検索ユースケース実装.
 */
final class FindLtcsAreaGradeInteractor implements FindLtcsAreaGradeUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFinder $finder
     */
    public function __construct(LtcsAreaGradeFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}
