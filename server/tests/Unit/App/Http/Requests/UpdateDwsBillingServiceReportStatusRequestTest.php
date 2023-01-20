<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingServiceReportStatusRequest;
use Domain\Billing\DwsBillingStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingServiceReportStatusRequest} Test.
 */
class UpdateDwsBillingServiceReportStatusRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingServiceReportUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected UpdateDwsBillingServiceReportStatusRequest $request;

    /** @var \Domain\Billing\DwsBillingServiceReport[] */
    private array $serviceReports;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsBillingServiceReportStatusRequestTest $self): void {
            $self->request = new UpdateDwsBillingServiceReportStatusRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $self->serviceReports = $self->examples->dwsBillingServiceReports;
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])))
                ->byDefault();
            $self->lookupDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceReports[0]->copy(['status' => DwsBillingStatus::ready()])))
                ->byDefault();
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
            'when status cannot be updated' => [
                ['status' => ['状態を更新できません。']],
                ['status' => DwsBillingStatus::ready()->value()],
                ['status' => $input['status']],
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
            'status' => DwsBillingStatus::fixed()->value(),
            'billingId' => $this->serviceReports[0]->dwsBillingId,
            'billingBundleId' => $this->serviceReports[0]->dwsBillingBundleId,
            'id' => $this->serviceReports[0]->id,
        ];
    }

    /**
     * payload が返す配列.
     *
     * @param array $input リクエストパラメータ
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'status' => DwsBillingStatus::from($input['status']),
        ];
    }
}
