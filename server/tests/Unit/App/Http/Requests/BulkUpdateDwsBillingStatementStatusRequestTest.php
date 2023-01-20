<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\BulkUpdateDwsBillingStatementStatusRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Faker\Generator;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\BulkUpdateDwsBillingStatementStatusRequest} のテスト.
 */
final class BulkUpdateDwsBillingStatementStatusRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected BulkUpdateDwsBillingStatementStatusRequest $request;

    private Generator $faker;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->faker = app(Generator::class);
            $self->request = new BulkUpdateDwsBillingStatementStatusRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::ready()])))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->examples->dwsBillingStatements[1]->id
                )
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[1]->copy(['status' => DwsBillingStatus::checking()])))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return organizationSetting', function (): void {
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->defaultInput())
            );
            $expected = [
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
                'status' => DwsBillingStatus::fixed(),
            ];
            $this->assertEquals(
                $expected,
                $this->request->payload()
            );
        });
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
        $examples = [
            'when ids is empty ' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->examples->dwsBillingStatements[6]->id]],
            ],
            'when ids is not array ' => [
                ['ids' => ['配列にしてください。']],
                ['ids' => 'error'],
                ['ids' => [$this->examples->dwsBillingStatements[0]->id]],
            ],
            'when status of statements with the ids can not be updated' => [
                ['ids' => ['状態を更新できない明細書が含まれています。']],
                ['ids' => [$this->examples->dwsBillingStatements[1]->id]],
                ['ids' => [$this->examples->dwsBillingStatements[0]->id]],
            ],
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => DwsBillingStatus::fixed()->value()],
            ],
            'when status is invalid' => [
                ['status' => ['障害福祉サービス：請求：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => DwsBillingStatus::fixed()->value()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
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
            'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
            'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
            'ids' => [$this->examples->dwsBillingStatements[0]->id],
            'status' => DwsBillingStatus::fixed()->value(),
        ];
    }
}
