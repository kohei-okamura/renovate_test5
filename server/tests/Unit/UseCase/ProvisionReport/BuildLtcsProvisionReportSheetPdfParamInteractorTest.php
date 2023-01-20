<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetPdf;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildLtcsProvisionReportSheetPdfParamInteractor} のテスト.
 */
final class BuildLtcsProvisionReportSheetPdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use GetOfficeListUseCaseMixin;

    private LtcsProvisionReport $report;
    private LtcsInsCard $insCard;
    private Office $office;
    private User $user;
    private Seq $serviceDetails;
    private Map $serviceCodeMap;
    private bool $needsMaskingInsNumber;
    private bool $needsMaskingInsName;
    private BuildLtcsProvisionReportSheetPdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (BuildLtcsProvisionReportSheetPdfParamInteractorTest $self): void {
            $self->report = $self->examples->ltcsProvisionReports[0];
            $self->insCard = $self->examples->ltcsInsCards[0]->copy(['ltcsLevel' => LtcsLevel::careLevel3()]);
            $self->office = $self->examples->offices[0];
            $self->user = $self->examples->users[0];
            $unmanagedServiceDetail = $self->examples->ltcsBillingServiceDetails[0]->copy([
                'isLimited' => false,
            ]);
            $self->serviceDetails = Seq::from($unmanagedServiceDetail, ...$self->examples->ltcsBillingServiceDetails);
            $self->serviceCodeMap = Seq::from(...$self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name);
            $self->needsMaskingInsNumber = true;
            $self->needsMaskingInsName = true;
        });
        self::beforeEachSpec(function (BuildLtcsProvisionReportSheetPdfParamInteractorTest $self): void {
            $self->interactor = app(BuildLtcsProvisionReportSheetPdfParamInteractor::class);
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[21]))
                ->byDefault();
            $self->interactor = app(BuildLtcsProvisionReportSheetPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return params for ltcs provision report sheet pdf', function (): void {
            $serviceCodeMap = Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name);
            $actual = LtcsProvisionReportSheetPdf::from(
                Option::from($this->insCard),
                $this->insCard,
                $this->serviceDetails,
                $this->serviceDetails,
                $this->user,
                Carbon::now(),
                $this->report,
                $serviceCodeMap,
                $this->office,
                Option::from($this->examples->offices[21]),
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName
            );
            $this->assertArrayStrictEquals(
                $actual->toArray(),
                $this->interactor->handle(
                    $this->context,
                    Option::from($this->insCard),
                    $this->insCard,
                    $this->serviceDetails,
                    $this->serviceDetails,
                    $this->user,
                    Carbon::now(),
                    $this->report,
                    $this->office,
                    $this->serviceCodeMap,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName
                )->toArray()
            );
        });
        $this->should('use GetOfficeListUseCase', function (): void {
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, $this->insCard->carePlanAuthorOfficeId)
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                Option::from($this->insCard),
                $this->insCard,
                $this->serviceDetails,
                $this->serviceDetails,
                $this->user,
                Carbon::now(),
                $this->report,
                $this->office,
                $this->serviceCodeMap,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName
            );
        });
    }
}
