<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Faker\Generator;

/**
 * OwnExpenseProgram Example.
 *
 * @property-read OwnExpenseProgram[] $ownExpensePrograms
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 */
trait OwnExpenseProgramExample
{
    /**
     * 自費サービス情報の一覧を生成する.
     *
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram[]
     */
    protected function ownExpensePrograms(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateOwnExpenseProgram([
                'id' => 1,
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 2,
                'name' => '洗濯',
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 3,
                'officeId' => $this->offices[1]->id,
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 4,
                'organizationId' => $this->organizations[1]->id,
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 5,
                'officeId' => null,
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 6,
                'officeId' => null,
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 7,
                'fee' => Expense::create([
                    'taxExcluded' => 3000,
                    'taxIncluded' => 3240,
                    'taxType' => TaxType::taxIncluded(),
                    'taxCategory' => TaxCategory::reducedConsumptionTax(),
                ]),
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 8,
                'fee' => Expense::create([
                    'taxExcluded' => 5400,
                    'taxIncluded' => 5940,
                    'taxType' => TaxType::taxExcluded(),
                    'taxCategory' => TaxCategory::consumptionTax(),
                ]),
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 9,
                'fee' => Expense::create([
                    'taxExcluded' => 1500,
                    'taxIncluded' => 1620,
                    'taxType' => TaxType::taxExcluded(),
                    'taxCategory' => TaxCategory::reducedConsumptionTax(),
                ]),
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 10,
                'fee' => Expense::create([
                    'taxExcluded' => 4320,
                    'taxIncluded' => 4320,
                    'taxType' => TaxType::taxExempted(),
                    'taxCategory' => TaxCategory::unapplicable(),
                ]),
            ], $faker),
            $this->generateOwnExpenseProgram([
                'id' => 11,
                'fee' => Expense::create([
                    'taxExcluded' => 2980,
                    'taxIncluded' => 2980,
                    'taxType' => TaxType::taxExempted(),
                    'taxCategory' => TaxCategory::unapplicable(),
                ]),
            ], $faker),
            // 請求額が 0 円（dws, ltcs で分けたいため 2 つ用意する）
            $this->generateOwnExpenseProgram([
                'id' => 12,
                'fee' => Expense::create([
                    'taxExcluded' => 0,
                    'taxIncluded' => 0,
                    'taxType' => TaxType::taxExempted(),
                    'taxCategory' => TaxCategory::unapplicable(),
                ]),
            ], $faker),
            // 請求額が 0 円（dws, ltcs で分けたいため 2 つ用意する）
            $this->generateOwnExpenseProgram([
                'id' => 13,
                'fee' => Expense::create([
                    'taxExcluded' => 0,
                    'taxIncluded' => 0,
                    'taxType' => TaxType::taxExempted(),
                    'taxCategory' => TaxCategory::unapplicable(),
                ]),
            ], $faker),
        ];
    }

    /**
     * Generate an example of OwnExpenseProgram.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    protected function generateOwnExpenseProgram(array $overwrites, Generator $faker): OwnExpenseProgram
    {
        $attrs = [
            'organizationId' => $this->organizations[0]->id,
            'officeId' => $this->offices[0]->id,
            'name' => '掃除',
            'durationMinutes' => 60,
            'fee' => Expense::create([
                'taxExcluded' => 1000,
                'taxIncluded' => 1100,
                'taxType' => TaxType::taxIncluded(),
                'taxCategory' => TaxCategory::consumptionTax(),
            ]),
            'note' => $faker->realText(100),
            'isEnabled' => true,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return OwnExpenseProgram::create($overwrites + $attrs);
    }
}
