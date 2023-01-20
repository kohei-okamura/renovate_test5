<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Office;

use ApiTester;
use Codeception\Util\HttpCode;
use DateTime;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Illuminate\Support\Arr;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Office update のテスト.
 * PUT /offices/{id}
 */
class UpdateOfficeCest extends OfficeTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[4];
        $id = $office->id;

        $I->sendPUT("offices/{$id}", $this->buildParam($office));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(3);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '事業所が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '事業所が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $updated = $I->grabResponseArray();

        // 更新後のチェック
        $I->sendGET("/offices/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時のレスポンスでは、位置情報がリセットされるため
        $expected['office']['location']['lat'] = 0;
        $expected['office']['location']['lng'] = 0;

        assertEquals($expected['office'], $updated['office']);
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
        $office = $this->examples->offices[4]->copy([
            'purpose' => Purpose::external(),
        ]);
        $id = $office->id;

        $I->sendPUT("offices/{$id}", $this->buildParam($office));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(3);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '事業所が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '事業所が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $expected = [
            'id' => $office->id,
            'name' => $office->name,
            'abbr' => $office->abbr,
            'phoneticName' => $office->phoneticName,
            'corporationName' => $office->corporationName,
            'phoneticCorporationName' => $office->phoneticCorporationName,
            'purpose' => $office->purpose->value(),
            'addr' => [
                'postcode' => '164-0012',
                'prefecture' => Prefecture::tokyo()->value(),
                'city' => '中野区',
                'street' => '本町１丁目32-2',
                'apartment' => '',
            ],
            'location' => [
                'lat' => 0,
                'lng' => 0,
            ],
            'tel' => $office->tel,
            'fax' => $office->fax,
            'email' => '',
            'qualifications' => Seq::fromArray($office->qualifications)
                ->map(fn (OfficeQualification $x): string => $x->value())
                ->toArray(),
            'officeGroupId' => null,
            'dwsGenericService' => $this->hasQualifications(
                $office,
                OfficeQualification::dwsHomeHelpService(),
                OfficeQualification::dwsVisitingCareForPwsd(),
                OfficeQualification::dwsOthers()
            )
                ? $this->officeDwsGenericService($office)
                : null,
            'dwsCommAccompanyService' => $this->hasQualifications($office, OfficeQualification::dwsCommAccompany())
                ? $this->dwsCommAccompanyService($office)
                : null,
            'ltcsCareManagementService' => $this->hasQualifications($office, OfficeQualification::ltcsCareManagement())
                ? $this->ltcsCareManagementService($office)
                : null,
            'ltcsHomeVisitLongTermCareService' => $this->hasQualifications($office, OfficeQualification::ltcsHomeVisitLongTermCare())
                ? $this->ltcsHomeVisitLongTermCareService($office)
                : null,
            'ltcsCompHomeVisitingService' => $this->hasQualifications($office, OfficeQualification::ltcsCompHomeVisiting())
                ? $this->ltcsCompHomeVisitingService($office)
                : null,
            'status' => $office->status->value(),
            'isEnabled' => $office->isEnabled,
            'createdAt' => $office->createdAt->format(DateTime::ISO8601),
            'updatedAt' => Carbon::now()->format(DateTime::ISO8601),
        ];
        // 更新された値が空になっていること
        $I->seeResponseJson(['office' => $expected]);
        $updated = $I->grabResponseArray();

        // 更新後のチェック
        $I->sendGET("/offices/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時のレスポンスでは、位置情報がリセットされるため
        $expected['office']['location']['lat'] = 0;
        $expected['office']['location']['lng'] = 0;

        assertEquals($expected['office'], $updated['office']);
    }

    /**
     * ID存在しないテスト.
     *
     * @param ApiTester $I
     */
    public function failsWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('fails with NOT FOUND when id not exists.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[4];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("offices/{$id}", $this->buildParam($office));

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$id}) not found");
    }

    /**
     * 位置情報が特定できなかったときlocationに{lat:0,lng:0)がセットされるテスト.
     *
     * @param ApiTester $I
     */
    public function setLocationToZeroWhenCannotResolveLocation(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('set location to {lat:0,lng:0} when cannot resolve location');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[4];
        $id = $office->id;

        $addr = [
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '米花市米花区',
            'street' => '米花町２丁目21',
            'apartment' => '',
        ];
        $param = $addr + $this->domainToArray($office);
        $I->sendPUT("offices/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(3);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '住所を特定できませんでした。', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '事業所が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $expected = [
            'location' => [
                'lat' => 0,
                'lng' => 0,
            ],
        ];
        $I->sendGET("offices/{$office->id}");
        $I->seeResponseContainsJson($expected);
    }

    /**
     * IDの情報が、所属するOrganizationのものでないテスト.
     *
     * @param ApiTester $I
     */
    public function failsWithNotFoundWhenIdNotHaveMyself(ApiTester $I)
    {
        $I->wantTo('fails with NOT FOUND when id not have myself.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $office = $this->examples->offices[1];

        $I->sendPUT("offices/{$office->id}", $this->buildParam($office));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$office->id}) not found");
    }

    /**
     * 権限のないOfficeのIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not in permitted office.');

        $I->actingAs($this->examples->staffs[28]);
        $office = $this->examples->offices[2];
        $id = $office->id;

        $I->sendPUT("offices/{$id}", $this->buildParam($office));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$id}) not found");
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
        $office = $this->examples->offices[0];
        $id = $office->id;

        $I->sendPUT("offices/{$id}", $this->buildParam($office));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * パラメータ組み立て.
     *
     * @param \Domain\Office\Office $office
     * @throws \JsonException
     * @return array
     */
    private function buildParam(Office $office): array
    {
        $value = $this->domainToArray($office);
        Arr::forget($value, 'addr');
        return $value + [
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '中野区',
            'street' => '本町１丁目32-2',
            'apartment' => '',
        ];
    }

    /**
     * 特定の指定区分がパラメータに含まれるか判定.
     *
     * @param \Domain\Office\Office $office
     * @param \Domain\Office\OfficeQualification ...$qualifications
     * @return bool
     */
    private function hasQualifications(Office $office, OfficeQualification ...$qualifications): bool
    {
        return isset($office->qualifications)
            && Seq::fromArray($qualifications)
                ->find(fn (OfficeQualification $x): bool => in_array($x, $office->qualifications, true))
                ->nonEmpty();
    }

    /**
     * 事業区分が自社かどうか判定.
     *
     * @param \Domain\Office\Office $office
     * @return bool
     */
    private function isInternal(Office $office): bool
    {
        return Purpose::isValid($office->purpose) && Purpose::from($office->purpose) === Purpose::internal();
    }

    /**
     * 事業所：障害福祉サービスを生成.
     *
     * @param \Domain\Office\Office $office
     * @return array
     */
    private function officeDwsGenericService(Office $office): array
    {
        return [
            'code' => $office->dwsGenericService->code,
            'openedOn' => $this->isInternal($office)
                ? $office->dwsGenericService->openedOn
                : null,
            'designationExpiredOn' => $this->isInternal($office)
                ? $office->dwsGenericService->designationExpiredOn
                : null,
            'dwsAreaGradeId' => $this->isInternal($office)
                ? $office->dwsGenericService->dwsAreaGradeId
                : null,
        ];
    }

    /**
     * 事業所：障害福祉サービス（地域生活支援事業・移動支援）を生成.
     *
     * @param \Domain\Office\Office $office
     * @return array
     */
    private function dwsCommAccompanyService(Office $office): array
    {
        return [
            'code' => $office->dwsCommAccompanyService->code,
            'openedOn' => $this->isInternal($office)
                ? $office->dwsCommAccompanyService->openedOn
                : null,
            'designationExpiredOn' => $this->isInternal($office)
                ? $office->dwsCommAccompanyService->designationExpiredOn
                : null,
        ];
    }

    /**
     * 事業所：介護保険サービス：居宅介護支援を生成.
     *
     * @param \Domain\Office\Office $office
     * @return array
     */
    private function ltcsCareManagementService(Office $office): array
    {
        return [
            'code' => $office->ltcsCareManagementService->code,
            'openedOn' => $this->isInternal($office)
                ? $office->ltcsCareManagementService->openedOn
                : null,
            'designationExpiredOn' => $this->isInternal($office)
                ? $office->ltcsCareManagementService->designationExpiredOn
                : null,
            'ltcsAreaGradeId' => $this->isInternal($office)
                ? $office->ltcsCareManagementService->ltcsAreaGradeId
                : null,
        ];
    }

    /**
     * 事業所：介護保険サービス：訪問介護を生成.
     *
     * @param \Domain\Office\Office $office
     * @return array
     */
    private function ltcsHomeVisitLongTermCareService(Office $office): array
    {
        return [
            'code' => $office->ltcsHomeVisitLongTermCareService->code,
            'openedOn' => $this->isInternal($office)
                ? $office->ltcsHomeVisitLongTermCareService->openedOn
                : null,
            'designationExpiredOn' => $this->isInternal($office)
                ? $office->ltcsHomeVisitLongTermCareService->designationExpiredOn
                : null,
            'ltcsAreaGradeId' => $this->isInternal($office)
                ? $office->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId
                : null,
        ];
    }

    /**
     * 事業所：介護保険サービス：訪問型サービス（総合事業）を生成.
     *
     * @param \Domain\Office\Office $office
     * @return array
     */
    private function ltcsCompHomeVisitingService(Office $office): array
    {
        return [
            'code' => $office->ltcsCompHomeVisitingService->code,
            'openedOn' => $this->isInternal($office)
                ? $office->ltcsCompHomeVisitingService->openedOn
                : null,
            'designationExpiredOn' => $this->isInternal($office)
                ? $office->ltcsCompHomeVisitingService->designationExpiredOn
                : null,
        ];
    }
}
