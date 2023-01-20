<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\LtcsProjectServiceMenu;
use Domain\Project\LtcsProjectServiceMenuRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 介護保険サービス計画：サービス内容 Seeder.
 */
class LtcsProjectServiceMenuSeeder extends Seeder
{
    private LtcsProjectServiceMenuRepository $repository;
    private TransactionManager $transaction;

    /**
     * ServiceMenuSeeder constructor.
     *
     * @param \Domain\Project\LtcsProjectServiceMenuRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LtcsProjectServiceMenuRepository $repository,
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
        foreach ($this->serviceMenus() as $serviceMenu) {
            $this->repository->store($serviceMenu);
        }
    }

    /**
     * サービス内容の一覧を生成する.
     *
     * @return array|\Domain\Project\LtcsProjectServiceMenu[]
     */
    protected function serviceMenus(): array
    {
        return [
            $this->generateServiceMenu([
                'id' => 1,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => 'サービス準備・記録等',
                'displayName' => 'サービス準備・記録等',
                'sortOrder' => 1,
            ]),
            $this->generateServiceMenu([
                'id' => 2,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '排泄介助',
                'displayName' => '排泄介助',
                'sortOrder' => 2,
            ]),
            $this->generateServiceMenu([
                'id' => 3,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '食事介助',
                'displayName' => '食事介助',
                'sortOrder' => 3,
            ]),
            $this->generateServiceMenu([
                'id' => 4,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '専門的配慮をもって行う調理',
                'displayName' => '専門的配慮をもって行う調理',
                'sortOrder' => 4,
            ]),
            $this->generateServiceMenu([
                'id' => 5,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '清拭（全身清拭）',
                'displayName' => '清拭（全身清拭）',
                'sortOrder' => 5,
            ]),
            $this->generateServiceMenu([
                'id' => 6,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '部分浴',
                'displayName' => '部分浴',
                'sortOrder' => 6,
            ]),
            $this->generateServiceMenu([
                'id' => 7,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '全身浴',
                'displayName' => '全身浴',
                'sortOrder' => 7,
            ]),
            $this->generateServiceMenu([
                'id' => 8,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '洗面等',
                'displayName' => '洗面等',
                'sortOrder' => 8,
            ]),
            $this->generateServiceMenu([
                'id' => 9,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '身体整容',
                'displayName' => '身体整容',
                'sortOrder' => 9,
            ]),
            $this->generateServiceMenu([
                'id' => 10,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '更衣介助',
                'displayName' => '更衣介助',
                'sortOrder' => 10,
            ]),
            $this->generateServiceMenu([
                'id' => 11,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '体位交換',
                'displayName' => '体位交換',
                'sortOrder' => 11,
            ]),
            $this->generateServiceMenu([
                'id' => 12,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '移動・移乗介助',
                'displayName' => '移動・移乗介助',
                'sortOrder' => 12,
            ]),
            $this->generateServiceMenu([
                'id' => 13,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '通院・外出介助',
                'displayName' => '通院・外出介助',
                'sortOrder' => 13,
            ]),
            $this->generateServiceMenu([
                'id' => 14,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '起床介助',
                'displayName' => '起床介助',
                'sortOrder' => 14,
            ]),
            $this->generateServiceMenu([
                'id' => 15,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '就寝介助',
                'displayName' => '就寝介助',
                'sortOrder' => 15,
            ]),
            $this->generateServiceMenu([
                'id' => 16,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '服薬介助',
                'displayName' => '服薬介助',
                'sortOrder' => 16,
            ]),
            $this->generateServiceMenu([
                'id' => 17,
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'name' => '自立生活支援・重度化防止のための見守り的援助',
                'displayName' => '自立生活支援・重度化防止のための見守り的援助',
                'sortOrder' => 17,
            ]),
            $this->generateServiceMenu([
                'id' => 18,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => 'サービス準備・記録等',
                'displayName' => 'サービス準備・記録等',
                'sortOrder' => 18,
            ]),
            $this->generateServiceMenu([
                'id' => 19,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '掃除',
                'displayName' => '掃除',
                'sortOrder' => 19,
            ]),
            $this->generateServiceMenu([
                'id' => 20,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '洗濯',
                'displayName' => '洗濯',
                'sortOrder' => 20,
            ]),
            $this->generateServiceMenu([
                'id' => 21,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => 'ベッドメイク',
                'displayName' => 'ベッドメイク',
                'sortOrder' => 21,
            ]),
            $this->generateServiceMenu([
                'id' => 22,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '衣類の整理・被服の補修',
                'displayName' => '衣類の整理・被服の補修',
                'sortOrder' => 22,
            ]),
            $this->generateServiceMenu([
                'id' => 23,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '一般的な調理、配下膳',
                'displayName' => '一般的な調理、配下膳',
                'sortOrder' => 23,
            ]),
            $this->generateServiceMenu([
                'id' => 24,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '買い物・薬の受け取り',
                'displayName' => '買い物・薬の受け取り',
                'sortOrder' => 24,
            ]),
        ];
    }

    /**
     * インスタンスを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Project\LtcsProjectServiceMenu
     */
    protected function generateServiceMenu(array $overwrites): LtcsProjectServiceMenu
    {
        $attrs = [
            'createdAt' => Carbon::now(),
        ];
        return LtcsProjectServiceMenu::create($overwrites + $attrs);
    }
}
