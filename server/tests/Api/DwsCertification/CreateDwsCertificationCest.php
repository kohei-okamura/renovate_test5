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
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsCertification Create のテスト.
 * POST /users/{userId}/dws-certifications
 */
class CreateDwsCertificationCest extends DwsCertificationTest
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

        $I->sendPOST(
            "users/{$this->examples->users[0]->id}/dws-certifications",
            $this->defaultParam($dwsCertification)
        );

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
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

        $I->sendPOST("users/{$this->examples->users[0]->id}/dws-certifications", $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 当該契約支給量によるサービス提供終了日がない場合でも登録ができるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenNoContractEndDate(ApiTester $I)
    {
        $I->wantTo('succeed API call when no contract end date');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[21];
        $param = $this->defaultParam($dwsCertification);
        Arr::forget($param, 'agreements.0.expiredOn');

        $I->sendPOST("users/{$this->examples->users[0]->id}/dws-certifications", $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス受給者証が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 利用者が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsCertification = $this->examples->dwsCertifications[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/dws-certifications", $this->defaultParam($dwsCertification));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
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

        $I->sendPOST(
            "users/{$userId}/dws-certifications/",
            $this->defaultParam($this->examples->dwsCertifications[0]->copy(['agreements' => []])),
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
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

        $I->sendPOST("users/{$userId}/dws-certifications", $this->defaultParam($dwsCertification));

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

        $I->sendPOST(
            "users/{$this->examples->users[0]->id}/dws-certifications",
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
