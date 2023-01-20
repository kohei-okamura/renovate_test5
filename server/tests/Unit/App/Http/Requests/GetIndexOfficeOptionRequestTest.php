<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\GetIndexOfficeOptionRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Closure;
use Domain\Context\Context;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use Traversable;

/**
 * {@link \App\Http\Requests\GetIndexOfficeOptionRequest} のテスト.
 */
final class GetIndexOfficeOptionRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected GetIndexOfficeOptionRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), anInstanceOf(Permission::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->request = new GetIndexOfficeOptionRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::from($self->examples->roles[0]->copy([
                    'isSystemAdmin' => false,
                    'permissions' => [Permission::listInternalOffices()],
                ])),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should(
            'pass',
            function (?Closure $f = null): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->passes());
            },
            ['examples' => iterator_to_array($this->passes())]
        );
        $this->should(
            'fail',
            function (?Closure $f, array $errors): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->fails());
                $this->assertSame($errors, $validator->errors()->toArray());
            },
            ['examples' => iterator_to_array($this->fails())]
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @param ?Closure $f
     * @return array
     */
    private function createInput(?Closure $f): array
    {
        $input = [
            'permission' => Permission::listInternalOffices()->value(),
            'userId' => $this->examples->users[0]->id,
            'purpose' => Purpose::internal()->value(),
            'qualifications' => [
                OfficeQualification::dwsHomeHelpService()->value(),
                OfficeQualification::dwsVisitingCareForPwsd()->value(),
            ],
        ];
        if ($f !== null) {
            $f($input);
        }
        return $input;
    }

    /**
     * バリデーションに成功するパターン.
     *
     * @returns \Generator
     */
    private function passes(): Traversable
    {
        yield 'when the permission is not given' => [
            function (array &$input): void {
                unset($input['permission']);
            },
        ];
        yield 'when the permission is empty' => [
            function (array &$input): void {
                $input['permission'] = '';
            },
        ];
        yield 'when the userId is not given' => [
            function (array &$input): void {
                unset($input['userId']);
            },
        ];
        yield 'when the userId is empty' => [
            function (array &$input): void {
                $input['userId'] = '';
            },
        ];
        yield 'when the purpose is not given' => [
            function (array &$input): void {
                unset($input['purpose']);
            },
        ];
        yield 'when the purpose is empty' => [
            function (array &$input): void {
                $input['purpose'] = '';
            },
        ];
        yield 'when the qualifications is not given' => [
            function (array &$input): void {
                unset($input['qualifications']);
            },
        ];
        yield 'when the qualifications is empty' => [
            function (array &$input): void {
                $input['qualifications'] = '';
            },
        ];
    }

    /**
     * バリデーションに失敗するパターン.
     *
     * @returns \Generator
     */
    private function fails(): Traversable
    {
        yield 'when the permission is not a valid enum value' => [
            function (array &$input): void {
                $input['permission'] = self::INVALID_ENUM_VALUE;
            },
            ['permission' => ['権限を指定してください。']],
        ];
        yield 'when the permission is not authorized to the staff' => [
            function (array &$input): void {
                $input['permission'] = Permission::createInternalOffices()->value();
            },
            ['permission' => ['権限を持っていません。']],
            ['permission' => Permission::createInternalOffices()->value()],
        ];
        yield 'when the userId is not a valid id' => [
            function (array &$input): void {
                $input['userId'] = self::NOT_EXISTING_ID;
            },
            ['userId' => ['正しい値を入力してください。']],
        ];
        yield 'when the qualifications is not an array' => [
            function (array &$input): void {
                $input['qualifications'] = 'error';
            },
            ['qualifications' => ['配列にしてください。']],
        ];
        yield 'when an element in the qualifications is not a valid enum value' => [
            function (array &$input): void {
                $input['qualifications'] = [
                    (string)self::INVALID_ENUM_VALUE,
                    OfficeQualification::dwsVisitingCareForPwsd()->value(),
                ];
            },
            ['qualifications.0' => ['事業所：指定区分を指定してください。']],
        ];
        yield 'when an element in the isCommunityGeneralSupportCenter is not bool' => [
            function (array &$input): void {
                $input['isCommunityGeneralSupportCenter'] = 'error';
            },
            ['isCommunityGeneralSupportCenter' => ['trueかfalseにしてください。']],
        ];
    }
}
