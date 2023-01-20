<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingFile;
use Domain\Billing\LtcsBillingRepository;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing Status update のテスト(Ltcs).
 * PUT /ltcs-billings/{id}/status
 */
class UpdateLtcsBillingStatusCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICall(ApiTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->ltcsBillings[8]->id;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(4, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 以下JOB内の処理
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '介護保険サービス請求が更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(5);
        $actual = $I->grabResponseArray();

        $I->sendGET("/ltcs-billings/{$id}");
        $latest = $I->grabResponseArray();

        // Responseの検証
        $I->assertEquals(['job', ...array_keys($latest)], array_keys($actual));
        $I->assertModelStrictEquals(
            LtcsBilling::create($latest['billing'])->copy(['files' => null]), // files はJOB内で更新がかかるので検証しない
            LtcsBilling::create($actual['billing'])->copy(['files' => null])
        );
        $I->assertEquals($latest['bundles'], $actual['bundles']);
        $I->assertEquals($latest['statements'], $actual['statements']);

        // 格納データの検証（LtcsBillingFiles）
        $I->assertMatchesModelSnapshot(
            Seq::fromArray($latest['billing']['files'])->map(
                fn (array $x): LtcsBillingFile => LtcsBillingFile::fromAssoc([
                    ...$x,
                    // 型をちゃんと変換する
                    'mimeType' => MimeType::from($x['mimeType']),
                    // 変化する値や JSON に含まれない値は検証から外す
                    'path' => '',
                    // ランダムや時間に依存する部分は検証から外す
                    'token' => '',
                    'createdAt' => Carbon::create(2022, 10, 24, 12, 34, 56),
                ])
            )
        );

        // ファイル数の検証
        // このテストで使用している billingId にはサービス提供年月が「2021年12月」と「2022年01月」の請求単位が紐づいているため、
        // （CSV x 1, pdf x 1） x 2 のファイルが作られているはず
        $repository = $this->getBillingRepository();
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $repository->lookup($actual['billing']['id'])->head();
        $groupedFiles = Seq::fromArray($billing->files)
            ->groupBy(fn (LtcsBillingFile $x) => $x->mimeType->value());
        $I->assertSame(2, $groupedFiles->get(MimeType::csv()->value())->toSeq()->flatten()->size());
        $I->assertSame(2, $groupedFiles->get(MimeType::pdf()->value())->toSeq()->flatten()->size());
    }

    /**
     * 無効化テスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     * @noinspection PhpUnused
     */
    public function succeedAPICallWithDisabled(ApiTester $I): void
    {
        $I->wantTo('succeed API call with Disabled');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->ltcsBillings[6]->id;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            ['status' => LtcsBillingStatus::disabled()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：請求が更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->sendGet("/ltcs-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $response = $I->grabResponseArray();
        $I->assertSame(LtcsBillingStatus::disabled()->value(), $response['billing']['status']);
    }

    /**
     * IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenIdNotExists(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$id}) not found");
    }

    /**
     * 請求IDが同じ事業者に存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBillingIdNotInOrganization(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when Billing ID not in Organization.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->ltcsBillings[4]->id;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$id}) not found");
    }

    /**
     * アクセス可能なOfficeの請求IDでない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBillingIdIsNotInAccessibleOffice(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when Billing ID is not in accessible Office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->ltcsBillings[5]->id;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$id}) not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithForbiddenWhenNotHavePermission(ApiTester $I): void
    {
        $I->wantTo('fail with Forbidden when not have Permission.');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $id = $this->examples->ltcsBillings[8]->id;

        $I->sendPUT(
            "/ltcs-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return [
            'status' => LtcsBillingStatus::fixed()->value(),
        ];
    }

    /**
     * 請求Repositoryの取得.
     *
     * @return \Domain\Billing\LtcsBillingRepository
     */
    private function getBillingRepository(): LtcsBillingRepository
    {
        return app(LtcsBillingRepository::class);
    }
}
