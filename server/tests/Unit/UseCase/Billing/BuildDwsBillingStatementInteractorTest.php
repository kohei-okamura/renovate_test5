<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingStatementElement as StatementElement;
use Domain\Billing\DwsBillingStatementItem as StatementItem;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Decimal;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractStatus;
use Domain\DwsAreaGrade\DwsAreaGrade;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\FinderResult;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementAggregateListUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsBillingStatementContractListUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsBillingStatementElementListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContractFinderMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementInteractor} のテスト.
 */
final class BuildDwsBillingStatementInteractorTest extends Test
{
    use BuildDwsBillingStatementAggregateListUseCaseMixin;
    use BuildDwsBillingStatementContractListUseCaseMixin;
    use BuildDwsBillingStatementElementListUseCaseMixin;
    use CarbonMixin;
    use ContractFinderMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use IdentifyDwsCertificationUseCaseMixin;
    use IdentifyUserDwsSubsidyUseCaseMixin;
    use LookupDwsAreaGradeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsAreaGrade $dwsAreaGrade;

    /** @var \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq */
    private Seq $details;

    private BuildDwsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->dwsAreaGrade = DwsAreaGrade::create([
                'id' => 1,
                'code' => '01',
                'name' => '一級地',
            ]);
            $self->details = $self->serviceDetails(
                DwsServiceDivisionCode::visitingCareForPwsd(),
                $self->bundle->providedIn
            );
        });
        self::beforeEachSpec(function (self $self): void {
            $self->contractFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$self->contract], Pagination::create([])))
                ->byDefault();

            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->dwsCertification))
                ->byDefault();

            $self->identifyUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->userDwsSubsidy))
                ->byDefault();

            $self->lookupDwsAreaGradeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->dwsAreaGrade))
                ->byDefault();

            $self->buildDwsBillingStatementElementListUseCase
                ->allows('handle')
                ->andReturn($self->statementElements)
                ->byDefault();

            $self->buildDwsBillingStatementAggregateListUseCase
                ->allows('handle')
                ->andReturn($self->statementAggregates)
                ->byDefault();

            $self->buildDwsBillingStatementContractListUseCase
                ->allows('handle')
                ->andReturn($self->statementContracts)
                ->byDefault();

            $self->interactor = app(BuildDwsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('identify Contract using ContractFinder', function (): void {
            $filterParams = [
                'officeId' => $this->office->id,
                'userId' => $this->user->id,
                'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                'status' => [ContractStatus::formal(), ContractStatus::terminated()],
                'contractedOnBefore' => $this->bundle->providedIn->endOfMonth(),
                'terminatedOnAfter' => $this->bundle->providedIn->startOfMonth(),
            ];
            $paginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'id',
                'desc' => true,
            ];
            $this->contractFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from([$this->contract], Pagination::create([])));

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );
        });
        $this->should('identify DwsCertification', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->user->id, $this->bundle->providedIn)
                ->andReturn(Option::some($this->dwsCertification));

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );
        });
        $this->should('identify UserDwsSubsidy', function (): void {
            $this->identifyUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, $this->user, $this->bundle->providedIn)
                ->andReturn(Option::some($this->userDwsSubsidy));

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );
        });
        $this->should('lookup DwsAreaGrade', function (): void {
            $this->lookupDwsAreaGradeUseCase
                ->expects('handle')
                ->with($this->context, $this->office->dwsGenericService->dwsAreaGradeId)
                ->andReturn(Seq::from($this->dwsAreaGrade));

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );
        });
        $this->should(
            'build elements when dwsBillingCopayCoordination is empty and selfCopayCoordination',
            function (): void {
                $dwsCertification = $this->dwsCertification->copy([
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::internal(),
                        'officeId' => $this->office->id,
                    ]),
                ]);
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::some($dwsCertification))
                    ->byDefault();
                $this->buildDwsBillingStatementElementListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->homeHelpServiceCalcSpec,
                        $this->visitingCareForPwsdCalcSpec,
                        true,
                        $this->providedIn,
                        $this->details
                    )
                    ->andReturn($this->statementElements);

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->bundle,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    $this->user,
                    $this->details,
                    Option::none(),
                    Option::none()
                );
            }
        );
        $this->should(
            'build elements when services of other offices exist and selfCopayCoordination',
            function (): void {
                $dwsCertification = $this->dwsCertification->copy([
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::internal(),
                        'officeId' => $this->office->id,
                    ]),
                ]);
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::some($dwsCertification))
                    ->byDefault();
                $this->buildDwsBillingStatementElementListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->homeHelpServiceCalcSpec,
                        $this->visitingCareForPwsdCalcSpec,
                        true,
                        $this->providedIn,
                        $this->details
                    )
                    ->andReturn($this->statementElements);

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->bundle,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    $this->user,
                    $this->details,
                    Option::from($this->dwsCopayCoordination),
                    Option::none()
                );
            }
        );
        $this->should(
            'build elements when copayCoordination is fixed and services of other offices exist fixed and not selfCopayCoordination',
            function (): void {
                $dwsCertification = $this->dwsCertification->copy([
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::external(),
                        'officeId' => $this->office->id,
                    ]),
                ]);
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::some($dwsCertification))
                    ->byDefault();
                $this->buildDwsBillingStatementElementListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->homeHelpServiceCalcSpec,
                        $this->visitingCareForPwsdCalcSpec,
                        false,
                        $this->providedIn,
                        $this->details
                    )
                    ->andReturn($this->statementElements);

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->bundle,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    $this->user,
                    $this->details,
                    Option::from($this->dwsCopayCoordination),
                    Option::none()
                );
            }
        );
        $this->should(
            'build elements when copayCoordination is fixed and services of other offices not exist and selfCopayCoordination',
            function (): void {
                $dwsCertification = $this->dwsCertification->copy([
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::internal(),
                        'officeId' => $this->office->id,
                    ]),
                ]);
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::some($dwsCertification))
                    ->byDefault();
                $this->buildDwsBillingStatementElementListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->homeHelpServiceCalcSpec,
                        $this->visitingCareForPwsdCalcSpec,
                        false,
                        $this->providedIn,
                        $this->details
                    )
                    ->andReturn($this->statementElements);

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->bundle,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    $this->user,
                    $this->details,
                    Option::from($this->dwsCopayCoordinations[1]),
                    Option::none()
                );
            }
        );
        $this->should(
            'build elements when copayCoordination is not fixed and selfCopayCoordination',
            function (): void {
                $dwsCertification = $this->dwsCertification->copy([
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::internal(),
                        'officeId' => $this->office->id,
                    ]),
                ]);
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::some($dwsCertification))
                    ->byDefault();
                $this->buildDwsBillingStatementElementListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->homeHelpServiceCalcSpec,
                        $this->visitingCareForPwsdCalcSpec,
                        true,
                        $this->providedIn,
                        $this->details
                    )
                    ->andReturn($this->statementElements);

                $this->interactor->handle(
                    $this->context,
                    $this->office,
                    $this->bundle,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    $this->user,
                    $this->details,
                    Option::from($this->dwsCopayCoordinations[2]),
                    Option::none()
                );
            }
        );
        $this->should('build aggregates', function (): void {
            $statement = Option::from($this->statement);
            $this->buildDwsBillingStatementAggregateListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->office,
                    $this->bundle->providedIn,
                    $this->contract,
                    $this->dwsCertification,
                    Mockery::capture($actualUserSubsidyOption),
                    $this->statementElements,
                    Option::none(),
                    $statement,
                )
                ->andReturnUsing(fn (): Seq => $this->statementAggregates(Decimal::fromInt(11_2000)));

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                $statement
            );

            $this->assertSame($this->userDwsSubsidy, $actualUserSubsidyOption->get());
        });
        $this->should('build contracts', function (): void {
            $this->buildDwsBillingStatementContractListUseCase
                ->expects('handle')
                ->with($this->context, $this->office, $this->dwsCertification, $this->bundle->providedIn)
                ->andReturn($this->statementContracts);

            $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );
        });
        $this->should('build items using elements', function (): void {
            $actualStatement = $this->interactor->handle(
                $this->context,
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none()
            );

            $this->assertEach(
                function (StatementItem $expected, StatementItem $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                [...$this->statementElements->map(fn (StatementElement $x): StatementItem => $x->toItem())],
                $actualStatement->items,
            );
        });
        $this->should(
            'return a DwsBillingStatement',
            function (...$args): void {
                $actual = $this->interactor->handle($this->context, ...$args);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples()]
        );
    }

    /**
     * テストパターンを生成する.
     *
     * @return array
     */
    private function examples(): array
    {
        return [
            'general case' => [
                $this->office,
                $this->bundle,
                $this->homeHelpServiceCalcSpec,
                $this->visitingCareForPwsdCalcSpec,
                $this->user,
                $this->details,
                Option::none(),
                Option::none(),
            ],
        ];
    }
}
