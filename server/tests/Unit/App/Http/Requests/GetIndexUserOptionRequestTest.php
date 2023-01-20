<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\GetIndexUserOptionRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\GetIndexUserOptionRequest} のテスト.
 */
class GetIndexUserOptionRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected GetIndexUserOptionRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexUserOptionRequestTest $self): void {
            $self->request = new GetIndexUserOptionRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::from($self->examples->roles[0]->copy([
                    'isSystemAdmin' => false,
                    'permissions' => [Permission::listUsers()],
                ])),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturnUsing(function (Context $context, array $permission, int ...$ids) use ($self): Seq {
                    return Seq::fromArray($self->examples->offices)
                        ->filter(fn (Office $x): bool => in_array($x->id, $ids, true));
                });
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
            'when permission is empty' => [
                ['permission' => ['入力してください。']],
                ['permission' => ''],
                ['permission' => Permission::listUsers()->value()],
            ],
            'when unknown permission given' => [
                ['permission' => ['権限を指定してください。']],
                ['permission' => self::INVALID_ENUM_VALUE],
                ['permission' => Permission::listUsers()->value()],
            ],
            'when unauthorized permission given' => [
                ['permission' => ['権限を持っていません。']],
                ['permission' => Permission::createUsers()->value()],
                ['permission' => Permission::listUsers()->value()],
            ],
            'when officeIds contain not existing id' => [
                ['officeIds' => ['正しい値を入力してください。']],
                ['officeIds' => [self::NOT_EXISTING_ID, $this->examples->offices[0]->id]],
                ['officeIds' => []],
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
            'permission' => Permission::listUsers()->value(),
        ];
    }
}
