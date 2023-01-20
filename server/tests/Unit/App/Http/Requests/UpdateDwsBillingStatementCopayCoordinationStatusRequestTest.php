<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationStatusRequest;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationStatusRequest} のテスト.
 */
final class UpdateDwsBillingStatementCopayCoordinationStatusRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private UpdateDwsBillingStatementCopayCoordinationStatusRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateDwsBillingStatementCopayCoordinationStatusRequest();

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[11]))
                ->byDefault();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should(
            'payload return DwsBillingStatementCopayCoordinationStatus',
            function (): void {
                $input = $this->defaultInput();
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );
                $this->assertEquals(
                    $this->expectedPayload(),
                    $this->request->payload()
                );
            },
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $examples = [
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value()],
            ],
            'when status is invalid' => [
                ['status' => ['障害福祉サービス：明細書：上限管理区分を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value()],
            ],
            'when status cannot be updated' => [
                ['status' => ['上限管理区分を更新できません。']],
                ['status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value()],
                ['status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $val) {
                    Arr::set($input, $key, $val);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                }
            },
            compact('examples')
        );
    }

    /**
     * 入力値.
     *
     * @return array|array[][]
     */
    private function defaultInput(): array
    {
        return [
            'billingId' => $this->examples->dwsBillings[0]->id,
            'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
            'id' => $this->examples->dwsBillingStatements[0]->id,
            'status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value(),
        ];
    }

    /**
     * payloadの期待値.
     *
     * @param array $input
     * @return DwsBillingStatementCopayCoordinationStatus
     */
    private function expectedPayload(): DwsBillingStatementCopayCoordinationStatus
    {
        return DwsBillingStatementCopayCoordinationStatus::unclaimable();
    }
}
