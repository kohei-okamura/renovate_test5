<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingSource;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingSourceListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingSourceListInteractor} のテスト.
 */
final class BuildDwsBillingSourceListInteractorTest extends Test
{
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use IdentifyDwsCertificationUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private BuildDwsBillingSourceListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->with($self->context, $self->users[0]->id, $self->reports[0]->providedIn)
                ->andReturn(Option::from($self->dwsCertifications[0]))
                ->byDefault();

            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->with($self->context, $self->users[1]->id, $self->reports[1]->providedIn)
                ->andReturn(Option::from($self->dwsCertifications[1]))
                ->byDefault();

            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->with($self->context, $self->users[2]->id, $self->reports[2]->providedIn)
                ->andReturn(Option::from($self->dwsCertifications[2]))
                ->byDefault();

            $self->interactor = app(BuildDwsBillingSourceListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingSource', function (): void {
            $actual = $this->interactor->handle($this->context, $this->reports, $this->previousReports);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof DwsBillingSource);
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return a Seq includes own expense because of manage the upper limit', function (): void {
            $certification = $this->createDwsCertification(CopayCoordinationType::internal());
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $reports = $this->createDwsProvisionReports();
            $actual = $this->interactor->handle($this->context, $reports, $this->previousReports);

            $this->assertEquals(4, $actual->count());
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return a Seq does not include own expense because of do not manage the upper limit', function (): void {
            $certification = $this->createDwsCertification(CopayCoordinationType::external());
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $reports = $this->createDwsProvisionReports();
            $actual = $this->interactor->handle($this->context, $reports, $this->previousReports);

            $this->assertEquals(3, $actual->count());
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return a Seq does not include dws service for plan because of do not manage the upper limit', function (): void {
            $certification = $this->createDwsCertification(CopayCoordinationType::external());
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $reports = $this->createDwsProvisionReports([
                'plans' => Seq::fromArray($this->reports->head()->plans)->map(function (DwsProvisionReportItem $x) {
                    return $x->copy([
                        'category' => DwsProjectServiceCategory::physicalCare(),
                        'ownExpenseProgramId' => 1,
                    ]);
                }),
            ]);
            $actual = $this->interactor->handle($this->context, $reports, $this->previousReports);

            $this->assertEquals(3, $actual->count());
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('identify dws certification for each provision report', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->users[0]->id, $this->reports[0]->providedIn)
                ->andReturn(Option::from($this->dwsCertifications[0]));

            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->users[1]->id, $this->reports[1]->providedIn)
                ->andReturn(Option::from($this->dwsCertifications[1]));

            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->users[2]->id, $this->reports[2]->providedIn)
                ->andReturn(Option::from($this->dwsCertifications[2]));

            $this->interactor->handle($this->context, $this->reports, $this->previousReports);
        });
        $this->should('throw a NotFoundException when dws certification cannot identified', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->users[1]->id, $this->reports[1]->providedIn)
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->reports, $this->previousReports);
            });
        });
        $this->should('be as expected when the previousReports is empty', function (): void {
            $previousReports = Seq::empty();

            $actual = $this->interactor->handle($this->context, $this->reports, $previousReports);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用の受給者証を作る
     *
     * @param \Domain\DwsCertification\CopayCoordinationType $copayCoordinationType
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function createDwsCertification(CopayCoordinationType $copayCoordinationType): DwsCertification
    {
        /** @var \Domain\DwsCertification\DwsCertification $certification */
        $certification = $this->dwsCertifications()->head();
        return $certification->copy([
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => $copayCoordinationType,
                'officeId' => $certification->copayCoordination->officeId,
            ]),
        ]);
    }

    /**
     * テスト用の障害福祉サービス：予実配列を作る
     *
     * @param array $overwrites
     * @return \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Seq
     */
    private function createDwsProvisionReports(array $overwrites = []): Seq
    {
        /** @var \Domain\ProvisionReport\DwsProvisionReport $report */
        $report = $this->reports()->head();
        $copiedReport = $report->copy($overwrites + [
            'id' => $report->id + 3,
            'plans' => Seq::fromArray($report->plans)->map(function (DwsProvisionReportItem $x) {
                return $x->copy([
                    'category' => DwsProjectServiceCategory::ownExpense(),
                    'ownExpenseProgramId' => 1,
                ]);
            }),
            'results' => [],
        ]);
        return $this->reports
            ->map(fn (DwsProvisionReport $x) => $x->copy())
            ->append([$copiedReport]);
    }
}
