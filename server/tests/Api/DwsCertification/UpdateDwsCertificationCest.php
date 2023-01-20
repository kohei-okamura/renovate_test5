<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsCertification;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsLevel;
use Illuminate\Support\Arr;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsCertification Update のテスト.
 * PUT /users/{userId}/dws-certifications/{id}
 */
class UpdateDwsCertificationCest extends DwsCertificationTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];

        $I->sendPUT(
            "users/{$dwsCertification->userId}/dws-certifications/{$dwsCertification->id}",
            $this->defaultParam($dwsCertification)
        );

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が更新されました', [
            'id' => $dwsCertification->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$dwsCertification->userId}/dws-certifications/{$dwsCertification->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 児童情報がないテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenNoChildInfos(ApiTester $I)
    {
        $I->wantTo('succeed API call when no child infos');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];
        $param = $this->defaultParam($dwsCertification);
        Arr::forget($param, 'child');

        $I->sendPUT("users/{$dwsCertification->userId}/dws-certifications/{$dwsCertification->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が更新されました', [
            'id' => $dwsCertification->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 当該契約支給量によるサービス提供終了日がない場合でも更新ができるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenNoExpiredOn(ApiTester $I)
    {
        $I->wantTo('succeed API call when no expiredOn');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[21];
        $param = $this->defaultParam($dwsCertification);
        Arr::forget($param, 'agreements.0.expiredOn');

        $I->sendPUT("users/{$dwsCertification->userId}/dws-certifications/{$dwsCertification->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が更新されました', [
            'id' => $dwsCertification->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * UserIdが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "users/{$userId}/dws-certifications/{$dwsCertification->id}",
            $this->defaultParam($dwsCertification)
        );

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * 利用者IDが同じ事業者に存在しない場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];
        $userId = $this->examples->users[14]->id;

        $I->sendPUT(
            "users/{$userId}/dws-certifications/{$dwsCertification->id}",
            $this->defaultParam($dwsCertification)
        );

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * IDが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "users/{$dwsCertification->userId}/dws-certifications/{$id}",
            $this->defaultParam($dwsCertification)
        );

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsCertification({$id}) not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * アクセス可能なOfficeと契約がない利用者を指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $userId = $this->examples->users[1]->id;
        $dwsCertification = $this->examples->dwsCertifications[0];
        $id = $dwsCertification->id;

        $I->sendPUT(
            "users/{$userId}/dws-certifications/{$id}",
            $this->defaultParam($dwsCertification->copy(['agreements' => []])),
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $dwsCertification = $this->examples->dwsCertifications[0];

        $I->sendPUT(
            "users/{$dwsCertification->userId}/dws-certifications/{$dwsCertification->id}",
            $this->defaultParam($dwsCertification)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * テストパラメータの設定.
     *
     * @param \Domain\DwsCertification\DwsCertification $dwsCertification
     * @return array
     */
    private function defaultParam(DwsCertification $dwsCertification): array
    {
        $agreement = DwsCertificationAgreement::create([
            'indexNumber' => 1,
            'officeId' => $this->examples->offices[0]->id,
            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
            'paymentAmount' => 300,
            'agreedOn' => Carbon::parse('2020-1-1'),
            'expiredOn' => Carbon::parse('2030-10-10'),
        ]);
        $param = $this->domainToArray($dwsCertification->copy([
            'dwsLevel' => DwsLevel::level6(),
            'agreements' => [$agreement],
        ]));
        $param['isSubjectOfComprehensiveSupport'] = (int)$param['isSubjectOfComprehensiveSupport'];
        return $param;
    }
}
