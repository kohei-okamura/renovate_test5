<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingSource;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Exception;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsHomeHelpServiceServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsVisitingCareForPwsdServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
use Tests\Unit\Mixins\IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingServiceDetailListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceDetailListInteractor} のテスト.
 */
final class BuildDwsBillingServiceDetailListInteractorTest extends Test
{
    use BuildDwsHomeHelpServiceServiceDetailListUseCaseMixin;
    use BuildDwsVisitingCareForPwsdServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
    use IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
    use IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
    use IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
    use LookupUserUseCaseMixin;
    use IdentifyUserDwsCalcSpecUseCaseMixin;
    use MatchesSnapshots;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private Option $homeHelpServiceCalcSpecOption;
    private Option $visitingCareForPwsdCalcSpecOption;
    private Option $userDwsCalcSpecsOption;
    private array $examples;

    private BuildDwsBillingServiceDetailListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->examples = [
                ...$self->sources->map(fn (DwsBillingSource $x): array => [
                    $x->provisionReport,
                    $x->certification,
                    $x->previousProvisionReport,
                ]),
            ];
            $self->homeHelpServiceCalcSpecOption = Option::from($self->homeHelpServiceCalcSpec);
            $self->visitingCareForPwsdCalcSpecOption = Option::from($self->visitingCareForPwsdCalcSpec);
            $self->userDwsCalcSpecsOption = Option::from($self->examples()->userDwsCalcSpecs[0]);
        });
        self::beforeEachSpec(function (self $self): void {
            for ($i = 0; $i < 3; ++$i) {
                $self->buildDwsHomeHelpServiceServiceDetailListUseCase
                    ->allows('handle')
                    ->with(
                        $self->context,
                        $self->providedIn,
                        equalTo($self->homeHelpServiceCalcSpecOption),
                        equalTo($self->userDwsCalcSpecsOption),
                        $self->dwsCertifications[$i],
                        $self->reports[$i],
                        equalTo(Option::some($self->previousReports[$i]))
                    )
                    ->andReturnUsing(fn (): Seq => $self->homeHelpServiceServiceDetailList($i))
                    ->byDefault();

                $self->buildDwsVisitingCareForPwsdServiceDetailListUseCase
                    ->allows('handle')
                    ->with(
                        $self->context,
                        $self->providedIn,
                        equalTo($self->visitingCareForPwsdCalcSpecOption),
                        equalTo($self->userDwsCalcSpecsOption),
                        $self->dwsCertifications[$i],
                        $self->reports[$i]
                    )
                    ->andReturnUsing(fn (): Seq => $self->visitingCareForPwsdServiceDetailList($i))
                    ->byDefault();
            }

            $self->identifyHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->homeHelpServiceCalcSpec))
                ->byDefault();

            $self->identifyVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->visitingCareForPwsdCalcSpec))
                ->byDefault();

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples()->users[0], $self->examples()->users[1], $self->examples()->users[2]))
                ->byDefault();

            $self->identifyUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples()->userDwsCalcSpecs[0]))
                ->byDefault();

            $self->interactor = app(BuildDwsBillingServiceDetailListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('identify HomeHelpServiceCalcSpec', function (): void {
            $this->identifyHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->office, $this->providedIn)
                ->andReturn(Option::some($this->homeHelpServiceCalcSpec));

            $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
        });
        $this->should(
            'build DwsBillingServiceDetails of home-help-service for each DwsProvisionReport',
            function (DwsProvisionReport $report, DwsCertification $certification, Option $previousReport): void {
                $this->buildDwsHomeHelpServiceServiceDetailListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->providedIn,
                        equalTo($this->homeHelpServiceCalcSpecOption),
                        equalTo($this->userDwsCalcSpecsOption),
                        $certification,
                        $report,
                        $previousReport
                    )
                    ->andReturn(Seq::empty());

                $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
            },
            ['examples' => $this->examples]
        );
        $this->should(
            'throw a Exception when BuildDwsHomeHelpServiceServiceDetailListUseCase throws it',
            function (DwsProvisionReport $report, DwsCertification $certification, Option $previousReport): void {
                $this->buildDwsHomeHelpServiceServiceDetailListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->providedIn,
                        equalTo($this->homeHelpServiceCalcSpecOption),
                        equalTo($this->userDwsCalcSpecsOption),
                        $certification,
                        $report,
                        $previousReport
                    )
                    ->andThrow(new Exception('some error'));

                $this->assertThrows(Exception::class, function (): void {
                    $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
                });
            },
            ['examples' => $this->examples]
        );
        $this->should('identify VisitingCareForPwsdCalcSpec', function (): void {
            $this->identifyVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->office, $this->providedIn)
                ->andReturn(Option::some($this->visitingCareForPwsdCalcSpec));

            $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
        });
        $this->should(
            'build DwsBillingServiceDetails of visiting-care-for-pwsd for each DwsProvisionReport',
            function (DwsProvisionReport $report, DwsCertification $certification): void {
                $this->buildDwsVisitingCareForPwsdServiceDetailListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->providedIn,
                        equalTo($this->visitingCareForPwsdCalcSpecOption),
                        equalTo($this->userDwsCalcSpecsOption),
                        $certification,
                        $report
                    )
                    ->andReturn(Seq::empty());

                $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
            },
            ['examples' => $this->examples]
        );
        $this->should(
            'throw a Exception when BuildDwsVisitingCareForPwsdServiceDetailListUseCase throws it',
            function (DwsProvisionReport $report, DwsCertification $certification): void {
                $this->buildDwsVisitingCareForPwsdServiceDetailListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->providedIn,
                        equalTo($this->visitingCareForPwsdCalcSpecOption),
                        equalTo($this->userDwsCalcSpecsOption),
                        $certification,
                        $report
                    )
                    ->andThrow(new Exception('some error'));

                $this->assertThrows(Exception::class, function (): void {
                    $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
                });
            },
            ['examples' => $this->examples]
        );
        $this->should(
            'return Map that contains Seq of DwsBillingServiceDetails grouped by cityCode',
            function (): void {
                $actual = $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
                $this->assertMatchesModelSnapshot($actual);
            }
        );

        $this->should('use LookupUserUseCase', function (): void {
            $userIds = $this->sources->map(function (DwsBillingSource $x): int {
                return $x->provisionReport->userId;
            })->toArray();
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), ...$userIds)
                ->andReturn(Seq::from($this->examples()->users[0], $this->examples()->users[1], $this->examples()->users[2]))
                ->twice();

            $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
        });

        $this->should('use IdentifyUserDwsCalcSpecUseCase', function (): void {
            $expect = $this->providedIn->lastOfMonth();
            $this->identifyUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples()->users[0], Mockery::capture($actual1))
                ->andReturn(Option::from($this->examples()->userDwsCalcSpecs[0]))
                ->twice();
            $this->identifyUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples()->users[1], Mockery::capture($actual2))
                ->andReturn(Option::from($this->examples()->userDwsCalcSpecs[0]))
                ->twice();
            $this->identifyUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples()->users[2], Mockery::capture($actual3))
                ->andReturn(Option::from($this->examples()->userDwsCalcSpecs[0]))
                ->twice();

            $this->interactor->handle($this->context, $this->office, $this->providedIn, $this->sources);
            $this->assertTrue(Seq::from($actual1, $actual2, $actual3)->forAll(fn (Carbon $x) => $x->eq($expect)));
        });
    }

    /**
     * テスト用の障害福祉サービス：請求：サービス詳細の一覧を生成する（居宅介護）.
     *
     * @param int $index
     * @return \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    private function homeHelpServiceServiceDetailList(int $index): Seq
    {
        switch ($index) {
            case 0: // 1つ目の市町村：重度訪問介護の例
                return Seq::empty();
            case 1: // 1つ目の市町村：居宅介護の例
                $endOfMonth = $this->providedIn->endOfMonth()->startOfDay();
                return Seq::from(
                    $this->serviceDetail($this->users[1]->id, $this->providedIn->day(3), '111147', DwsServiceCodeCategory::physicalCare(), 1139),
                    $this->serviceDetail($this->users[1]->id, $this->providedIn->day(7), '117667', DwsServiceCodeCategory::housework(), 438),
                    $this->serviceDetail($this->users[1]->id, $endOfMonth, '116010', DwsServiceCodeCategory::specifiedOfficeAddition1(), 315, true),
                );
            case 2: // 2つ目の市町村：重度訪問介護の例
                return Seq::empty();
        }
    }

    /**
     * テスト用の障害福祉サービス：請求：サービス詳細の一覧を生成する（重度訪問介護）.
     *
     * @param int $index
     * @return \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    private function visitingCareForPwsdServiceDetailList(int $index): Seq
    {
        switch ($index) {
            case 0: // 1つ目の市町村：重度訪問介護の例
                return Seq::from(
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124171', DwsServiceCodeCategory::visitingCareForPwsd1(), 318),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124181', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124391', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124401', DwsServiceCodeCategory::visitingCareForPwsd1(), 158),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124411', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124421', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124431', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124121', DwsServiceCodeCategory::visitingCareForPwsd1(), 147, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '122121', DwsServiceCodeCategory::visitingCareForPwsd1(), 123, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121131', DwsServiceCodeCategory::visitingCareForPwsd1(), 98, false, 8),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121141', DwsServiceCodeCategory::visitingCareForPwsd1(), 92, false, 8),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '121151', DwsServiceCodeCategory::visitingCareForPwsd1(), 99, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '123151', DwsServiceCodeCategory::visitingCareForPwsd1(), 124, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '123161', DwsServiceCodeCategory::visitingCareForPwsd1(), 115, false, 4),
                    $this->serviceDetail($this->users[0]->id, $this->providedIn->day(11), '124161', DwsServiceCodeCategory::visitingCareForPwsd1(), 138, false, 4),
                );
            case 1: // 1つ目の市町村：居宅介護の例
                return Seq::empty();
            case 2: // 2つ目の市町村：重度訪問介護の例
                $endOfMonth = $this->providedIn->endOfMonth()->startOfDay();
                return Seq::from(
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124371', DwsServiceCodeCategory::visitingCareForPwsd3(), 276),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124381', DwsServiceCodeCategory::visitingCareForPwsd3(), 135),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124491', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124501', DwsServiceCodeCategory::visitingCareForPwsd3(), 137),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124511', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124521', DwsServiceCodeCategory::visitingCareForPwsd3(), 135),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124531', DwsServiceCodeCategory::visitingCareForPwsd3(), 138),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124321', DwsServiceCodeCategory::visitingCareForPwsd3(), 128, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '122321', DwsServiceCodeCategory::visitingCareForPwsd3(), 106, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121331', DwsServiceCodeCategory::visitingCareForPwsd3(), 85, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121341', DwsServiceCodeCategory::visitingCareForPwsd3(), 80, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '121351', DwsServiceCodeCategory::visitingCareForPwsd3(), 86, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '123351', DwsServiceCodeCategory::visitingCareForPwsd3(), 108, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '123361', DwsServiceCodeCategory::visitingCareForPwsd3(), 100, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(8), '124361', DwsServiceCodeCategory::visitingCareForPwsd3(), 120, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124171', DwsServiceCodeCategory::visitingCareForPwsd1(), 318),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124181', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124391', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124401', DwsServiceCodeCategory::visitingCareForPwsd1(), 158),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124411', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124421', DwsServiceCodeCategory::visitingCareForPwsd1(), 156),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124431', DwsServiceCodeCategory::visitingCareForPwsd1(), 159),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124121', DwsServiceCodeCategory::visitingCareForPwsd1(), 147, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '122121', DwsServiceCodeCategory::visitingCareForPwsd1(), 123, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121131', DwsServiceCodeCategory::visitingCareForPwsd1(), 98, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121141', DwsServiceCodeCategory::visitingCareForPwsd1(), 92, false, 8),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '121151', DwsServiceCodeCategory::visitingCareForPwsd1(), 99, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '123151', DwsServiceCodeCategory::visitingCareForPwsd1(), 124, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '123161', DwsServiceCodeCategory::visitingCareForPwsd1(), 115, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '124161', DwsServiceCodeCategory::visitingCareForPwsd1(), 138, false, 4),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128453', DwsServiceCodeCategory::outingSupportForPwsd(), 100),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128457', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128461', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128465', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128469', DwsServiceCodeCategory::outingSupportForPwsd(), 25),
                    $this->serviceDetail($this->users[2]->id, $this->providedIn->day(23), '128473', DwsServiceCodeCategory::outingSupportForPwsd(), 50),
                    $this->serviceDetail($this->users[2]->id, $endOfMonth, '126010', DwsServiceCodeCategory::specifiedOfficeAddition1(), 4446, true),
                );
        }
    }
}
