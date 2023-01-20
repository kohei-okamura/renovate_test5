<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\LtcsAreaGrade\LtcsAreaGrade;
use Domain\LtcsAreaGrade\LtcsAreaGradeRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 介護保険サービス：地域区分 Seeder.
 */
final class LtcsAreaGradeSeeder extends Seeder
{
    private LtcsAreaGradeRepository $repository;
    private TransactionManager $transaction;

    /**
     * LtcsAreaGradeSeeder constructor.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LtcsAreaGradeRepository $repository,
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
        foreach ($this->ltcsAreaGrades() as $ltcsAreaGrade) {
            $this->repository->store($ltcsAreaGrade);
        }
    }

    /**
     * 介護保険サービス：地域区分の一覧を生成する.
     *
     * @return array|\Domain\LtcsAreaGrade\LtcsAreaGrade[]
     */
    protected function ltcsAreaGrades(): array
    {
        return [
            LtcsAreaGrade::create([
                'id' => 1,
                'code' => '1',
                'name' => '1級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 2,
                'code' => '6',
                'name' => '2級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 3,
                'code' => '7',
                'name' => '3級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 4,
                'code' => '2',
                'name' => '4級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 5,
                'code' => '3',
                'name' => '5級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 6,
                'code' => '4',
                'name' => '6級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 7,
                'code' => '9',
                'name' => '7級地',
            ]),
            LtcsAreaGrade::create([
                'id' => 8,
                'code' => '5',
                'name' => 'その他',
            ]),
        ];
    }
}
