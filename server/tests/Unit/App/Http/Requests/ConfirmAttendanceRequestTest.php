<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\ConfirmAttendanceRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Attendance;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * ConfirmAttendanceRequest のテスト.
 */
class ConfirmAttendanceRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupAttendanceUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const TARGET_PARAMETERS = ['ids' => [1, 2]];

    protected ConfirmAttendanceRequest $request;
    private Attendance $attendance;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmAttendanceRequestTest $self): void {
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateAttendances(), 1, 2)
                ->andReturn(Seq::fromArray([$self->examples->attendances[0], $self->examples->attendances[1]]))
                ->byDefault();
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateAttendances(),
                    $self->examples->attendances[0]->id
                )
                ->andReturn(Seq::from($self->examples->attendances[0]));
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->request = new ConfirmAttendanceRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->attendance = $self->examples->attendances[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
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
            $validator = $this->request->createValidatorInstance(self::TARGET_PARAMETERS);
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when ids is empty' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => self::TARGET_PARAMETERS['ids']],
            ],
            'when ids is not array' => [
                ['ids' => ['配列にしてください。']],
                ['ids' => 1],
                ['ids' => self::TARGET_PARAMETERS['ids']],
            ],
            'when ids includes INVALID_ID' => [
                ['ids' => ['正しい値を入力してください。']],
                ['ids' => [self::NOT_EXISTING_ID]],
                ['ids' => [$this->examples->attendances[0]->id]],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
    }
}
