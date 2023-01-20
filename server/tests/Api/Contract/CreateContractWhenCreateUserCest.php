<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Contract;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Prefecture;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Contract create のテスト.
 * POST /dws-contracts
 */
class CreateContractWhenCreateUserCest extends ContractTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * 契約登録ロールを持つ事業所管理者が登録した利用者の契約を追加するテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenOfficeAdministratorHasTheRoleToContractRegister(ApiTester $I)
    {
        $I->markTestSkipped('ジオコーディング関連のテストを一旦スキップ');
        $I->wantTo('succeed api call when office administrator has the role to contract register');

        // 事業所管理者ロールと契約担当者ロールを持つスタッフ
        $staff = $this->examples->staffs[32];
        $I->actingAs($staff);

        $I->sendPOST('users', $this->user());
        $I->seeResponseCodeIs(HttpCode::CREATED);
        // 上で登録したユーザーを取得
        $I->sendGET('users?sortBy=id&desc=true');
        $I->seeResponseCodeIs(HttpCode::OK);
        $user = $I->grabResponseArray()['list'][0];
        // 契約作成
        $I->sendPOST("users/{$user['id']}/dws-contracts", $this->domainToArray($this->examples->contracts[0]->copy([
            'userId' => $user['id'],
        ])));

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @return array
     */
    private function user(): array
    {
        $value = $this->domainToArray($this->examples->users[0]);
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
