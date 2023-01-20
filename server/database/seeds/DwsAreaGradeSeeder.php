<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\DwsAreaGrade\DwsAreaGrade;
use Domain\DwsAreaGrade\DwsAreaGradeRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 障害福祉サービス：地域区分 Seeder.
 */
final class DwsAreaGradeSeeder extends Seeder
{
    private DwsAreaGradeRepository $repository;
    private TransactionManager $transaction;

    /**
     * DwsAreaGradeSeeder constructor.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsAreaGradeRepository $repository,
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
        foreach ($this->dwsAreaGrades() as $dwsAreaGrade) {
            $this->repository->store($dwsAreaGrade);
        }
    }

    /**
     * 障害福祉サービス：地域区分の一覧を生成する.
     *
     * @return array|\Domain\DwsAreaGrade\DwsAreaGrade[]
     */
    protected function dwsAreaGrades(): array
    {
        return [
            DwsAreaGrade::create([
                'id' => 1,
                'code' => '01',
                'name' => '一級地',
            ]),
            DwsAreaGrade::create([
                'id' => 2,
                'code' => '02',
                'name' => '二級地',
            ]),
            DwsAreaGrade::create([
                'id' => 3,
                'code' => '03',
                'name' => '三級地',
            ]),
            DwsAreaGrade::create([
                'id' => 4,
                'code' => '04',
                'name' => '四級地',
            ]),
            DwsAreaGrade::create([
                'id' => 5,
                'code' => '05',
                'name' => '五級地',
            ]),
            DwsAreaGrade::create([
                'id' => 6,
                'code' => '06',
                'name' => '六級地',
            ]),
            DwsAreaGrade::create([
                'id' => 7,
                'code' => '07',
                'name' => '七級地',
            ]),
            DwsAreaGrade::create([
                'id' => 8,
                'code' => '20',
                'name' => 'その他',
            ]),
        ];
    }
}
