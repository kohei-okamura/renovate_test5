<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\CustomValidator;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LookupWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\EntityExistsValidator} Test.
 */
class EntityExistsValidatorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupDwsAreaGradeUseCaseMixin;
    use LookupDwsCertificationUseCaseMixin;
    use LookupDwsProjectServiceMenuUseCaseMixin;
    use LookupInvitationUseCaseMixin;
    use LookupLtcsAreaGradeUseCaseMixin;
    use LookupLtcsProjectServiceMenuUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupShiftUseCaseMixin;
    use LookupAttendanceUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LookupUserBillingUseCaseMixin;
    use LookupWithdrawalTransactionUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EntityExistsValidatorTest $self): void {
            // 余分なLookupUseCaseが使用されていないことを確認するために、allows() しない
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsAreaGradeExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->dwsAreaGrades[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->dwsAreaGrades[2], $this->examples->dwsAreaGrades[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->dwsAreaGrades[1];
            $this->lookupDwsAreaGradeUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsProjectServiceMenuExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->dwsProjectServiceMenus[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('dws_project_service_menu_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->dwsProjectServiceMenus[2], $this->examples->dwsProjectServiceMenus[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('dws_project_service_menu_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupDwsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('dws_project_service_menu_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('dws_project_service_menu_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->dwsProjectServiceMenus[1];
            $this->lookupDwsProjectServiceMenuUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('dws_project_service_menu_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateInvitationExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->invitations[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('invitation_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->invitations[2], $this->examples->invitations[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('invitation_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('invitation_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('invitation_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->invitations[1];
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('invitation_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsAreaGradeExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->ltcsAreaGrades[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_area_grade_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->ltcsAreaGrades[2], $this->examples->ltcsAreaGrades[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_area_grade_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsAreaGradeUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('ltcs_area_grade_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->ltcsAreaGrades[1];
            $this->lookupLtcsAreaGradeUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_area_grade_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsProjectServiceMenuExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->ltcsProjectServiceMenus[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_project_service_menu_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->ltcsProjectServiceMenus[2], $this->examples->ltcsProjectServiceMenus[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_project_service_menu_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupLtcsProjectServiceMenuUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('ltcs_project_service_menu_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('ltcs_project_service_menu_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->ltcsProjectServiceMenus[1];
            $this->lookupLtcsProjectServiceMenuUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('ltcs_project_service_menu_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateOfficeExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->offices[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists:' . Permission::viewInternalOffices(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->offices[2], $this->examples->offices[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists:' . Permission::viewInternalOffices(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('office_exists:' . Permission::viewInternalOffices(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->offices[1];
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists:' . Permission::viewInternalOffices(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateOfficeExistsIgnorePermission(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->offices[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->getOfficeListUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists_ignore_permissions', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->offices[2], $this->examples->offices[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->getOfficeListUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists_ignore_permissions', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->getOfficeListUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('office_exists_ignore_permissions', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('dws_area_grade_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->offices[1];
            $this->getOfficeListUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_exists_ignore_permissions', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateOfficeGroupExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->officeGroups[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_group_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->officeGroups[2], $this->examples->officeGroups[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('office_group_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('office_group_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('office_group_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->officeGroups[1];
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('office_group_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateShiftExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->shifts[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('shift_exists:' . Permission::viewShifts(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->shifts[2], $this->examples->shifts[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('shift_exists:' . Permission::viewShifts(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('shift_exists:' . Permission::viewShifts(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('shift_exists:' . Permission::viewShifts(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->shifts[1];
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewShifts(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('shift_exists:' . Permission::viewShifts(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateAttendanceExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->attendances[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('attendance_exists:' . Permission::viewAttendances(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->attendances[2], $this->examples->attendances[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('attendance_exists:' . Permission::viewAttendances(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('attendance_exists:' . Permission::viewAttendances(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('attendance_exists:' . Permission::viewAttendances(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->attendances[1];
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewAttendances(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('attendance_exists:' . Permission::viewAttendances(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateRoleExists(): void
    {
        $this->should('pass id is existed entity', function (): void {
            $entity = $this->examples->roles[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupRoleUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('role_exists', $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->roles[2], $this->examples->roles[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupRoleUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('role_exists', [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupRoleUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('role_exists', self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('role_exists', 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->roles[1];
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('role_exists', $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateStaffExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->staffs[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupStaffUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('staff_exists:' . Permission::viewStaffs(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->staffs[2], $this->examples->staffs[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupStaffUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('staff_exists:' . Permission::viewStaffs(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupStaffUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('staff_exists:' . Permission::viewStaffs(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('staff_exists:' . Permission::viewStaffs(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->staffs[1];
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewStaffs(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('staff_exists:' . Permission::viewStaffs(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->users[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('user_exists:' . Permission::viewUsers(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->users[2], $this->examples->users[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('user_exists:' . Permission::viewUsers(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('user_exists:' . Permission::viewUsers(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('user_exists:' . Permission::viewUsers(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->users[1];
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('user_exists:' . Permission::viewUsers(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBillingExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->userBillings[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('user_billing_exists:' . Permission::viewUserBillings(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->userBillings[2], $this->examples->userBillings[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('user_billing_exists:' . Permission::viewUserBillings(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->makeValidator('user_billing_exists:' . Permission::viewUserBillings(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('user_billing_exists:' . Permission::viewUserBillings(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->userBillings[1];
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('user_billing_exists:' . Permission::viewUserBillings(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateWithdrawalTransactionExists(): void
    {
        $this->should('pass when the entity of specified id is existed', function (): void {
            $entity = $this->examples->userBillings[0];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('withdrawal_transaction_exists:' . Permission::downloadWithdrawalTransactions(), $entity->id)
                    ->passes()
            );
        });
        $this->should('pass with multiple ids', function (): void {
            $entity = [$this->examples->userBillings[2], $this->examples->userBillings[3]];
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(Seq::fromArray($entity));

            $this->assertTrue(
                $this->makeValidator('withdrawal_transaction_exists:' . Permission::downloadWithdrawalTransactions(), [$entity[0]->id, $entity[1]->id])
                    ->passes()
            );
        });
        $this->should('fail when the entity of specified id is not existed', function (): void {
            // 途中で終わっていないことを検証するために expects() する
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue(
                $this->makeValidator('withdrawal_transaction_exists:' . Permission::downloadWithdrawalTransactions(), self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
        $this->should('fail when id is string', function (): void {
            $this->assertTrue(
                $this->makeValidator('withdrawal_transaction_exists:' . Permission::downloadWithdrawalTransactions(), 'STRING')
                    ->fails()
            );
        });
        $this->should('use UseCase with expected arguments', function (): void {
            $entity = $this->examples->userBillings[1];
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->with($this->context, Permission::downloadWithdrawalTransactions(), $entity->id)
                ->andReturn(Seq::from($entity));

            $this->assertTrue(
                $this->makeValidator('withdrawal_transaction_exists:' . Permission::downloadWithdrawalTransactions(), $entity->id)
                    ->passes()
            );
        });
    }

    /**
     * Validatorを作る.
     *
     * @param string $validation バリデーションルール
     * @param mixed $value パラメータ(int, array(複数の場合))
     * @return \App\Validations\CustomValidator
     */
    private function makeValidator(string $validation, $value): CustomValidator
    {
        return CustomValidator::make(
            $this->context,
            ['param' => $value],
            ['param' => $validation],
            [],
            []
        );
    }
}
