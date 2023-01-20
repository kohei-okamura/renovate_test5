<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Office;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Faker\Generator;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Office create のテスト
 * POST /offices
 */
class CreateOfficeCest extends OfficeTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * API正常呼び出し テスト
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('offices', $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '事業所が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '事業所が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 自社以外で登録できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithPurposeNotInternal(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call with Purpose not internal');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $param = $this->defaultParam();
        $param['purpose'] = Purpose::external()->value();

        $I->sendPOST('offices', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
    }

    /**
     * 存在しない住所を登録するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedProcessNormallyWhenNoResolveLocation(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed process normally when no resolve location');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = [
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '米花市米花区',
            'street' => '米花町２丁目21',
            'apartment' => '',
        ] + $this->defaultParam();

        $I->sendPOST('offices', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '住所を特定できませんでした。', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // ここから下はWeb側で出力されたログ
        $I->seeLogMessage(2, LogLevel::INFO, '事業所が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendPOST('offices', $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成
     *
     * @return array
     */
    private function defaultParam(): array
    {
        $faker = app(Generator::class);
        $office = $faker->officeName();

        return [
            'name' => $office['name'],
            'abbr' => $office['abbr'],
            'phoneticName' => $office['phonetic_name'] . 'クリエイトテスト',
            'corporationName' => '事業所テスト',
            'phoneticCorporationName' => 'ジギョウショテスト',
            'purpose' => $faker->randomElement(Purpose::all())->value(),
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '中野区',
            'street' => '本町１丁目32-2',
            'tel' => '03-1234-5678',
            'fax' => '03-9876-5432',
            'email' => $faker->emailAddress,
            'officeGroupId' => $this->examples->officeGroups[0]->id,
            'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
            'dwsGenericService' => [
                'dwsAreaGradeId' => $this->examples->dwsAreaGrades[0]->id,
                'code' => $this->examples->dwsAreaGrades[0]->code,
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ],
            'dwsCommAccompanyService' => [
                'code' => $this->examples->dwsAreaGrades[0]->code,
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ],
            'ltcsCareManagementService' => [
                'ltcsAreaGradeId' => $this->examples->ltcsAreaGrades[0]->id,
                'code' => $this->examples->ltcsAreaGrades[0]->code,
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ],
            'ltcsHomeVisitLongTermCareService' => [
                'ltcsAreaGradeId' => $this->examples->ltcsAreaGrades[0]->id,
                'code' => $this->examples->ltcsAreaGrades[0]->code,
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ],
            'ltcsCompHomeVisitingService' => [
                'code' => $faker->numerify(str_repeat('#', 20)),
                'openedOn' => Carbon::instance($faker->dateTime)->startOfDay(),
                'designationExpiredOn' => Carbon::instance($faker->dateTime)->startOfDay(),
            ],
            'status' => OfficeStatus::inOperation()->value(),
        ];
    }
}
