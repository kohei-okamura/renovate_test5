<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatusRequest;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetDwsBillingInfoUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingStatusRequest} のテスト.
 */
final class UpdateDwsBillingStatusRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use GetDwsBillingInfoUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private UpdateDwsBillingStatusRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateDwsBillingStatusRequest();

            $billing = $self->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::ready()]);
            $bundles = $self->examples->dwsBillingBundles;
            $copayCoordinations = $self->examples->dwsBillingCopayCoordinations;
            $statements = Seq::from(...$self->examples->dwsBillingStatements)
                ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy(['status' => DwsBillingStatus::fixed()]))
                ->toArray();
            $reports = Seq::from(...$self->examples->dwsBillingServiceReports)
                ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy(['status' => DwsBillingStatus::fixed()]))
                ->toArray();
            $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
            $self->getDwsBillingInfoUseCase
                ->allows('handle')
                ->andReturn($info)
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
            'payload return array',
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
            $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $examples = [
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
            'when status cannot be updated' => [
                ['status' => ['状態を更新できません。']],
                ['status' => DwsBillingStatus::ready()->value()],
                ['status' => DwsBillingStatus::fixed()->value()],
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
                $this->assertSame($validator->errors()->toArray(), $expected);
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
            'status' => DwsBillingStatus::fixed()->value(),

            // URLパラメータ
            'id' => $this->examples->dwsBillings[0]->id,
        ];
    }

    /**
     * payloadの期待値.
     *
     * @param array $input
     * @return \Domain\Billing\DwsBillingStatus
     */
    private function expectedPayload(array $input): DwsBillingStatus
    {
        return DwsBillingStatus::fixed();
    }
}
