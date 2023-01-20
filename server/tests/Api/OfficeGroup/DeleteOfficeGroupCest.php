<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OfficeGroup;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup deleteのテスト.
 * DELETE /office-groups/{id}
 */
class DeleteOfficeGroupCest extends OfficeGroupTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API Call.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->officeGroups[2]->id;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが削除されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 存在しないIDを指定すると404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenSpecifyNonExistentId(ApiTester $I)
    {
        $I->wantTo('failed with Not Found when specify non-existent id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OfficeGroup[{$id}] not found");
    }

    /**
     * 事業所が紐づく事業所グループのIDを指定すると400を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenTheSpecifiedIdIsRelatedToSomeOffices(ApiTester $I)
    {
        $I->wantTo('failed with bad request when the specified id is related to some offices');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->officeGroups[0]->id;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson([
            'errors' => ['id' => ['指定した事業所グループに紐づく事業所が存在しています。']],
        ]);
    }

    /**
     * 親事業所グループである事業所グループのIDを指定すると400を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenTheSpecifiedIdIsParentOfSomeOfficeGroupss(ApiTester $I)
    {
        $I->wantTo('failed with bad request when the specified id is parent of some office-groups');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->officeGroups[0]->id;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson([
            'errors' => ['id' => ['指定した事業所グループを親とする事業所グループが存在しています。']],
        ]);
    }

    /**
     * IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = 'id';

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $id = $this->examples->officeGroups[0]->id;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 異なる事業者のエンティティの場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenAccessingEntityFromDifferentOrganization(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when accessing entity from different organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->officeGroups[3]->id;

        $I->sendDELETE("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OfficeGroup[{$id}] not found");
    }
}
