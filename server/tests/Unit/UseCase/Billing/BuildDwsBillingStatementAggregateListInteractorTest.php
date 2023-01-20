<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\IdentifyDwsAreaGradeFeeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementAggregateListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementAggregateListInteractor} のテスト.
 */
final class BuildDwsBillingStatementAggregateListInteractorTest extends Test
{
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use IdentifyDwsAreaGradeFeeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsAreaGradeFee $fee;

    /** @var \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq */
    private Seq $elements;

    private BuildDwsBillingStatementAggregateListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->fee = DwsAreaGradeFee::create([
                'id' => 1,
                'dwsAreaGradeId' => 1,
                'effectivatedOn' => Carbon::create(2019, 4, 1),
                'fee' => Decimal::fromInt(11_2000),
            ]);
            $self->elements = $self->statementElements();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyDwsAreaGradeFeeUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->fee))
                ->byDefault();

            $self->interactor = app(BuildDwsBillingStatementAggregateListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('identify DwsAreaGradeFee', function (): void {
            $this->identifyDwsAreaGradeFeeUseCase
                ->expects('handle')
                ->with($this->context, $this->office->dwsGenericService->dwsAreaGradeId, $this->providedIn)
                ->andReturn(Option::some($this->fee));

            $this->interactor->handle(
                context: $this->context,
                office: $this->office,
                providedIn: $this->providedIn,
                contract: $this->contract,
                certification: $this->dwsCertification,
                userSubsidyOption: Option::none(),
                elements: $this->elements,
                coordinatedCopayOption: Option::none(),
                baseStatementOption: Option::none()
            );
        });
        $this->should('throw SetupException when DwsAreaGrade is not found', function (): void {
            $this->identifyDwsAreaGradeFeeUseCase->expects('handle')->andReturn(Option::none());

            $this->assertThrows(SetupException::class, function (): void {
                $this->interactor->handle(
                    context: $this->context,
                    office: $this->office,
                    providedIn: $this->providedIn,
                    contract: $this->contract,
                    certification: $this->dwsCertification,
                    userSubsidyOption: Option::none(),
                    elements: $this->elements,
                    coordinatedCopayOption: Option::none(),
                    baseStatementOption: Option::none()
                );
            });
        });
        $this->should('return a Seq of DwsBillingStatementAggregate', function (): void {
            $actual = $this->interactor->handle(
                context: $this->context,
                office: $this->office,
                providedIn: $this->providedIn,
                contract: $this->contract,
                certification: $this->dwsCertification,
                userSubsidyOption: Option::none(),
                elements: $this->elements,
                coordinatedCopayOption: Option::none(),
                baseStatementOption: Option::none()
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof DwsBillingStatementAggregate);
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return a Seq of DwsBillingStatementAggregate when coordinatedCopay given', function (): void {
            $actual = $this->interactor->handle(
                context: $this->context,
                office: $this->office,
                providedIn: $this->providedIn,
                contract: $this->contract,
                certification: $this->dwsCertification,
                userSubsidyOption: Option::none(),
                elements: $this->elements,
                coordinatedCopayOption: Option::some(9300),
                baseStatementOption: Option::none()
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof DwsBillingStatementAggregate);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
