<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\OwnExpenseProgram;

use Domain\Common\Carbon;
use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * OwnExpenseProgram のテスト
 */
class OwnExpenseProgramTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected OwnExpenseProgram $ownExpenseProgram;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OwnExpenseProgramTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'officeId' => 1,
                'name' => '掃除',
                'durationMinutes' => 60,
                'fee' => Expense::create([
                    'taxExcluded' => 1000,
                    'taxIncluded' => 1100,
                    'taxType' => TaxType::taxIncluded(),
                    'taxCategory' => TaxCategory::consumptionTax(),
                ]),
                'note' => '',
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->ownExpenseProgram = OwnExpenseProgram::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have officeId attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('officeId'), Arr::get($this->values, 'officeId'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have durationMinutes attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('durationMinutes'), Arr::get($this->values, 'durationMinutes'));
        });
        $this->should('have fee attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('fee'), Arr::get($this->values, 'fee'));
        });
        $this->should('have note attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('note'), Arr::get($this->values, 'note'));
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('isEnabled'), Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->ownExpenseProgram->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ownExpenseProgram);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isForAllOffices(): void
    {
        $this->should('return false if the service is for a particular office', function (): void {
            $this->assertFalse($this->ownExpenseProgram->isForAllOffices());
        });
        $this->should('return true if the service is for all offices', function (): void {
            $program = $this->ownExpenseProgram->copy([
                'officeId' => null,
            ]);
            $this->assertTrue($program->isForAllOffices());
        });
    }
}
