<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\DwsAreaGrade\DwsAreaGradeFee;
use Domain\DwsAreaGrade\DwsAreaGradeFeeRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 障害福祉サービス：地域区分単価 Seeder.
 */
final class DwsAreaGradeFeeSeeder extends Seeder
{
    private DwsAreaGradeFeeRepository $repository;
    private TransactionManager $transaction;

    /**
     * DwsAreaGradeFeeSeeder constructor.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFeeRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsAreaGradeFeeRepository $repository,
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
        foreach ($this->dwsAreaGradeFees() as $dwsAreaGradeFee) {
            $this->repository->store($dwsAreaGradeFee);
        }
    }

    /**
     * 障害福祉サービス：地域区分単価の一覧を生成する.
     *
     * @return \Domain\DwsAreaGrade\DwsAreaGradeFee[]|iterable
     */
    protected function dwsAreaGradeFees(): iterable
    {
        // キー: 単価 ID
        // 0: 地域区分 ID
        // 1: 適用日
        // 2: 単価（小数部2桁）
        $xs = [
            1 => [1, '2018-04-01', 11_2000],
            2 => [2, '2018-04-01', 10_9600],
            3 => [3, '2018-04-01', 10_9000],
            4 => [4, '2018-04-01', 10_7200],
            5 => [5, '2018-04-01', 10_6000],
            6 => [6, '2018-04-01', 10_3600],
            7 => [7, '2018-04-01', 10_1800],
            8 => [8, '2018-04-01', 10_0000],
        ];
        foreach ($xs as $id => $values) {
            [$dwsAreaGradeId, $effectivatedOnString, $feeAsInt] = $values;
            $effectivatedOn = Carbon::parse($effectivatedOnString);
            $fee = Decimal::fromInt($feeAsInt);
            $attrs = compact('id', 'dwsAreaGradeId', 'effectivatedOn', 'fee');
            yield DwsAreaGradeFee::create($attrs);
        }
    }
}
