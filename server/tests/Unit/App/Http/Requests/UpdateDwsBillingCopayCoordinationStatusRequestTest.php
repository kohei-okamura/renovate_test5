<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationStatusRequest;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LookupDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingCopayCoordinationStatusRequest} Test.
 */
class UpdateDwsBillingCopayCoordinationStatusRequestTest extends Test
{
    use ConfigMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use LookupDwsBillingStatementUseCaseMixin;
    use LookupDwsBillingCopayCoordinationUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    private UpdateDwsBillingCopayCoordinationStatusRequest $request;
    private DwsBillingCopayCoordination $copayCoordination;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsBillingCopayCoordinationStatusRequestTest $self): void {
            $self->request = new UpdateDwsBillingCopayCoordinationStatusRequest();
            $self->copayCoordination = $self->examples->dwsBillingCopayCoordinations[0];
            $copayCoordination = $self->copayCoordination;

            $self->lookupDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->copayCoordination->copy(['status' => DwsBillingStatus::fixed()])))
                ->byDefault();
            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::fromArray($self->examples->dwsBillingStatements)
                        ->filter(function (DwsBillingStatement $x) use ($copayCoordination) {
                            return $x->dwsBillingId === $copayCoordination->dwsBillingId
                                && $x->dwsBillingBundleId === $copayCoordination->dwsBillingBundleId;
                        })
                )
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $self->examples->dwsBillingStatements[0]->copy([
                                'status' => DwsBillingStatus::checking(),
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),
                            ]),
                        ],
                        Pagination::create()
                    )
                )
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
            'dwsBillingId' => $this->copayCoordination->dwsBillingId,
            'dwsBillingBundleId' => $this->copayCoordination->dwsBillingBundleId,
            'id' => $this->copayCoordination->id,
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
