<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProvisionReportStatusRequest;
use Domain\Common\Carbon;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * UpdateDwsProvisionReportStatusRequest のテスト.
 */
class UpdateDwsProvisionReportStatusRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;

    protected UpdateDwsProvisionReportStatusRequest $request;
    private DwsProvisionReport $dwsProvisionReport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsProvisionReportStatusRequestTest $self): void {
            $self->request = new UpdateDwsProvisionReportStatusRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsProvisionReports[0]->copy([
                    'results' => [],
                ])))
                ->byDefault();
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->with(
                    \Mockery::any(),
                    \Mockery::any(),
                    \Mockery::any(),
                    $self->examples->users[2]->id,
                    \Mockery::any()
                )
                ->andReturn(Option::some($self->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $self->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::physicalCare(),
                        ]),
                    ],
                ])))
                ->byDefault();
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
                ->andReturn(Option::from($self->examples->contracts[31]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->with(
                    \Mockery::any(),
                    $self->examples->users[2]->id,
                    \Mockery::any()
                )
                ->andReturn(Option::from($self->examples->dwsCertifications[0]->copy([
                    'grants' => [],
                ])))
                ->byDefault();

            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return DwsProvisionReportStatus', function (): void {
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
                ['status' => $this->dwsProvisionReport->status->value()],
            ],
            'when unknown status given' => [
                ['status' => ['障害福祉サービス：予実：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => $this->dwsProvisionReport->status->value()],
            ],
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when start of contract period is not filled' => [
                ['userId' => ['障害福祉サービス契約に初回サービス提供日が設定されていないため確定できません。']],
                ['userId' => $this->examples->users[1]->id],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when does not have active certification grant' => [
                [
                    'userId' => [
                        '支給決定期間外です。障害福祉サービス受給者証の登録内容に間違いがないかご確認ください。',
                        '訪問系サービス事業者記入欄に記入のない実績が含まれています。障害福祉サービス受給者証の登録内容に間違いがないかご確認ください。',
                    ],
                ],
                ['userId' => $this->examples->users[2]->id],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when does not have active certification agreement' => [
                [
                    'userId' => [
                        '支給決定期間外です。障害福祉サービス受給者証の登録内容に間違いがないかご確認ください。',
                        '訪問系サービス事業者記入欄に記入のない実績が含まれています。障害福祉サービス受給者証の登録内容に間違いがないかご確認ください。',
                    ],
                ],
                ['userId' => $this->examples->users[2]->id, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => $this->examples->users[2]->id, 'status' => DwsProvisionReportStatus::inProgress()->value()],
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
            'providedIn' => Carbon::now()->format('Y-m'),
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
        return ['status' => DwsProvisionReportStatus::from($input['status'])];
    }
}
