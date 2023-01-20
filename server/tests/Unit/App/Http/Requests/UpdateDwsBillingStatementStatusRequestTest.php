<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementStatusRequest;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingStatementStatusRequest} Test.
 */
class UpdateDwsBillingStatementStatusRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected UpdateDwsBillingStatementStatusRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsBillingStatementStatusRequestTest $self): void {
            $self->request = new UpdateDwsBillingStatementStatusRequest();

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->dwsBillingStatements[0]->copy(
                        [
                            'status' => DwsBillingStatus::fixed(),
                        ]
                    )
                ))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), \Mockery::any(), $self->examples->dwsBillingStatements[1]->id)
                ->andReturn(Seq::from(
                    $self->examples->dwsBillingStatements[1]->copy(
                        [
                            'status' => DwsBillingStatus::fixed(),
                            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated(),
                        ]
                    )
                ))
                ->byDefault();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
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
            'payload return DwsCertification',
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
                    $this->expectedPayload($input),
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
            $this->assertTrue($validator->passes());
        });
        $input = $this->defaultInput();
        $examples = [
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => $input['status']],
            ],
            'when invalid status given' => [
                ['status' => ['障害福祉サービス：請求：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => $input['status']],
            ],
            'when status cannot be updated for billing status' => [
                ['status' => ['状態を更新できません。']],
                ['status' => DwsBillingStatus::checking()->value()],
                ['status' => $input['status']],
            ],
            'when status cannot be updated for copay coordination status' => [
                ['status' => ['利用者負担上限額管理結果結果票が未入力のため状態を更新できません。']],
                ['id' => $this->examples->dwsBillingStatements[1]->id],
                ['id' => $this->examples->dwsBillingStatements[0]->id],
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
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'status' => DwsBillingStatus::ready()->value(),

            // URL パラメータ
            'id' => $this->examples->dwsBillingStatements[0]->id,
            'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
            'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
        ];
    }

    /**
     * payload が返す配列.
     *
     * @param array $input リクエストパラメータ
     * @return \Domain\Billing\DwsBillingStatus
     */
    private function expectedPayload(array $input): DwsBillingStatus
    {
        return DwsBillingStatus::from($input['status']);
    }
}
