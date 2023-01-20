<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateDwsServiceReportPreviewRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateDwsServiceReportPreviewRequest} のテスト.
 */
final class CreateDwsServiceReportPreviewRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected CreateDwsServiceReportPreviewRequest $request;
    private DwsProvisionReport $dwsProvisionReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $requiredPermission = Permission::updateDwsProvisionReports();
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsProvisionReport))
                ->byDefault();
            $self->request = new CreateDwsServiceReportPreviewRequest();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [$requiredPermission], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
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
                ->with(anInstanceOf(Context::class), $requiredPermission, self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
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
        $this->should('return assoc', function (): void {
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
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->dwsProvisionReport->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->dwsProvisionReport->officeId],
            ],
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->dwsProvisionReport->userId],
            ],
            'when unknown userId given' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->dwsProvisionReport->userId],
            ],
            'when providedIn is empty' => [
                ['providedIn' => ['入力してください。']],
                ['providedIn' => ''],
                ['providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m')],
            ],
            'when providedIn is invalid date format' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2020-10-10'],
                ['providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m')],
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
            'officeId' => $this->dwsProvisionReport->officeId,
            'userId' => $this->dwsProvisionReport->userId,
            'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
        ];
    }

    /**
     * payload が返す連想配列.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'officeId' => $input['officeId'],
            'userId' => $input['userId'],
            'providedIn' => Carbon::parse($input['providedIn']),
        ];
    }
}
