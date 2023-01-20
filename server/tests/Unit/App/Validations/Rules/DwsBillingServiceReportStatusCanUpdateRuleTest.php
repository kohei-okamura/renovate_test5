<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsBillingServiceReportStatusCanUpdateRule} のテスト.
 */
final class DwsBillingServiceReportStatusCanUpdateRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingServiceReportUseCaseMixin;

    private DwsBillingServiceReport $serviceReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->serviceReport = $self->examples->dwsBillingServiceReports[0];
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBillingCanUpdate(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->serviceReport->dwsBillingId,
                    'billingBundleId' => $this->serviceReport->dwsBillingBundleId,
                    'id' => $this->serviceReport->id,
                    'status' => DwsBillingStatus::ready()->value(),
                ],
                [
                    'status' => 'dws_billing_service_report_status_can_update',
                ],
                [],
                []
            );
        };
        $this->should('pass when billingId is invalid', function () use ($customValidator): void {
            $billingId = 'error';
            $this->assertTrue($customValidator(compact('billingId'))->passes());
        });
        $this->should('pass when billingBundleId is invalid', function () use ($customValidator): void {
            $billingBundleId = 'error';
            $this->assertTrue($customValidator(compact('billingBundleId'))->passes());
        });
        $this->should('pass when id is invalid', function () use ($customValidator): void {
            $id = 'error';
            $this->assertTrue($customValidator(compact('id'))->passes());
        });
        $this->should('pass when status is invalid', function () use ($customValidator): void {
            $status = self::INVALID_ENUM_VALUE;
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
        $this->should('fail when status is neither ready nor fixed', function () use ($customValidator): void {
            $status = DwsBillingStatus::disabled()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('pass when billing is not found', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue($customValidator()->passes());
        });
        $this->should('fail when billing status is fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()])));
            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when billing status is disabled', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::disabled()])));
            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when status is fixed although current status is not ready', function () use ($customValidator): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->serviceReport->copy(['status' => DwsBillingStatus::checking()])));

            $status = DwsBillingStatus::fixed()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('fail when status is ready although current status is not fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->serviceReport->copy(['status' => DwsBillingStatus::checking()])));

            $status = DwsBillingStatus::ready()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('pass when status is fixed and current status is ready', function () use ($customValidator): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->serviceReport->copy(['status' => DwsBillingStatus::ready()])));

            $status = DwsBillingStatus::fixed()->value();
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
        $this->should('pass when status is ready and current status is fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->serviceReport->copy(['status' => DwsBillingStatus::fixed()])));

            $status = DwsBillingStatus::ready()->value();
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
    }
}
