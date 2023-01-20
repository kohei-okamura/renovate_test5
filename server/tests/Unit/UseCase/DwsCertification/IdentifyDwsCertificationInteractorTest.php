<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsCertification;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsCertificationFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\DwsCertification\IdentifyDwsCertificationInteractor;

/**
 * {@link \UseCase\DwsCertification\IdentifyDwsCertificationInteractor} Test.
 */
final class IdentifyDwsCertificationInteractorTest extends Test
{
    use ContextMixin;
    use DwsCertificationFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private IdentifyDwsCertificationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (IdentifyDwsCertificationInteractorTest $self): void {
            $self->interactor = app(IdentifyDwsCertificationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('identify dwsCertification using Finder', function (): void {
            $userId = $this->examples->users[0]->id;
            $targetDate = Carbon::now();
            $dwsCertification = $this->examples->dwsCertifications[0];
            $expectedFilter = [
                'userId' => $userId,
                'status' => DwsCertificationStatus::approved(),
                'effectivatedBefore' => $targetDate,
            ];
            $expectedPagination = [
                'sortBy' => 'effectivatedOn',
                'desc' => true,
                'itemsPerPage' => 1,
            ];
            $certificationFind = FinderResult::from(Seq::from($dwsCertification), Pagination::create());
            $this->dwsCertificationFinder
                ->expects('find')
                ->with(equalTo($expectedFilter), equalTo($expectedPagination))
                ->andReturn($certificationFind);

            $actual = $this->interactor->handle($this->context, $userId, $targetDate);

            $this->assertNotEmpty($actual);
            $this->assertSame($dwsCertification, $actual->head());
        });
    }
}
