<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProvisionReportStatusRequest;
use Domain\Common\Carbon;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * UpdateLtcsProvisionReportStatusRequest のテスト.
 */
class UpdateLtcsProvisionReportStatusRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;

    protected UpdateLtcsProvisionReportStatusRequest $request;
    private LtcsProvisionReport $ltcsProvisionReport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateLtcsProvisionReportStatusRequestTest $self): void {
            $self->request = new UpdateLtcsProvisionReportStatusRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(\Mockery::any(), \Mockery::any(), $self->examples->users[1]->id)
                ->andReturn(Seq::from($self->examples->users[1]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->with(
                    \Mockery::any(),
                    \Mockery::any(),
                    \Mockery::any(),
                    $self->examples->users[1]->id,
                    \Mockery::any(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($self->examples->contracts[32]))
                ->byDefault();

            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return LtcsProvisionReportStatus', function (): void {
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
            $this->assertEquals(
                $this->expectedPayload($this->defaultInput()),
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
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => $this->ltcsProvisionReport->status->value()],
            ],
            'when unknown status given' => [
                ['status' => ['介護保険サービス：予実：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => $this->ltcsProvisionReport->status->value()],
            ],
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when start of contract period is not filled' => [
                ['userId' => ['介護保険サービス契約に初回サービス提供日が設定されていないため確定できません。']],
                ['userId' => $this->examples->users[1]->id],
                ['userId' => $this->examples->users[0]->id],

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
            'officeId' => $this->examples->offices[0]->id,
            'userId' => $this->examples->users[0]->id,
            'status' => DwsProvisionReportStatus::fixed()->value(),
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return ['status' => LtcsProvisionReportStatus::from($input['status'])];
    }
}
