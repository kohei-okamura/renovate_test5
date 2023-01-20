<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsGrantedServiceCode;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\CreateDwsHomeHelpServiceChunkListUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingServiceReportListByIdInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListInteractor;
use UseCase\Billing\BuildDwsHomeHelpServiceUnitListUseCase;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListInteractor;
use UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceReportListByIdInteractor} のテスト.
 */
final class BuildDwsBillingServiceReportListByIdInteractorTest extends Test
{
    use CarbonMixin;
    use CreateDwsHomeHelpServiceChunkListUseCaseMixin;
    use CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use ExamplesConsumer;
    use IdentifyDwsCertificationUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Option $previousReport;
    private BuildDwsBillingServiceReportListByIdInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();

            // 今回のテストでは以下のユースケースに処理を切り出すという対応をしたためテストでは以前のものと変更がないことが確認したい。
            // Mockを使うとMockの戻り値によってしまうため定義の手間がかかる & 以前のものと変わっていてもわからないという問題がある。
            // そのため今回のテストでは共通化して切り出したユースケースは実際の実装を使うようにする。
            $dependencies = [
                BuildDwsHomeHelpServiceUnitListUseCase::class => BuildDwsHomeHelpServiceUnitListInteractor::class,
                BuildDwsVisitingCareForPwsdUnitListUseCase::class => BuildDwsVisitingCareForPwsdUnitListInteractor::class,
            ];
            foreach ($dependencies as $abstract => $concrete) {
                app()->bind($abstract, $concrete);
            }
        });

        self::beforeEachSpec(function (self $self): void {
            app()->bind(DwsGrantedServiceCode::class, function () {
                $dwsGrantedServiceCode = Mockery::mock(DwsGrantedServiceCode::class)->makePartial();
                $dwsGrantedServiceCode->allows('context')->andReturn($this->context)->byDefault();
                return $dwsGrantedServiceCode;
            });
            $self->createDwsVisitingCareForPwsdChunkListUseCase
                ->allows('handle')
                ->andReturn($self->dwsVisitingCareForPwsdChunks)
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsCertifications[0]))
                ->byDefault();
            $self->createDwsHomeHelpServiceChunkListUseCase
                ->allows('handle')
                ->andReturn($self->dwsHomeHelpServiceChunks)
                ->byDefault();
            $self->previousReport = $self->previousReports->headOption();

            $self->interactor = app(BuildDwsBillingServiceReportListByIdInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('identify DwsCertification', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->report->userId, $this->report->providedIn)
                ->andReturn(Option::some($this->examples->dwsCertifications[0]));

            $this->interactor->handle(
                $this->context,
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user
            );
        });

        $this->should('create DwsVisitingCareForPwsdChunks for plan', function (): void {
            $this->createDwsVisitingCareForPwsdChunkListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsCertifications[0], $this->report, true)
                ->andReturn($this->dwsVisitingCareForPwsdChunks);

            $this->interactor->handle(
                $this->context,
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user
            );
        });

        $this->should('create DwsVisitingCareForPwsdChunks for result', function (): void {
            $this->createDwsVisitingCareForPwsdChunkListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsCertifications[0], $this->report, false)
                ->andReturn($this->dwsVisitingCareForPwsdChunks);

            $this->interactor->handle(
                $this->context,
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user
            );
        });

        $this->should('create DwsHomeHelpServiceChunks for plan', function (): void {
            $this->createDwsHomeHelpServiceChunkListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, true)
                ->andReturn($this->dwsHomeHelpServiceChunks);

            $this->interactor->handle(
                $this->context,
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user
            );
        });

        $this->should('create DwsHomeHelpServiceChunks for result', function (): void {
            $this->createDwsHomeHelpServiceChunkListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, false)
                ->andReturn($this->dwsHomeHelpServiceChunks);

            $this->interactor->handle(
                $this->context,
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user
            );
        });

        $this->should(
            'return a ServiceReports',
            function (...$args): void {
                $actual = $this->interactor->handle($this->context, ...$args);

                // `assertMatchesModelSnapshot` だと一部データが正しく比較できないため
                // `assertMatchesJsonSnapshot` を用いてテストする
                $this->assertMatchesJsonSnapshot($actual->toArray());
            },
            ['examples' => $this->generateExample()]
        );

        $this->should(
            'return a Empty Seq for zero DwsVisitingCareForPwsdResult',
            function (): void {
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, false)
                    ->andReturn(Seq::empty());
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, true)
                    ->andReturn($this->dwsVisitingCareForPwsdChunks);
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->allows('handle')
                    ->andReturn(Seq::empty());

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->bundle->dwsBillingId,
                    $this->bundle->id,
                    $this->report,
                    $this->previousReport,
                    $this->user
                );

                $this->assertInstanceOf(Seq::class, $actual);
                $this->assertEmpty($actual);
            }
        );

        $this->should(
            'return a Empty Seq for zero DwsHomeHelpServiceResult',
            function (): void {
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, false)
                    ->andReturn(Seq::empty());
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, true)
                    ->andReturn($this->dwsHomeHelpServiceChunks);
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->allows('handle')
                    ->andReturn(Seq::empty());

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->bundle->dwsBillingId,
                    $this->bundle->id,
                    $this->report,
                    $this->previousReport,
                    $this->user
                );

                $this->assertInstanceOf(Seq::class, $actual);
                $this->assertEmpty($actual);
            }
        );
        $this->should(
            'return Preview Seq for zero DwsVisitingCareForPwsdResult',
            function (): void {
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, false)
                    ->andReturn(Seq::empty());
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, true)
                    ->andReturn($this->dwsVisitingCareForPwsdChunks);
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->allows('handle')
                    ->andReturn(Seq::empty());

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->bundle->dwsBillingId,
                    $this->bundle->id,
                    $this->report,
                    $this->previousReport,
                    $this->user,
                    true
                );

                $this->assertMatchesModelSnapshot($actual);
            }
        );

        $this->should(
            'return Preview Seq for zero DwsHomeHelpServiceResult',
            function (): void {
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, false)
                    ->andReturn(Seq::empty());
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsCertifications[0], $this->report, $this->previousReport, true)
                    ->andReturn($this->dwsHomeHelpServiceChunks);
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->allows('handle')
                    ->andReturn(Seq::empty());

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->bundle->dwsBillingId,
                    $this->bundle->id,
                    $this->report,
                    $this->previousReport,
                    $this->user,
                    true
                );

                $this->assertMatchesModelSnapshot($actual);
            }
        );
    }

    /**
     * テストパターンを生成する.
     *
     * @return array
     */
    private function generateExample(): array
    {
        return [
            'general case' => [
                $this->bundle->dwsBillingId,
                $this->bundle->id,
                $this->report,
                $this->previousReport,
                $this->user,
            ],
        ];
    }
}
