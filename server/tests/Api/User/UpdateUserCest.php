<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\User;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Prefecture;
use Domain\User\BillingDestination;
use Domain\User\User;
use Illuminate\Support\Arr;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * User update のテスト.
 * /PUST users/{id}
 */
class UpdateUserCest extends UserTest
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
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = $user->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時 Responseの緯度経度は0になる
        $expected['user']['location']['lat'] = 0;
        $expected['user']['location']['lng'] = 0;

        assertSame($expected['user'], $actual['user']);
    }

    /**
     * 電話番号が0件で更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithoutTel(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call without tel');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = $user->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($user->copy([
            'contacts' => [
                Contact::create([
                    'tel' => null,
                    'relationship' => ContactRelationship::family(),
                    'name' => '田中花子',
                ]),
            ],
        ])));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時 Responseの緯度経度は0になる
        $expected['user']['location']['lat'] = 0;
        $expected['user']['location']['lng'] = 0;

        assertSame($expected['user'], $actual['user']);
    }

    /**
     * 請求先情報の「建物名」がない場合に更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithoutApartmentInBillingDestination(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call without apartment in billing destination');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = $user->id;

        $params = tap($this->defaultParam($user), function (array &$defaultParams): void {
            Arr::forget($defaultParams, 'billingDestination.apartment');
        });

        $I->sendPUT("users/{$id}", $params);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時 Responseの緯度経度は0になる
        $expected['user']['location']['lat'] = 0;
        $expected['user']['location']['lng'] = 0;

        assertSame($expected['user'], $actual['user']);
    }

    /**
     * 請求先が「本人」の場合に更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenDestinationIsTheirself(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call when destination is theirself');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $user = $this->examples->users[0]->copy([
            'billingDestination' => $this->examples->users[0]->billingDestination->copy([
                'destination' => BillingDestination::theirself(),
            ]),
        ]);
        $id = $user->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);

        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        // 住所更新時 Responseの緯度経度は0になる
        $expected['user']['location']['lat'] = 0;
        $expected['user']['location']['lng'] = 0;

        assertSame($expected['user'], $actual['user']);
    }

    /**
     * isEnabledを更新するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithUpdatingIsEnabled(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call with updating isEnabled.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $user = $this->examples->users[0]->copy(['isEnabled' => false]);
        $id = $user->id;

        $I->sendPUT("users/{$id}", ['isEnabled' => false] + $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // JOBのログが先に出力される
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);
        $actual = $I->grabResponseArray();
        $I->assertFalse($actual['user']['isEnabled']);
    }

    /**
     * 住所更新がない場合 Google Geocoding API が呼ばれないテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithoutAddrUpdate(ApiTester $I)
    {
        $I->wantTo('succeed API call without addr update');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = $user->id;

        $param = [
            'postcode' => $user->addr->postcode,
            'prefecture' => $user->addr->prefecture->value(),
            'city' => $user->addr->city,
            'street' => $user->addr->street,
            'apartment' => $user->addr->apartment,
        ] + $this->defaultParam($user);

        $I->sendPUT("users/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(1);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected['user'], $actual['user']);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when Invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("users/{$id}", $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(0, LogLevel::WARNING, "User({$id}) not found");
        $I->seeLogCount(1);
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
        $user = $this->examples->users[0];
        $id = $user->id;
        $addr = [
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '米花市',
            'street' => '米花区米花町2-21',
            'apartment' => '',
        ];
        $param = $addr + $this->defaultParam($user);

        $I->sendPUT("users/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => "{$id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
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

        $expected = [
            'location' => [
                'lat' => 0,
                'lng' => 0,
            ],
        ];
        $I->sendGET("users/{$id}");
        $I->seeResponseContainsJson($expected);
    }

    /**
     * 異なる事業者の利用者を更新しようとすると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when User is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $id = $this->examples->users[14]->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User({$id}) not found");
    }

    /**
     * アクセス可能なOfficeでない（Officeと契約がない）利用者を更新すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->users[1]->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($this->examples->users[0]));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(0, LogLevel::WARNING, "User({$id}) not found");
        $I->seeLogCount(1);
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
        $user = $this->examples->users[0];
        $id = $user->id;

        $I->sendPUT("users/{$id}", $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param User $user
     * @return array
     */
    private function defaultParam(User $user): array
    {
        $value = $this->domainToArray($user);
        return [
            'familyName' => $value['name']['familyName'],
            'givenName' => $value['name']['givenName'],
            'phoneticFamilyName' => $value['name']['phoneticFamilyName'],
            'phoneticGivenName' => $value['name']['phoneticGivenName'],
            'sex' => $value['sex'],
            'birthday' => $value['birthday'],
            'postcode' => '164-0012',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '中野区',
            'street' => '本町１丁目32-2',
            'apartment' => '',
            'contacts' => Seq::fromArray($value['contacts'])
                ->map(fn (array $x): array => [
                    'tel' => $x['tel'] ?? '',
                    'relationship' => $x['relationship'],
                    'name' => $x['name'],
                ])
                ->toArray(),
            'billingDestination' => [
                'destination' => $value['billingDestination']['destination'],
                'paymentMethod' => $value['billingDestination']['paymentMethod'],
                'contractNumber' => $value['billingDestination']['contractNumber'],
                'corporationName' => $value['billingDestination']['corporationName'],
                'agentName' => $value['billingDestination']['agentName'],
                'postcode' => $value['billingDestination']['addr']['postcode'],
                'prefecture' => $value['billingDestination']['addr']['prefecture'],
                'city' => $value['billingDestination']['addr']['city'],
                'street' => $value['billingDestination']['addr']['street'],
                'apartment' => $value['billingDestination']['addr']['apartment'],
                'tel' => $value['billingDestination']['tel'],
            ],
            'isEnabled' => $value['isEnabled'],
        ];
    }
}
