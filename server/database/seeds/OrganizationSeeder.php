<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Organization\Organization;
use Domain\Organization\OrganizationRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 事業者 Seeder.
 */
final class OrganizationSeeder extends Seeder
{
    private OrganizationRepository $repository;
    private TransactionManager $transaction;

    /**
     * OrganizationSeeder constructor.
     *
     * @param \Domain\Organization\OrganizationRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        OrganizationRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /**
     * Run the database seeds.
     *
     * @throws \Throwable
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();
        //
        // **重要**
        // 定義を変更する場合は version をインクリメントすること
        //
        $organization = Organization::create([
            'id' => 1,
            'code' => 'eustylelab',
            'name' => 'ユースタイルラボラトリー株式会社',
            'addr' => new Addr(
                postcode: '164-0011',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: '中央1-35-6',
                apartment: 'レッチフィールド中野坂上ビル6F',
            ),
            'tel' => '03-5937-6825',
            'fax' => '03-5937-6828',
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        $this->transaction->run(function () use ($organization): void {
            $exists = $this->repository
                ->lookup($organization->id)
                ->exists(function (Organization $x) use ($organization) {
                    return $x->version >= $organization->version;
                });
            if (!$exists) {
                $this->repository->store($organization);
            }
        });
    }
}
