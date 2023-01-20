<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOfficeRequest;
use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Domain\Context\Context;
use Domain\Office\OfficeDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeLtcsPreventionService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UpdateOfficeRequest のテスト.
 */
final class UpdateOfficeRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupDwsAreaGradeUseCaseMixin;
    use LookupLtcsAreaGradeUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected UpdateOfficeRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateOfficeRequest();
            $self->lookupDwsAreaGradeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->dwsAreaGrades[5]->id)
                ->andReturn(Seq::from($self->examples->dwsAreaGrades[0]));
            $self->lookupDwsAreaGradeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $self->lookupLtcsAreaGradeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->ltcsAreaGrades[4]->id)
                ->andReturn(Seq::from($self->examples->ltcsAreaGrades[0]));
            $self->lookupLtcsAreaGradeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[0]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
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
        $this->should('payload return array', function (): void {
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
        });
        $this->should(
            'return Office when non-required parameter is null or empty',
            function (string $key): void {
                foreach (['', null] as $value) {
                    $input = $this->defaultInput();
                    Arr::set($input, $key, $value);
                    $this->request->initialize(
                        [],
                        [],
                        [],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        Json::encode($input),
                    );
                    $this->assertEquals($this->expectedPayload($input), $this->request->payload());
                }
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when corporationName' => ['corporationName'],
                    'when phoneticCorporationName' => ['phoneticCorporationName'],
                ],
            ]
        );
        $this->should(
            'return Office when non-required parameter is omitted',
            function (string $key): void {
                $input = $this->defaultInput();
                Arr::forget($input, $key);
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input),
                );
                $this->assertEquals($this->expectedPayload($input), $this->request->payload());
            },
            [
                'examples' => [
                    'when apartment' => ['apartment'],
                    'when fax' => ['fax'],
                    'when corporationName' => ['corporationName'],
                    'when phoneticCorporationName' => ['phoneticCorporationName'],
                ],
            ]
        );
        $this->should(
            'return Office when Purpose is not internal and optional parameters is null.',
            function (string $key): void {
                $input = $this->defaultInput();
                $input['purpose'] = Purpose::external()->value();
                Arr::set($input, $key, null);
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input),
                );
                $this->assertEquals($this->expectedPayload($input), $this->request->payload());
            },
            [
                'examples' => [
                    'when email' => ['email'],
                    'when officeGroupId' => ['officeGroupId'],
                    'when dwsGenericService.dwsAreaGradeId' => ['dwsGenericService.dwsAreaGradeId'],
                    'when dwsGenericService.openedOn' => ['dwsGenericService.openedOn'],
                    'when dwsGenericService.designationExpiredOn' => ['dwsGenericService.designationExpiredOn'],
                    'when dwsCommAccompanyService.openedOn' => ['dwsCommAccompanyService.openedOn'],
                    'when dwsCommAccompanyService.designationExpiredOn' => ['dwsCommAccompanyService.designationExpiredOn'],
                    'when ltcsCareManagementService.ltcsAreaGradeId' => ['ltcsCareManagementService.ltcsAreaGradeId'],
                    'when ltcsCareManagementService.openedOn' => ['ltcsCareManagementService.openedOn'],
                    'when ltcsCareManagementService.designationExpiredOn' => ['ltcsCareManagementService.designationExpiredOn'],
                    'when ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId'],
                    'when ltcsHomeVisitLongTermCareService.openedOn' => ['ltcsHomeVisitLongTermCareService.openedOn'],
                    'when ltcsHomeVisitLongTermCareService.designationExpiredOn' => ['ltcsHomeVisitLongTermCareService.designationExpiredOn'],
                    'when ltcsCompHomeVisitingService.openedOn' => ['ltcsCompHomeVisitingService.openedOn'],
                    'when ltcsCompHomeVisitingService.designationExpiredOn' => ['ltcsCompHomeVisitingService.designationExpiredOn'],
                    'when ltcsPreventionService.openedOn' => ['ltcsPreventionService.openedOn'],
                    'when ltcsPreventionService.designationExpiredOn' => ['ltcsPreventionService.designationExpiredOn'],
                ],
            ]
        );
        $this->should(
            'return Office when Purpose is not internal and optional parameters is empty.',
            function (string $key): void {
                $input = $this->defaultInput();
                $input['purpose'] = Purpose::external()->value();
                Arr::set($input, $key, '');
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input),
                );
                $this->assertEquals($this->expectedPayload($input), $this->request->payload());
            },
            [
                'examples' => [
                    'when email' => ['email'],
                    'when officeGroupId' => ['officeGroupId'],
                    'when dwsGenericService.dwsAreaGradeId' => ['dwsGenericService.dwsAreaGradeId'],
                    'when dwsGenericService.openedOn' => ['dwsGenericService.openedOn'],
                    'when dwsGenericService.designationExpiredOn' => ['dwsGenericService.designationExpiredOn'],
                    'when dwsCommAccompanyService.openedOn' => ['dwsCommAccompanyService.openedOn'],
                    'when dwsCommAccompanyService.designationExpiredOn' => ['dwsCommAccompanyService.designationExpiredOn'],
                    'when ltcsCareManagementService.ltcsAreaGradeId' => ['ltcsCareManagementService.ltcsAreaGradeId'],
                    'when ltcsCareManagementService.openedOn' => ['ltcsCareManagementService.openedOn'],
                    'when ltcsCareManagementService.designationExpiredOn' => ['ltcsCareManagementService.designationExpiredOn'],
                    'when ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId'],
                    'when ltcsHomeVisitLongTermCareService.openedOn' => ['ltcsHomeVisitLongTermCareService.openedOn'],
                    'when ltcsHomeVisitLongTermCareService.designationExpiredOn' => ['ltcsHomeVisitLongTermCareService.designationExpiredOn'],
                    'when ltcsCompHomeVisitingService.openedOn' => ['ltcsCompHomeVisitingService.openedOn'],
                    'when ltcsCompHomeVisitingService.designationExpiredOn' => ['ltcsCompHomeVisitingService.designationExpiredOn'],
                    'when ltcsPreventionService.openedOn' => ['ltcsPreventionService.openedOn'],
                    'when ltcsPreventionService.designationExpiredOn' => ['ltcsPreventionService.designationExpiredOn'],
                ],
            ]
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
        $examples = [
            'when name is empty' => [
                ['name' => ['入力してください。']],
                ['name' => ''],
                ['name' => $this->examples->offices[0]->name],
            ],
            'when name is longer than 200' => [
                ['name' => ['200文字以内で入力してください。']],
                ['name' => str_repeat('山', 201)],
                ['name' => str_repeat('山', 200)],
            ],
            'when abbr is empty and purpose is internal' => [
                ['abbr' => ['入力してください。']],
                ['abbr' => '', 'purpose' => Purpose::internal()->value()],
                ['abbr' => $this->examples->offices[0]->abbr, 'purpose' => Purpose::internal()->value()],
            ],
            'when abbr is longer than 200' => [
                ['abbr' => ['200文字以内で入力してください。']],
                ['abbr' => str_repeat('川', 201)],
                ['abbr' => str_repeat('川', 200)],
            ],
            'when phoneticName is empty' => [
                ['phoneticName' => ['入力してください。']],
                ['phoneticName' => ''],
                ['phoneticName' => 'ツチヤホウモンカイゴジギョウショサッポロ'],
            ],
            'when phoneticName is longer than 200' => [
                ['phoneticName' => ['200文字以内で入力してください。']],
                ['phoneticName' => str_repeat('ア', 201)],
                ['phoneticName' => str_repeat('ア', 200)],
            ],
            'when phoneticName contains non-katakana character(s)' => [
                ['phoneticName' => ['カタカナで入力してください。']],
                ['phoneticName' => 'あいうえおかきくけこ'],
                ['phoneticName' => 'アイウエオカキクケコ'],
            ],
            'when corporationName is longer than 200' => [
                ['phoneticName' => ['200文字以内で入力してください。']],
                ['phoneticName' => str_repeat('ア', 201)],
                ['phoneticName' => str_repeat('ア', 200)],
            ],
            'when phoneticCorporationName is longer than 200' => [
                ['phoneticCorporationName' => ['200文字以内で入力してください。']],
                ['phoneticCorporationName' => str_repeat('ア', 201)],
                ['phoneticCorporationName' => str_repeat('ア', 200)],
            ],
            'when phoneticCorporationName contains non-katakana character(s)' => [
                ['phoneticCorporationName' => ['カタカナで入力してください。']],
                ['phoneticCorporationName' => 'あいうえおかきくけこ'],
                ['phoneticCorporationName' => 'アイウエオカキクケコ'],
            ],
            'when purpose is empty' => [
                ['purpose' => ['入力してください。']],
                ['purpose' => ''],
                ['purpose' => $this->examples->offices[0]->purpose->value()],
            ],
            'when purpose not exists' => [
                ['purpose' => ['事業所区分を指定してください。']],
                ['purpose' => 'あいうえおかきくけこ'],
                ['purpose' => $this->examples->offices[0]->purpose->value()],
            ],
            'when postcode is empty and purpose is internal' => [
                ['postcode' => ['入力してください。']],
                ['postcode' => '', 'purpose' => Purpose::internal()->value()],
                ['postcode' => '164-0011', 'purpose' => Purpose::internal()->value()],
            ],
            'when invalid postcode given' => [
                ['postcode' => ['郵便番号は7桁で入力してください。']],
                ['postcode' => '133-005'],
                ['postcode' => '133-0051'],
            ],
            'when prefecture is empty and purpose is internal' => [
                ['prefecture' => ['入力してください。']],
                ['prefecture' => '', 'purpose' => Purpose::internal()->value()],
                ['prefecture' => Prefecture::tokyo()->value(), 'purpose' => Purpose::internal()->value()],
            ],
            'when invalid prefecture given' => [
                ['prefecture' => ['都道府県を指定してください。']],
                ['prefecture' => 99],
                ['prefecture' => Prefecture::tokyo()->value()],
            ],
            'when city is empty and purpose is internal' => [
                ['city' => ['入力してください。']],
                ['city' => '', 'purpose' => Purpose::internal()->value()],
                ['city' => $this->examples->offices[0]->addr->city, 'purpose' => Purpose::internal()->value()],
            ],
            'when city is longer than 200' => [
                ['city' => ['200文字以内で入力してください。']],
                ['city' => str_repeat('江', 201)],
                ['city' => str_repeat('江', 200)],
            ],
            'when street is empty and purpose is internal' => [
                ['street' => ['入力してください。']],
                ['street' => '', 'purpose' => Purpose::internal()->value()],
                ['street' => $this->examples->offices[0]->addr->street, 'purpose' => Purpose::internal()->value()],
            ],
            'when street is longer than 200' => [
                ['street' => ['200文字以内で入力してください。']],
                ['street' => str_repeat('北', 201)],
                ['street' => str_repeat('北', 200)],
            ],
            'when apartment is longer than 200' => [
                ['apartment' => ['200文字以内で入力してください。']],
                ['apartment' => str_repeat('西', 201)],
                ['apartment' => str_repeat('西', 200)],
            ],
            'when tel is empty and purpose is internal' => [
                ['tel' => ['入力してください。']],
                ['tel' => '', 'purpose' => Purpose::internal()->value()],
                ['tel' => '012-345-6789', 'purpose' => Purpose::internal()->value()],
            ],
            'when tel is non phone number' => [
                ['tel' => ['正しい値を入力してください。']],
                ['tel' => '9876-5432-10'],
                ['tel' => '012-345-6789'],
            ],
            'when fax is non phone number' => [
                ['fax' => ['正しい値を入力してください。']],
                ['fax' => '033-12345-67'],
                ['fax' => '03-3333-3333'],
            ],
            'when email is longer than 255' => [
                ['email' => ['255文字以内で入力してください。']],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
            ],
            'when qualifications.0 not exists' => [
                ['qualifications.0' => ['事業所：指定区分を指定してください。']],
                ['qualifications' => ['あいうえおかきくけこ']],
                [
                    'qualifications' => Seq::fromArray($this->examples->offices[0]->qualifications)
                        ->map(fn (OfficeQualification $x): string => $x->value())->toArray(),
                ],
            ],
            'when officeGroupId is empty and purpose is internal' => [
                ['officeGroupId' => ['入力してください。']],
                ['officeGroupId' => '', 'purpose' => Purpose::internal()->value()],
                [
                    'officeGroupId' => $this->examples->offices[0]->officeGroupId,
                    'purpose' => Purpose::internal()->value(),
                ],
            ],
            'when unknown officeGroupId given' => [
                ['officeGroupId' => ['正しい値を入力してください。']],
                ['officeGroupId' => self::NOT_EXISTING_ID],
                ['officeGroupId' => $this->examples->offices[0]->officeGroupId],
            ],
            'when dwsGenericService.dwsAreaGradeId is empty and purpose is internal and qualifications contain dwsHomeHelpService or dwsVisitingCareForPwsd or dwsOthers' => [
                ['dwsGenericService.dwsAreaGradeId' => ['入力してください。']],
                [
                    'dwsGenericService.dwsAreaGradeId' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
                [
                    'dwsGenericService.dwsAreaGradeId' => $this->examples->offices[0]->dwsGenericService->dwsAreaGradeId,
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
            ],
            'when dwsGenericService.dwsAreaGradeId is not exist' => [
                ['dwsGenericService.dwsAreaGradeId' => ['正しい値を入力してください']],
                ['dwsGenericService.dwsAreaGradeId' => self::NOT_EXISTING_ID],
                ['dwsGenericService.dwsAreaGradeId' => $this->examples->offices[0]->dwsGenericService->dwsAreaGradeId],
            ],
            'when dwsGenericService.code is empty and qualifications contain dwsHomeHelpService or dwsVisitingCareForPwsd or dwsOthers' => [
                ['dwsGenericService.code' => ['入力してください。']],
                [
                    'dwsGenericService.code' => '',
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
                [
                    'dwsGenericService.code' => $this->examples->offices[0]->dwsGenericService->code,
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
            ],
            'when dwsGenericService.code is not ascii_alpha_num' => [
                ['dwsGenericService.code' => ['英数字のみで入力してください。']],
                ['dwsGenericService.code' => '12345678!?'],
                ['dwsGenericService.code' => str_repeat('1', 10)],
            ],
            'when dwsGenericService.code is longer than 10' => [
                ['dwsGenericService.code' => ['障害福祉サービス：事業所番号は10文字で入力してください。']],
                ['dwsGenericService.code' => str_repeat('1', 5)],
                ['dwsGenericService.code' => str_repeat('1', 10)],
            ],
            'when dwsGenericService.openedOn is empty and purpose is internal and qualifications contain dwsHomeHelpService or dwsVisitingCareForPwsd or dwsOthers' => [
                ['dwsGenericService.openedOn' => ['入力してください。']],
                [
                    'dwsGenericService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
                [
                    'dwsGenericService.openedOn' => $this->examples->offices[0]->dwsGenericService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
            ],
            'when invalid dwsGenericService.openedOn given' => [
                ['dwsGenericService.openedOn' => ['正しい日付を入力してください。']],
                [
                    'dwsGenericService.openedOn' => '1999-02-29',
                ],
                [
                    'dwsGenericService.openedOn' => '2000-02-29',
                ],
            ],
            'when dwsGenericService.designationExpiredOn is empty and purpose is internal and qualifications contain dwsHomeHelpService or dwsVisitingCareForPwsd or dwsOthers' => [
                ['dwsGenericService.designationExpiredOn' => ['入力してください。']],
                [
                    'dwsGenericService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
                [
                    'dwsGenericService.designationExpiredOn' => $this->examples->offices[0]->dwsGenericService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsHomeHelpService()->value()],
                ],
            ],
            'when invalid dwsGenericService.designationExpiredOn given' => [
                ['dwsGenericService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['dwsGenericService.designationExpiredOn' => '1999-02-29'],
                ['dwsGenericService.designationExpiredOn' => '2000-02-29'],
            ],
            'when dwsCommAccompanyService.code is empty and qualifications contain dwsCommAccompany' => [
                ['dwsCommAccompanyService.code' => ['入力してください。']],
                [
                    'dwsCommAccompanyService.code' => '',
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
                [
                    'dwsCommAccompanyService.code' => $this->examples->offices[0]->dwsCommAccompanyService->code,
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
            ],
            'when dwsCommAccompanyService.code is not ascii_alpha_num' => [
                ['dwsCommAccompanyService.code' => ['英数字のみで入力してください。']],
                ['dwsCommAccompanyService.code' => '12345678!?'],
                ['dwsCommAccompanyService.code' => str_repeat('1', 10)],
            ],
            'when dwsCommAccompanyService.code is longer than 10' => [
                ['dwsCommAccompanyService.code' => ['障害福祉サービス：移動支援（地域生活支援事業）：事業所番号は10文字で入力してください。']],
                ['dwsCommAccompanyService.code' => str_repeat('1', 5)],
                ['dwsCommAccompanyService.code' => str_repeat('1', 10)],
            ],
            'when dwsCommAccompanyService.openedOn is empty and purpose is internal and qualifications contain dwsCommAccompany' => [
                ['dwsCommAccompanyService.openedOn' => ['入力してください。']],
                [
                    'dwsCommAccompanyService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
                [
                    'dwsCommAccompanyService.openedOn' => $this->examples->offices[0]->dwsCommAccompanyService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
            ],
            'when invalid dwsCommAccompanyService.openedOn given' => [
                ['dwsCommAccompanyService.openedOn' => ['正しい日付を入力してください。']],
                ['dwsCommAccompanyService.openedOn' => '1999-02-29'],
                ['dwsCommAccompanyService.openedOn' => '2000-02-29'],
            ],
            'when dwsCommAccompanyService.designationExpiredOn is empty and purpose is internal and qualifications contain dwsCommAccompany' => [
                ['dwsCommAccompanyService.designationExpiredOn' => ['入力してください。']],
                [
                    'dwsCommAccompanyService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
                [
                    'dwsCommAccompanyService.designationExpiredOn' => $this->examples->offices[0]->dwsCommAccompanyService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::dwsCommAccompany()->value()],
                ],
            ],
            'when invalid dwsCommAccompanyService.designationExpiredOn given' => [
                ['dwsCommAccompanyService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['dwsCommAccompanyService.designationExpiredOn' => '1999-02-29'],
                ['dwsCommAccompanyService.designationExpiredOn' => '2000-02-29'],
            ],
            'when ltcsCareManagementService.ltcsAreaGradeId is empty and purpose is internal and qualifications contain ltcsCareManagement' => [
                ['ltcsCareManagementService.ltcsAreaGradeId' => ['入力してください。']],
                [
                    'ltcsCareManagementService.ltcsAreaGradeId' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
                [
                    'ltcsCareManagementService.ltcsAreaGradeId' => $this->examples->offices[0]->ltcsCareManagementService->ltcsAreaGradeId,
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
            ],
            'when ltcsCareManagementService.ltcsAreaGradeId is not exist' => [
                ['ltcsCareManagementService.ltcsAreaGradeId' => ['正しい値を入力してください']],
                ['ltcsCareManagementService.ltcsAreaGradeId' => self::NOT_EXISTING_ID],
                ['ltcsCareManagementService.ltcsAreaGradeId' => $this->examples->offices[0]->ltcsCareManagementService->ltcsAreaGradeId],
            ],
            'when ltcsCareManagementService.code is empty and qualifications contain ltcsCareManagement' => [
                ['ltcsCareManagementService.code' => ['入力してください。']],
                [
                    'ltcsCareManagementService.code' => '',
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
                [
                    'ltcsCareManagementService.code' => $this->examples->offices[0]->ltcsCareManagementService->code,
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
            ],
            'when ltcsCareManagementService.code is not ascii_alpha_num' => [
                ['ltcsCareManagementService.code' => ['英数字のみで入力してください。']],
                ['ltcsCareManagementService.code' => '12345678!?'],
                ['ltcsCareManagementService.code' => str_repeat('1', 10)],
            ],
            'when ltcsCareManagementService.code is longer than 10' => [
                ['ltcsCareManagementService.code' => ['介護保険サービス：訪問介護：事業所番号は10文字で入力してください。']],
                ['ltcsCareManagementService.code' => str_repeat('1', 5)],
                ['ltcsCareManagementService.code' => str_repeat('1', 10)],
            ],
            'when ltcsCareManagementService.openedOn is empty and purpose is internal and qualifications contain ltcsCareManagement' => [
                ['ltcsCareManagementService.openedOn' => ['入力してください。']],
                [
                    'ltcsCareManagementService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
                [
                    'ltcsCareManagementService.openedOn' => $this->examples->offices[0]->ltcsCareManagementService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
            ],
            'when invalid ltcsCareManagementService.openedOn given' => [
                ['ltcsCareManagementService.openedOn' => ['正しい日付を入力してください。']],
                ['ltcsCareManagementService.openedOn' => '1999-02-29'],
                ['ltcsCareManagementService.openedOn' => '2000-02-29'],
            ],
            'when ltcsCareManagementService.designationExpiredOn is empty and purpose is internal and qualifications contain ltcsCareManagement' => [
                ['ltcsCareManagementService.designationExpiredOn' => ['入力してください。']],
                [
                    'ltcsCareManagementService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
                [
                    'ltcsCareManagementService.designationExpiredOn' => $this->examples->offices[0]->ltcsCareManagementService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCareManagement()->value()],
                ],
            ],
            'when invalid ltcsCareManagementService.designationExpiredOn given' => [
                ['ltcsCareManagementService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['ltcsCareManagementService.designationExpiredOn' => '1999-02-29'],
                ['ltcsCareManagementService.designationExpiredOn' => '2000-02-29'],
            ],
            'when ltcsHomeVisitLongTermCareService.ltcsAreaGradeId is empty and purpose is internal and qualifications contain ltcsHomeVisitLongTermCare' => [
                ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => ['入力してください。']],
                [
                    'ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
                [
                    'ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
            ],
            'when ltcsHomeVisitLongTermCareService.ltcsAreaGradeId is not exist' => [
                ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => ['正しい値を入力してください']],
                ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => self::NOT_EXISTING_ID],
                ['ltcsHomeVisitLongTermCareService.ltcsAreaGradeId' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId],
            ],
            'when ltcsHomeVisitLongTermCareService.code is empty and qualifications contain ltcsHomeVisitLongTermCare' => [
                ['ltcsHomeVisitLongTermCareService.code' => ['入力してください。']],
                [
                    'ltcsHomeVisitLongTermCareService.code' => '',
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
                [
                    'ltcsHomeVisitLongTermCareService.code' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->code,
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
            ],
            'when ltcsHomeVisitLongTermCareService.code is not ascii_alpha_num' => [
                ['ltcsHomeVisitLongTermCareService.code' => ['英数字のみで入力してください。']],
                ['ltcsHomeVisitLongTermCareService.code' => '12345678!?'],
                ['ltcsHomeVisitLongTermCareService.code' => str_repeat('1', 10)],
            ],
            'when ltcsHomeVisitLongTermCareService.code is longer than 10' => [
                ['ltcsHomeVisitLongTermCareService.code' => ['介護保険サービス：居宅介護支援：事業所番号は10文字で入力してください。']],
                ['ltcsHomeVisitLongTermCareService.code' => str_repeat('1', 5)],
                ['ltcsHomeVisitLongTermCareService.code' => str_repeat('1', 10)],
            ],
            'when ltcsHomeVisitLongTermCareService.openedOn is empty and purpose is internal and qualifications contain ltcsHomeVisitLongTermCare' => [
                ['ltcsHomeVisitLongTermCareService.openedOn' => ['入力してください。']],
                [
                    'ltcsHomeVisitLongTermCareService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
                [
                    'ltcsHomeVisitLongTermCareService.openedOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
            ],
            'when invalid ltcsHomeVisitLongTermCareService.openedOn given' => [
                ['ltcsHomeVisitLongTermCareService.openedOn' => ['正しい日付を入力してください。']],
                ['ltcsHomeVisitLongTermCareService.openedOn' => '1999-02-29'],
                ['ltcsHomeVisitLongTermCareService.openedOn' => '2000-02-29'],
            ],
            'when ltcsHomeVisitLongTermCareService.designationExpiredOn is empty and purpose is internal and qualifications contain ltcsHomeVisitLongTermCare' => [
                ['ltcsHomeVisitLongTermCareService.designationExpiredOn' => ['入力してください。']],
                [
                    'ltcsHomeVisitLongTermCareService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
                [
                    'ltcsHomeVisitLongTermCareService.designationExpiredOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsHomeVisitLongTermCare()->value()],
                ],
            ],
            'when invalid ltcsHomeVisitLongTermCareService.designationExpiredOn given' => [
                ['ltcsHomeVisitLongTermCareService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['ltcsHomeVisitLongTermCareService.designationExpiredOn' => '1999-02-29'],
                ['ltcsHomeVisitLongTermCareService.designationExpiredOn' => '2000-02-29'],
            ],
            'when ltcsCompHomeVisitingService.code is empty and qualifications contain ltcsCompHomeVisiting' => [
                ['ltcsCompHomeVisitingService.code' => ['入力してください。']],
                [
                    'ltcsCompHomeVisitingService.code' => '',
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
                [
                    'ltcsCompHomeVisitingService.code' => $this->examples->offices[0]->ltcsCompHomeVisitingService->code,
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
            ],
            'when ltcsCompHomeVisitingService.code is not ascii_alpha_num' => [
                ['ltcsCompHomeVisitingService.code' => ['英数字のみで入力してください。']],
                ['ltcsCompHomeVisitingService.code' => '12345678!?'],
                ['ltcsCompHomeVisitingService.code' => str_repeat('1', 10)],
            ],
            'when ltcsCompHomeVisitingService.code is longer than 10' => [
                ['ltcsCompHomeVisitingService.code' => ['介護保険サービス：訪問型サービス（総合事業）：事業所番号は10文字で入力してください。']],
                ['ltcsCompHomeVisitingService.code' => str_repeat('1', 5)],
                ['ltcsCompHomeVisitingService.code' => str_repeat('1', 10)],
            ],
            'when ltcsCompHomeVisitingService.openedOn is empty' => [
                ['ltcsCompHomeVisitingService.openedOn' => ['入力してください。']],
                [
                    'ltcsCompHomeVisitingService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
                [
                    'ltcsCompHomeVisitingService.openedOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
            ],
            'when invalid ltcsCompHomeVisitingService.openedOn given' => [
                ['ltcsCompHomeVisitingService.openedOn' => ['正しい日付を入力してください。']],
                ['ltcsCompHomeVisitingService.openedOn' => '1999-02-29'],
                ['ltcsCompHomeVisitingService.openedOn' => '2000-02-29'],
            ],
            'when ltcsCompHomeVisitingService.designationExpiredOn is empty and purpose is internal and qualifications contain ltcsCompHomeVisiting' => [
                ['ltcsCompHomeVisitingService.designationExpiredOn' => ['入力してください。']],
                [
                    'ltcsCompHomeVisitingService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
                [
                    'ltcsCompHomeVisitingService.designationExpiredOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsCompHomeVisiting()->value()],
                ],
            ],
            'when invalid ltcsCompHomeVisitingService.designationExpiredOn given' => [
                ['ltcsCompHomeVisitingService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['ltcsCompHomeVisitingService.designationExpiredOn' => '1999-02-29'],
                ['ltcsCompHomeVisitingService.designationExpiredOn' => '2000-02-29'],
            ],
            'when ltcsPreventionService.code is empty and qualifications contain ltcsPrevention' => [
                ['ltcsPreventionService.code' => ['入力してください。']],
                [
                    'ltcsPreventionService.code' => '',
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
                [
                    'ltcsPreventionService.code' => $this->examples->offices[0]->ltcsPreventionService->code,
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
            ],
            'when ltcsPreventionService.code is not ascii_alpha_num' => [
                ['ltcsPreventionService.code' => ['英数字のみで入力してください。']],
                ['ltcsPreventionService.code' => '12345678!?'],
                ['ltcsPreventionService.code' => str_repeat('1', 10)],
            ],
            'when ltcsPreventionService.code is longer than 10' => [
                ['ltcsPreventionService.code' => ['介護保険サービス：介護予防支援：事業所番号は10文字で入力してください。']],
                ['ltcsPreventionService.code' => str_repeat('1', 5)],
                ['ltcsPreventionService.code' => str_repeat('1', 10)],
            ],
            'when ltcsPreventionService.openedOn is empty' => [
                ['ltcsPreventionService.openedOn' => ['入力してください。']],
                [
                    'ltcsPreventionService.openedOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
                [
                    'ltcsPreventionService.openedOn' => $this->examples->offices[0]->ltcsPreventionService->openedOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
            ],
            'when invalid ltcsPreventionService.openedOn given' => [
                ['ltcsPreventionService.openedOn' => ['正しい日付を入力してください。']],
                ['ltcsPreventionService.openedOn' => '1999-02-29'],
                ['ltcsPreventionService.openedOn' => '2000-02-29'],
            ],
            'when ltcsPreventionService.designationExpiredOn is empty and purpose is internal and qualifications contain ltcsPrevention' => [
                ['ltcsPreventionService.designationExpiredOn' => ['入力してください。']],
                [
                    'ltcsPreventionService.designationExpiredOn' => '',
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
                [
                    'ltcsPreventionService.designationExpiredOn' => $this->examples->offices[0]->ltcsPreventionService->designationExpiredOn->toDateString(),
                    'purpose' => Purpose::internal()->value(),
                    'qualifications' => [OfficeQualification::ltcsPrevention()->value()],
                ],
            ],
            'when invalid ltcsPreventionService.designationExpiredOn given' => [
                ['ltcsPreventionService.designationExpiredOn' => ['正しい日付を入力してください。']],
                ['ltcsPreventionService.designationExpiredOn' => '1999-02-29'],
                ['ltcsPreventionService.designationExpiredOn' => '2000-02-29'],
            ],
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => $this->examples->offices[0]->status->value()],
            ],
            'when status not exists' => [
                ['status' => ['事業所の状態を指定してください。']],
                ['status' => 'あいうえおかきくけこ'],
                ['status' => $this->examples->offices[0]->status->value()],
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
        $values = [
            'name' => $this->examples->offices[0]->name,
            'abbr' => $this->examples->offices[0]->abbr,
            'phoneticName' => $this->examples->offices[0]->phoneticName,
            'corporationName' => $this->examples->offices[0]->corporationName,
            'phoneticCorporationName' => $this->examples->offices[0]->phoneticCorporationName,
            'purpose' => $this->examples->offices[0]->purpose->value(),
            'postcode' => '123-4567',
            'prefecture' => $this->examples->offices[0]->addr->prefecture->value(),
            'city' => $this->examples->offices[0]->addr->city,
            'street' => $this->examples->offices[0]->addr->street,
            'apartment' => $this->examples->offices[0]->addr->apartment,
            'tel' => '03-1234-5678',
            'fax' => '03-9876-5432',
            'email' => 'test@mail.com',
            'officeGroupId' => $this->examples->offices[0]->officeGroupId,
            'dwsGenericService' => [
                'dwsAreaGradeId' => $this->examples->offices[0]->dwsGenericService->dwsAreaGradeId,
                'code' => $this->examples->offices[0]->dwsGenericService->code,
                'openedOn' => $this->examples->offices[0]->dwsGenericService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->dwsGenericService->designationExpiredOn->toDateString(),
            ],
            'dwsCommAccompanyService' => [
                'code' => $this->examples->offices[0]->dwsCommAccompanyService->code,
                'openedOn' => $this->examples->offices[0]->dwsCommAccompanyService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->dwsCommAccompanyService->designationExpiredOn->toDateString(),
            ],
            'ltcsCareManagementService' => [
                'ltcsAreaGradeId' => $this->examples->offices[0]->ltcsCareManagementService->ltcsAreaGradeId,
                'code' => $this->examples->offices[0]->ltcsCareManagementService->code,
                'openedOn' => $this->examples->offices[0]->ltcsCareManagementService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsCareManagementService->designationExpiredOn->toDateString(),
            ],
            'ltcsHomeVisitLongTermCareService' => [
                'ltcsAreaGradeId' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                'code' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->code,
                'openedOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->designationExpiredOn->toDateString(),
            ],
            'ltcsCompHomeVisitingService' => [
                'code' => $this->examples->offices[0]->ltcsCompHomeVisitingService->code,
                'openedOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->designationExpiredOn->toDateString(),
            ],
            'ltcsPreventionService' => [
                'code' => $this->examples->offices[0]->ltcsPreventionService->code,
                'openedOn' => $this->examples->offices[0]->ltcsPreventionService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsPreventionService->designationExpiredOn->toDateString(),
            ],
            'status' => $this->examples->offices[0]->status->value(),
        ];
        $qualifications = Seq::fromArray($this->examples->offices[0]->qualifications)
            ->map(fn (OfficeQualification $qualification) => $qualification->value())
            ->toArray();

        return $values + compact('qualifications');
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        $values = [
            'name' => $input['name'],
            'abbr' => $input['abbr'],
            'phoneticName' => $input['phoneticName'],
            'corporationName' => $this->isInternal($input)
                ? ''
                : ($input['corporationName'] ?? ''),
            'phoneticCorporationName' => $this->isInternal($input)
                ? ''
                : ($input['phoneticCorporationName'] ?? ''),
            'purpose' => Purpose::from($input['purpose']),
            'addr' => new Addr(
                postcode: $input['postcode'],
                prefecture: Prefecture::from($input['prefecture']),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'] ?? '',
            ),
            'tel' => $input['tel'],
            'fax' => $input['fax'] ?? '',
            'status' => OfficeStatus::from($input['status']),
        ];

        $email = $this->isInternal($input) ? $input['email'] : '';

        $officeGroupId = $this->isInternal($input) ? $input['officeGroupId'] : null;

        $qualifications = Seq::fromArray($input['qualifications'] ?? [])
            ->map(fn (string $qualification) => OfficeQualification::from($qualification))
            ->toArray();

        $inputDwsGenericService = $input['dwsGenericService'];
        if (!$this->isInternal($input)) {
            $inputDwsGenericService['dwsAreaGradeId'] = '';
            Arr::forget($inputDwsGenericService, ['openedOn', 'designationExpiredOn']);
        }
        $dwsGenericService = $this->hasQualifications(
            $input,
            OfficeQualification::dwsHomeHelpService(),
            OfficeQualification::dwsVisitingCareForPwsd(),
            OfficeQualification::dwsOthers()
        )
            ? OfficeDwsGenericService::create($inputDwsGenericService)
            : null;

        $inputDwsCommAccompanyService = $input['dwsCommAccompanyService'];
        if (!$this->isInternal($input)) {
            Arr::forget($inputDwsCommAccompanyService, ['openedOn', 'designationExpiredOn']);
        }
        $dwsCommAccompanyService = $this->hasQualifications($input, OfficeQualification::dwsCommAccompany())
            ? OfficeDwsCommAccompanyService::create($inputDwsCommAccompanyService)
            : null;

        $inputLtcsCareManagementService = $input['ltcsCareManagementService'];
        if (!$this->isInternal($input)) {
            $inputLtcsCareManagementService['ltcsAreaGradeId'] = '';
            Arr::forget($inputLtcsCareManagementService, ['openedOn', 'designationExpiredOn']);
        }
        $ltcsCareManagementService = $this->hasQualifications($input, OfficeQualification::ltcsCareManagement())
            ? OfficeLtcsCareManagementService::create($inputLtcsCareManagementService)
            : null;

        $inputLtcsHomeVisitLongTermCareService = $input['ltcsHomeVisitLongTermCareService'];
        if (!$this->isInternal($input)) {
            $inputLtcsHomeVisitLongTermCareService['ltcsAreaGradeId'] = '';
            Arr::forget($inputLtcsHomeVisitLongTermCareService, ['openedOn', 'designationExpiredOn']);
        }
        $ltcsHomeVisitLongTermCareService = $this
            ->hasQualifications(
                $input,
                OfficeQualification::ltcsHomeVisitLongTermCare()
            )
            ? OfficeLtcsHomeVisitLongTermCareService::create($inputLtcsHomeVisitLongTermCareService)
            : null;

        $inputLtcsCompHomeVisitingService = $input['ltcsCompHomeVisitingService'];
        if (!$this->isInternal($input)) {
            Arr::forget($inputLtcsCompHomeVisitingService, ['openedOn', 'designationExpiredOn']);
        }
        $ltcsCompHomeVisitingService = $this->hasQualifications($input, OfficeQualification::ltcsCompHomeVisiting())
            ? OfficeLtcsCompHomeVisitingService::create($inputLtcsCompHomeVisitingService)
            : null;

        $inputPreventionService = $input['ltcsPreventionService'];
        if (!$this->isInternal($input)) {
            Arr::forget($inputPreventionService, ['openedOn', 'designationExpiredOn']);
        }
        $ltcsPreventionService = $this->hasQualifications($input, OfficeQualification::ltcsPrevention())
            ? OfficeLtcsPreventionService::fromAssoc($inputPreventionService)
            : null;

        return $values
            + compact(
                'email',
                'officeGroupId',
                'qualifications',
                'dwsGenericService',
                'dwsCommAccompanyService',
                'ltcsCareManagementService',
                'ltcsHomeVisitLongTermCareService',
                'ltcsCompHomeVisitingService',
                'ltcsPreventionService'
            );
    }

    /**
     * 自社内かどうかを判定.
     *
     * @param array $input
     * @return bool
     */
    private function isInternal(array $input): bool
    {
        return $input['purpose'] === Purpose::internal()->value();
    }

    /**
     * 特定の指定区分がパラメータに含まれるか判定.
     *
     * @param array $input
     * @param \Domain\Office\OfficeQualification[] $qualifications
     * @return bool
     */
    private function hasQualifications(array $input, OfficeQualification ...$qualifications): bool
    {
        return Seq::fromArray($qualifications)
            ->find(fn (OfficeQualification $x): bool => in_array($x->value(), $input['qualifications'], true))
            ->nonEmpty();
    }
}
