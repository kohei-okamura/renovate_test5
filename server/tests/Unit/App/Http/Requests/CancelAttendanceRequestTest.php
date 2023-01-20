<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CancelAttendanceRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Faker\Generator;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CancelAttendanceRequest} のテスト.
 */
class CancelAttendanceRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupAttendanceUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CancelAttendanceRequest $request;
    private Generator $faker;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelAttendanceRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->attendances[0]))
                ->byDefault();
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateAttendances(), $self->examples->attendances[1]->id)
                ->andReturn(Seq::from($self->examples->attendances[1]))
                ->byDefault();
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateAttendances(), $self->examples->attendances[6]->id)
                ->andReturn(Seq::from($self->examples->attendances[6]))
                ->byDefault();

            $self->request = new CancelAttendanceRequest(); // Parameter必須なのでnewする

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
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->routeParam() + $this->defaultInput());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when id is canceled' => [
                ['id' => ['存在しないIDまたはキャンセル済みのIDが含まれています。']],
                ['id' => $this->examples->attendances[6]->id],
                ['id' => $this->examples->attendances[1]->id],
            ],
            'when reason is empty' => [
                ['reason' => ['入力してください。']],
                ['reason' => ''],
                ['reason' => 'キャンセル理由'],
            ],
            'when reason is not string' => [
                ['reason' => ['文字列で入力してください。']],
                ['reason' => 12345],
                ['reason' => 'キャンセル理由'],
            ],
            'when reason is over 255 letters' => [
                ['reason' => ['255文字以内で入力してください。']],
                ['reason' => $this->faker->numerify(str_repeat('#', 256))],
                ['reason' => $this->faker->numerify(str_repeat('#', 255))],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
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
        return ['reason' => 'キャンセル理由'];
    }

    /**
     * ルートパラメーター.
     *
     * @return array
     */
    private function routeParam(): array
    {
        return [
            'id' => $this->examples->attendances[1]->id,
        ];
    }
}
