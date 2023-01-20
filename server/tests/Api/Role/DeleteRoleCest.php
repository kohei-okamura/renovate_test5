<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Role;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Role\Role;
use Domain\Staff\Staff;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Role delete のテスト.
 * DELETE /roles/{id}
 */
class DeleteRoleCest extends RoleTest
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
        $usedRoleIds = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->flatMap(fn (Staff $x): array => $x->roleIds)
            ->toArray();
        $id = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => $x->organizationId === $staff->organizationId) // Exampleで不整合があるのでfilterする
            ->filter(fn (Role $x): bool => !in_array($x->id, $usedRoleIds, true)) // 未使用のIDを探す
            ->head()->id;

        $I->sendDELETE("/roles/{$id}");

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '権限情報が削除されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * IDがStaffで使用中の場合に、400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenIdInUse(ApiTester $I)
    {
        $I->wantTo('failed with Bad Request when ID in use');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $usedRoleIds = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->flatMap(fn (Staff $x): array => $x->roleIds)
            ->toArray();
        $id = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => in_array($x->id, $usedRoleIds, true)) // 使用中のIDを探す
            ->head()->id;

        $I->sendDELETE("/roles/{$id}");

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * IDが存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendDELETE("/roles/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Role[{$id}] not found");
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
        $role = $this->examples->roles[0];

        $I->sendDELETE("/roles/{$role->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 指定されたロールが異なる事業者に紐づく場合、404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenSpecifyRoleFromOtherOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when specify role from other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $role = $this->examples->roles[2];

        $I->sendDELETE("/roles/{$role->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
    }
}
