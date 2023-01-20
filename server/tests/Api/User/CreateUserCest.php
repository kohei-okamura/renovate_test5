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
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * User create のテスト
 * /POST users
 */
class CreateUserCest extends UserTest
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

        $I->sendPOST('users', $this->defaultParam($this->examples->users[0]));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
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
            'city' => '米花市',
            'street' => '米花区米花町2-21',
            'apartment' => '',
        ] + $this->defaultParam($this->examples->users[0]);

        $I->sendPOST('users', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
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
        $I->seeLogMessage(2, LogLevel::INFO, '利用者が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(3);
    }

    /**
     * 電話番号が0件で登録できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithoutTel(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call without tel');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('users', $this->defaultParam($this->examples->users[0]->copy([
            'contacts' => [
                Contact::create([
                    'tel' => null,
                    'relationship' => ContactRelationship::family(),
                    'name' => '田中花子',
                ]),
            ],
        ])));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 請求先情報の「建物名」がない場合に登録できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithoutApartmentInBillingDestination(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed API call without apartment in billing destination');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $params = tap($this->defaultParam($this->examples->users[0]), function (array &$defaultParams): void {
            Arr::forget($defaultParams, 'billingDestination.apartment');
        });
        $I->sendPOST('users', $params);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 請求先が「本人」の場合に登録できるテスト.
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
        $I->sendPOST('users', $this->defaultParam($user));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(3);
        // JOBを同期で使用しているので、JOBのログが先に来る
        $I->seeLogMessage(0, LogLevel::INFO, 'Google Geocoding API との通信に成功しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            // その他ContextにGoogle Geocoding APIの情報が入るが、assertからは省略
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者情報が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '利用者が登録されました', [
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
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendPOST('users', $this->defaultParam($this->examples->users[0]));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param \Domain\User\User $user
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
        ];
    }
}
