<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Domain\LtcsAreaGrade\LtcsAreaGradeFeeRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 介護保険サービス：地域区分 Seeder.
 */
final class LtcsAreaGradeFeeSeeder extends Seeder
{
    private LtcsAreaGradeFeeRepository $repository;
    private TransactionManager $transaction;

    /**
     * LtcsAreaGradeFeeSeeder constructor.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFeeRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LtcsAreaGradeFeeRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->ltcsAreaGradeFees() as $ltcsAreaGradeFee) {
            $this->repository->store($ltcsAreaGradeFee);
        }
    }

    /**
     * 介護保険サービス：地域区分単価の一覧を生成する.
     *
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee[]|iterable
     */
    private function ltcsAreaGradeFees(): iterable
    {
        // キー: 単価 ID
        // 0: 地域区分 ID
        // 1: 適用日
        // 2: 単価（小数部2桁）
        $xs = [
            1 => [1, '2018-04-01', 11_4000],
            2 => [2, '2018-04-01', 11_1200],
            3 => [3, '2018-04-01', 11_0500],
            4 => [4, '2018-04-01', 10_8400],
            5 => [5, '2018-04-01', 10_7000],
            6 => [6, '2018-04-01', 10_4200],
            7 => [7, '2018-04-01', 10_2100],
            8 => [8, '2018-04-01', 10_0000],
        ];
        foreach ($xs as $id => $values) {
            [$ltcsAreaGradeId, $effectivatedOnString, $feeAsInt] = $values;
            $effectivatedOn = Carbon::parse($effectivatedOnString);
            $fee = Decimal::fromInt($feeAsInt);
            $attrs = compact('id', 'ltcsAreaGradeId', 'effectivatedOn', 'fee');
            yield LtcsAreaGradeFee::create($attrs);
        }
    }
}
