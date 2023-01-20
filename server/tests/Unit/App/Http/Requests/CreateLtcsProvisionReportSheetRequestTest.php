<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateLtcsProvisionReportSheetRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateLtcsProvisionReportSheetRequest} のテスト.
 */
final class CreateLtcsProvisionReportSheetRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected CreateLtcsProvisionReportSheetRequest $request;
    private LtcsProvisionReport $ltcsProvisionReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateLtcsProvisionReportSheetRequestTest $self): void {
            $requiredPermission = Permission::updateLtcsProvisionReports();
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->ltcsProvisionReport))
                ->byDefault();
            $self->request = new CreateLtcsProvisionReportSheetRequest();
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
        $this->should('payload return assoc', function (): void {
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
                ['officeId' => $this->ltcsProvisionReport->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->ltcsProvisionReport->officeId],
            ],
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->ltcsProvisionReport->userId],
            ],
            'when unknown userId given' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->ltcsProvisionReport->userId],
            ],
            'when providedIn is empty' => [
                ['providedIn' => ['入力してください。']],
                ['providedIn' => ''],
                ['providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m')],
            ],
            'when providedIn is invalid date format' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2020-10-10'],
                ['providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m')],
            ],
            'when issuedOn is empty' => [
                ['issuedOn' => ['入力してください。']],
                ['issuedOn' => ''],
                ['issuedOn' => $this->defaultInput()['issuedOn']],
            ],
            'when issuedOn is invalid date format' => [
                ['issuedOn' => ['正しい日付を入力してください。']],
                ['issuedOn' => 'error'],
                ['issuedOn' => $this->defaultInput()['issuedOn']],
            ],
            'when needsMaskingInsNumber is not boolean_ext' => [
                ['needsMaskingInsNumber' => ['trueかfalseにしてください。']],
                ['needsMaskingInsNumber' => 'error'],
                ['needsMaskingInsNumber' => $this->defaultInput()['needsMaskingInsNumber']],
            ],
            'when needsMaskingInsName is not boolean_ext' => [
                ['needsMaskingInsName' => ['trueかfalseにしてください。']],
                ['needsMaskingInsName' => 'error'],
                ['needsMaskingInsName' => $this->defaultInput()['needsMaskingInsName']],
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
            'officeId' => $this->ltcsProvisionReport->officeId,
            'userId' => $this->ltcsProvisionReport->userId,
            'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
            'issuedOn' => '2021-11-10',
            'needsMaskingInsNumber' => true,
            'needsMaskingInsName' => true,
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
            'issuedOn' => Carbon::parse($input['issuedOn']),
            'needsMaskingInsNumber' => true,
            'needsMaskingInsName' => true,
        ];
    }
}
