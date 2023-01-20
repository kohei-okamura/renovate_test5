<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Office\OfficeDwsGenericService;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\OfficeHasDwsGenericServiceRule} のテスト.
 */
final class OfficeHasDwsGenericServiceRuleTest extends Test
{
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateOfficeHasDwsGenericService(): void
    {
        $this->should('pass when officeId is not integer', function (): void {
            $this->getOfficeListUseCase
                ->expects('handle')
                ->times(0);

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'error'],
                    ['value' => 'office_has_dws_generic_service']
                )
                    ->passes()
            );
        });
        $this->should('pass when office does not exist', function (): void {
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => self::NOT_EXISTING_ID],
                    ['value' => 'office_has_dws_generic_service']
                )
                    ->passes()
            );
        });
        $this->should('pass when dwsGenericService is not null', function (): void {
            $office = $this->examples->offices[0]->copy([
                'dwsGenericService' => OfficeDwsGenericService::create([
                    'dwsAreaGradeId' => 1,
                    'code' => '0123456789',
                    'openedOn' => Carbon::now()->startOfDay(),
                    'designationExpiredOn' => Carbon::now()->startOfDay(),
                ]),
            ]);
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, $office->id)
                ->andReturn(Seq::from($office));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $office->id],
                    ['value' => 'office_has_dws_generic_service']
                )
                    ->passes()
            );
        });
        $this->should('fail when dwsGenericService is null', function (): void {
            $office = $this->examples->offices[0]->copy([
                'dwsGenericService' => null,
            ]);
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, $office->id)
                ->andReturn(Seq::from($office));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $office->id],
                    ['value' => 'office_has_dws_generic_service']
                )
                    ->fails()
            );
        });
    }
}
