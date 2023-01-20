<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Expense のテスト
 */
class ExpenseTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected Expense $expense;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ExpenseTest $self): void {
            $self->values = [
                'taxExcluded' => 1000,
                'taxIncluded' => 1100,
                'taxType' => TaxType::taxIncluded(),
                'taxCategory' => TaxCategory::consumptionTax(),
            ];
            $self->expense = Expense::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have taxExcluded attribute', function (): void {
            $this->assertSame($this->expense->get('taxExcluded'), Arr::get($this->values, 'taxExcluded'));
        });
        $this->should('have taxIncluded attribute', function (): void {
            $this->assertSame($this->expense->get('taxIncluded'), Arr::get($this->values, 'taxIncluded'));
        });
        $this->should('have taxType attribute', function (): void {
            $this->assertSame($this->expense->get('taxType'), Arr::get($this->values, 'taxType'));
        });
        $this->should('have taxCategory attribute', function (): void {
            $this->assertSame($this->expense->get('taxCategory'), Arr::get($this->values, 'taxCategory'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->expense);
        });
    }
}
