<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Domain\Common\Carbon;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Project\DwsProjectServiceMenu;
use Domain\Project\DwsProjectServiceMenuRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Illuminate\Database\Seeder;

/**
 * 障害福祉サービス計画：サービス内容 Seeder
 */
class DwsProjectServiceMenuSeeder extends Seeder
{
    private DwsProjectServiceMenuRepository $repository;
    private TransactionManager $transaction;

    /**
     * ServiceMenuSeeder constructor.
     *
     * @param \Domain\Project\DwsProjectServiceMenuRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsProjectServiceMenuRepository $repository,
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
     * @return array|\Domain\Project\DwsProjectServiceMenu[]
     */
    protected function serviceMenus(): array
    {
        return [
            $this->generateServiceMenu([
                'id' => 1,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'サービス準備・記録等',
                'displayName' => 'サービス準備・記録等',
                'sortOrder' => 1,
            ]),
            $this->generateServiceMenu([
                'id' => 2,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '排泄介助',
                'displayName' => '排泄介助',
                'sortOrder' => 2,
            ]),
            $this->generateServiceMenu([
                'id' => 3,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '食事介助',
                'displayName' => '食事介助',
                'sortOrder' => 3,
            ]),
            $this->generateServiceMenu([
                'id' => 4,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '入浴介助',
                'displayName' => '入浴介助',
                'sortOrder' => 4,
            ]),
            $this->generateServiceMenu([
                'id' => 5,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '清拭介助',
                'displayName' => '清拭介助',
                'sortOrder' => 5,
            ]),
            $this->generateServiceMenu([
                'id' => 6,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '移乗介助',
                'displayName' => '移乗介助',
                'sortOrder' => 6,
            ]),
            $this->generateServiceMenu([
                'id' => 7,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '外出介助',
                'displayName' => '外出介助',
                'sortOrder' => 7,
            ]),
            $this->generateServiceMenu([
                'id' => 8,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '外出準備介助',
                'displayName' => '外出準備介助',
                'sortOrder' => 8,
            ]),
            $this->generateServiceMenu([
                'id' => 9,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '帰宅受入介助',
                'displayName' => '帰宅受入介助',
                'sortOrder' => 9,
            ]),
            $this->generateServiceMenu([
                'id' => 10,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'その他移動・歩行介助',
                'displayName' => 'その他移動・歩行介助',
                'sortOrder' => 10,
            ]),
            $this->generateServiceMenu([
                'id' => 11,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '起床介助',
                'displayName' => '起床介助',
                'sortOrder' => 11,
            ]),
            $this->generateServiceMenu([
                'id' => 12,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '就寝介助',
                'displayName' => '就寝介助',
                'sortOrder' => 12,
            ]),
            $this->generateServiceMenu([
                'id' => 13,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '体位変換',
                'displayName' => '体位変換',
                'sortOrder' => 13,
            ]),
            $this->generateServiceMenu([
                'id' => 14,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'その他起床・就寝介助',
                'displayName' => 'その他起床・就寝介助',
                'sortOrder' => 14,
            ]),
            $this->generateServiceMenu([
                'id' => 15,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '服薬',
                'displayName' => '服薬',
                'sortOrder' => 15,
            ]),
            $this->generateServiceMenu([
                'id' => 16,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '服薬管理',
                'displayName' => '服薬管理',
                'sortOrder' => 16,
            ]),
            $this->generateServiceMenu([
                'id' => 17,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '医療的ケア',
                'displayName' => '医療的ケア',
                'sortOrder' => 17,
            ]),
            $this->generateServiceMenu([
                'id' => 18,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '自立への声かけと見守り',
                'displayName' => '自立への声かけと見守り',
                'sortOrder' => 18,
            ]),
            $this->generateServiceMenu([
                'id' => 19,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '意欲・関心の引き出し',
                'displayName' => '意欲・関心の引き出し',
                'sortOrder' => 19,
            ]),
            $this->generateServiceMenu([
                'id' => 20,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '共に行う家事（掃除）',
                'displayName' => '共に行う家事（掃除）',
                'sortOrder' => 20,
            ]),
            $this->generateServiceMenu([
                'id' => 21,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '共に行う家事（洗濯）',
                'displayName' => '共に行う家事（洗濯）',
                'sortOrder' => 21,
            ]),
            $this->generateServiceMenu([
                'id' => 22,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '共に行う家事（調理）',
                'displayName' => '共に行う家事（調理）',
                'sortOrder' => 22,
            ]),
            $this->generateServiceMenu([
                'id' => 23,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'その他自立支援',
                'displayName' => 'その他自立支援',
                'sortOrder' => 23,
            ]),
            $this->generateServiceMenu([
                'id' => 24,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '掃除',
                'displayName' => '掃除',
                'sortOrder' => 24,
            ]),
            $this->generateServiceMenu([
                'id' => 25,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '洗濯',
                'displayName' => '洗濯',
                'sortOrder' => 25,
            ]),
            $this->generateServiceMenu([
                'id' => 26,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '居室整理（寝具の手入れ）',
                'displayName' => '居室整理（寝具の手入れ）',
                'sortOrder' => 26,
            ]),
            $this->generateServiceMenu([
                'id' => 27,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '居室整理（衣類）',
                'displayName' => '居室整理（衣類）',
                'sortOrder' => 27,
            ]),
            $this->generateServiceMenu([
                'id' => 28,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '調理配下膳',
                'displayName' => '調理配下膳',
                'sortOrder' => 28,
            ]),
            $this->generateServiceMenu([
                'id' => 29,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '買い物',
                'displayName' => '買い物',
                'sortOrder' => 29,
            ]),
            $this->generateServiceMenu([
                'id' => 30,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'コミュニケーション支援',
                'displayName' => 'コミュニケーション支援',
                'sortOrder' => 30,
            ]),
            $this->generateServiceMenu([
                'id' => 31,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '生活等に関する相談・助言',
                'displayName' => '生活等に関する相談・助言',
                'sortOrder' => 31,
            ]),
            $this->generateServiceMenu([
                'id' => 32,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => '常時付添いを必要とする見守り的援助',
                'displayName' => '常時付添いを必要とする見守り的援助',
                'sortOrder' => 32,
            ]),
            $this->generateServiceMenu([
                'id' => 33,
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'name' => 'その他',
                'displayName' => 'その他',
                'sortOrder' => 33,
            ]),
            $this->generateServiceMenu([
                'id' => 34,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => 'サービス準備・記録等',
                'displayName' => 'サービス準備・記録等',
                'sortOrder' => 34,
            ]),
            $this->generateServiceMenu([
                'id' => 35,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '排泄介助',
                'displayName' => '排泄介助',
                'sortOrder' => 35,
            ]),
            $this->generateServiceMenu([
                'id' => 36,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '食事介助',
                'displayName' => '食事介助',
                'sortOrder' => 36,
            ]),
            $this->generateServiceMenu([
                'id' => 37,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '専門的配慮をもって行う調理',
                'displayName' => '専門的配慮をもって行う調理',
                'sortOrder' => 37,
            ]),
            $this->generateServiceMenu([
                'id' => 38,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '清拭（全身清拭）',
                'displayName' => '清拭（全身清拭）',
                'sortOrder' => 38,
            ]),
            $this->generateServiceMenu([
                'id' => 39,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '部分浴',
                'displayName' => '部分浴',
                'sortOrder' => 39,
            ]),
            $this->generateServiceMenu([
                'id' => 40,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '全身浴',
                'displayName' => '全身浴',
                'sortOrder' => 40,
            ]),
            $this->generateServiceMenu([
                'id' => 41,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '洗面等',
                'displayName' => '洗面等',
                'sortOrder' => 41,
            ]),
            $this->generateServiceMenu([
                'id' => 42,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '身体整容',
                'displayName' => '身体整容',
                'sortOrder' => 42,
            ]),
            $this->generateServiceMenu([
                'id' => 43,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '更衣介助',
                'displayName' => '更衣介助',
                'sortOrder' => 43,
            ]),
            $this->generateServiceMenu([
                'id' => 44,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '体位交換',
                'displayName' => '体位交換',
                'sortOrder' => 44,
            ]),
            $this->generateServiceMenu([
                'id' => 45,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '移動・移乗介助',
                'displayName' => '移動・移乗介助',
                'sortOrder' => 45,
            ]),
            $this->generateServiceMenu([
                'id' => 46,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '通院・外出介助',
                'displayName' => '通院・外出介助',
                'sortOrder' => 36,
            ]),
            $this->generateServiceMenu([
                'id' => 47,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '起床介助',
                'displayName' => '起床介助',
                'sortOrder' => 47,
            ]),
            $this->generateServiceMenu([
                'id' => 48,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '就寝介助',
                'displayName' => '就寝介助',
                'sortOrder' => 48,
            ]),
            $this->generateServiceMenu([
                'id' => 49,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '服薬介助',
                'displayName' => '服薬介助',
                'sortOrder' => 49,
            ]),
            $this->generateServiceMenu([
                'id' => 50,
                'category' => DwsProjectServiceCategory::physicalCare(),
                'name' => '自立生活支援・重度化防止のための見守り的援助',
                'displayName' => '自立生活支援・重度化防止のための見守り的援助',
                'sortOrder' => 50,
            ]),
            $this->generateServiceMenu([
                'id' => 51,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => 'サービス準備・記録等',
                'displayName' => 'サービス準備・記録等',
                'sortOrder' => 51,
            ]),
            $this->generateServiceMenu([
                'id' => 52,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => '掃除',
                'displayName' => '掃除',
                'sortOrder' => 52,
            ]),
            $this->generateServiceMenu([
                'id' => 53,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => '洗濯',
                'displayName' => '洗濯',
                'sortOrder' => 53,
            ]),
            $this->generateServiceMenu([
                'id' => 54,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => 'ベッドメイク',
                'displayName' => 'ベッドメイク',
                'sortOrder' => 54,
            ]),
            $this->generateServiceMenu([
                'id' => 55,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => '衣類の整理・被服の補修',
                'displayName' => '衣類の整理・被服の補修',
                'sortOrder' => 55,
            ]),
            $this->generateServiceMenu([
                'id' => 56,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => '一般的な調理、配下膳',
                'displayName' => '一般的な調理、配下膳',
                'sortOrder' => 56,
            ]),
            $this->generateServiceMenu([
                'id' => 57,
                'category' => DwsProjectServiceCategory::housework(),
                'name' => '買い物・薬の受け取り',
                'displayName' => '買い物・薬の受け取り',
                'sortOrder' => 57,
            ]),
        ];
    }

    /**
     * インスタンスを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Project\DwsProjectServiceMenu
     */
    protected function generateServiceMenu(array $overwrites): DwsProjectServiceMenu
    {
        $attrs = [
            'createdAt' => Carbon::now(),
        ];
        return DwsProjectServiceMenu::create($overwrites + $attrs);
    }
}
