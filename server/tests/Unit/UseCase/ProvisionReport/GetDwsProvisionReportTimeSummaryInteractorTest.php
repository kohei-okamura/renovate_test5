<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Carbon;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsHomeHelpServiceChunkListUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use Tests\Unit\UseCase\Billing\DwsBillingTestSupport;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListInteractor;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryInteractor;

/**
 * {@link \UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryInteractor} のテスト.
 */
final class GetDwsProvisionReportTimeSummaryInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateDwsHomeHelpServiceChunkListUseCaseMixin;
    use CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
    use DwsBillingTestSupport;
    use ExamplesConsumer;
    use IdentifyDwsCertificationUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private GetDwsProvisionReportTimeSummaryInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();

            // 下記ユースケースは実装をテストに用いる
            $dependencies = [
                BuildDwsHomeHelpServiceUnitListUseCase::class => BuildDwsHomeHelpServiceUnitListInteractor::class,
                BuildDwsVisitingCareForPwsdUnitListUseCase::class => BuildDwsVisitingCareForPwsdUnitListInteractor::class,
            ];
            foreach ($dependencies as $abstract => $concrete) {
                app()->bind($abstract, $concrete);
            }
        });
        self::beforeEachSpec(function (self $self): void {
            $self->createDwsHomeHelpServiceChunkListUseCase
                ->allows('handle')
                ->andReturn($self->dwsHomeHelpServiceChunks)
                ->byDefault();

            $self->createDwsVisitingCareForPwsdChunkListUseCase
                ->allows('handle')
                ->andReturn($self->dwsVisitingCareForPwsdChunks)
                ->byDefault();

            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsCertifications[0]))
                ->byDefault();

            $self->interactor = app(GetDwsProvisionReportTimeSummaryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return an assoc contains plan/result time summary items', function (): void {
            $assert = $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    Carbon::create(2021, 3),
                    Seq::fromArray($this->examples->dwsProvisionReports[0]->plans),
                    Seq::fromArray($this->examples->dwsProvisionReports[0]->results)
                );
            $this->assertMatchesJsonSnapshot($assert);
        });
    }
}
