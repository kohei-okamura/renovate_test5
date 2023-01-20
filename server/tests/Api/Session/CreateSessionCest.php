<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Session;

use ApiTester;
use Codeception\Util\HttpCode;
use DateTime;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Lib\Json;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Session create のテスト
 *
 * POST /sessions
 */
class CreateSessionCest extends Test
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出し テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];

        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
        ]);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $expected = $staff;
        $roles = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => in_array($x->id, $staff->roleIds, true));
        $permissions = $roles->exists(fn (Role $x): bool => $x->isSystemAdmin)
            ? Seq::fromArray(Permission::all())
                ->map(fn (Permission $x): string => $x->value())
                ->toArray()
            : $roles->flatMap(fn (Role $x): array => $x->permissions)
                ->distinctBy(fn (Permission $permission): string => $permission->value())
                ->map(fn (Permission $x): string => $x->value())
                ->toArray();
        // ネストされたJSONは正しく判定できないようなので個別に実施
        // see https://github.com/Codeception/phpunit-wrapper/issues/61
        $actual = Json::decode($I->grabResponse(), true);
        assertSame([
            'auth' => [
                'isSystemAdmin' => $roles->exists(fn (Role $x): bool => $x->isSystemAdmin),
                'permissions' => $permissions,
                'staff' => [
                    'id' => $expected->id,
                    'employeeNumber' => $expected->employeeNumber,
                    'bankAccountId' => $expected->bankAccountId,
                    'name' => $expected->name->toAssoc(),
                    'sex' => $expected->sex->value(),
                    'birthday' => $expected->birthday->format(DateTime::ISO8601),
                    'addr' => [
                        'postcode' => $expected->addr->postcode,
                        'prefecture' => $expected->addr->prefecture->value(),
                        'city' => $expected->addr->city,
                        'street' => $expected->addr->street,
                        'apartment' => $expected->addr->apartment,
                    ],
                    'location' => $expected->location->toAssoc(),
                    'tel' => $expected->tel,
                    'fax' => $expected->fax,
                    'email' => $expected->email,
                    'certifications' => $this->domainToArray(Seq::fromArray($expected->certifications)),
                    'roleIds' => $expected->roleIds,
                    'officeIds' => $expected->officeIds,
                    'officeGroupIds' => $expected->officeGroupIds,
                    'isVerified' => $expected->isVerified,
                    'status' => $expected->status->value(),
                    'isEnabled' => $expected->isEnabled,
                    'createdAt' => $expected->createdAt->format(DateTime::ISO8601),
                    'updatedAt' => $expected->updatedAt->format(DateTime::ISO8601),
                ],
            ],
        ], $actual);
        $I->seeHttpHeader('Set-Cookie');

        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::INFO,
            'スタッフがログインしました',
            ['organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
    }

    /**
     * rememberMeを指定する テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithRememberMe(ApiTester $I)
    {
        $I->wantTo('succeed API Call with rememberMe.');

        $staff = $this->examples->staffs[2];

        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
            'rememberMe' => true,
        ]);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeSetCookie('session');
        $I->seeSetCookie('e2e_token');
        $I->dontSeeSetCookie('rememberToken');

        $I->seeLogCount(2);
        $I->seeLogMessage(
            0,
            LogLevel::INFO,
            'スタッフがログインしました',
            ['organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
        $I->seeLogMessage(
            1,
            LogLevel::INFO,
            'スタッフリメンバートークンが登録されました',
            ['id' => '*', 'organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
    }

    /**
     * 存在しない事業者コードを指定してアクセスすると404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenNoExistOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when no exist Organization.');

        $I->haveHttpHeader('Host', 'unknown.zinger-e2e.test');

        $staff = $this->examples->staffs[2];

        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
            'rememberMe' => true,
        ]);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'Organization not found');
    }

    /**
     * メールアドレス認証がすんでないユーザは401が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithUnauthorizedWhenIsVerificationFalse(ApiTester $I)
    {
        $I->wantTo('fail with Unauthorized when isVerification false');

        $staff = $this->examples->staffs[17];

        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
        ]);

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeLogCount(0);
    }
}
