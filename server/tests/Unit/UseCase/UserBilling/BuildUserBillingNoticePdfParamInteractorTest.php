<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\StructuredName;
use Domain\UserBilling\UserBilling;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\BuildUserBillingNoticePdfParamInteractor;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingNoticePdfParamInteractor} のテスト.
 */
final class BuildUserBillingNoticePdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use UnitSupport;

    private UserBilling $userBilling;
    private Carbon $issuedOn;
    private BuildUserBillingNoticePdfParamInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->interactor = app(BuildUserBillingNoticePdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a array of params', function (): void {
            // exampleに追加したいが色々テストでこけたのでここで調整する
            $statement = $this->examples->dwsBillingStatements[18]->copy([
                'id' => 1,
                'user' => DwsBillingUser::create([
                    'userId' => $this->examples->users[16]->id,
                    'dwsCertificationId' => $this->examples->dwsCertifications[0]->id,
                    'dwsNumber' => 1234567890,
                    'name' => new StructuredName(
                        familyName: '固定姓',
                        givenName: '固定名',
                        phoneticFamilyName: 'コテイセイ',
                        phoneticGivenName: 'コテイメイ',
                    ),
                    'childName' => new StructuredName(
                        familyName: '固定児童姓',
                        givenName: '固定児童名',
                        phoneticFamilyName: 'コテイジドウセイ',
                        phoneticGivenName: 'コテイジドウメイ',
                    ),
                    'copayLimit',
                ]),
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 700000,
                        unmanagedCopay: 10000,
                        managedCopay: 200000,
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: 100000,
                        subtotalSubsidy: 100000,
                    ),
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 700000,
                        unmanagedCopay: 10000,
                        managedCopay: 200000,
                        cappedCopay: 37200,
                        adjustedCopay: null,
                        coordinatedCopay: null,
                        subtotalCopay: 37200,
                        subtotalBenefit: 100000,
                        subtotalSubsidy: 100000,
                    ),
                ],
            ]);
            $this->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($statement));
            $this->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->users[16]));

            $actual = $this->interactor->handle(
                $this->context,
                Seq::from($this->examples->userBillings[14]),
                $this->issuedOn
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('use SimpleLookupDwsBillingStatementUseCase', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

            $this->interactor->handle(
                $this->context,
                Seq::from($this->examples->userBillings[0]),
                $this->issuedOn
            );
        });
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingBundles[0]));

            $this->interactor->handle(
                $this->context,
                Seq::from($this->examples->userBillings[0]),
                $this->issuedOn
            );
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle(
                $this->context,
                Seq::from($this->examples->userBillings[0]),
                $this->issuedOn
            );
        });
        $this->should(
            'throw NotFoundException when discrepancy in the number of cases returned by SimpleLookupDwsBillingStatementUseCase',
            function (): void {
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Seq::from($this->examples->userBillings[0], $this->examples->userBillings[1]),
                        $this->issuedOn
                    );
                });
            }
        );
        $this->should(
            'throw NotFoundException when discrepancy in the number of cases returned by LookupDwsBillingBundleUseCase',
            function (): void {
                $this->lookupDwsBillingBundleUseCase
                    ->expects('handle')
                    ->andReturn(Seq::empty());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Seq::from($this->examples->userBillings[0]),
                        $this->issuedOn
                    );
                });
            }
        );
    }
}
