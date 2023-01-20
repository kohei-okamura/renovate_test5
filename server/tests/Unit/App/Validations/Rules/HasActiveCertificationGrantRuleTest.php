<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\HasActiveCertificationGrantRule} のテスト.
 */
final class HasActiveCertificationGrantRuleTest extends Test
{
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsProvisionReports[0]->copy([
                    'results' => [
                        $self->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::physicalCare(),
                        ]),
                        $self->examples->dwsProvisionReports[0]->results[1]->copy([
                            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        ]),
                    ],
                ])))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsCertifications[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    /**
     * @test
     * @return void
     */
    public function describe_validateHasActiveCertificationGrant(): void
    {
        $this->should('pass when status is not fixed', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::inProgress()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when provision report does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::none());
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when dws certification does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('pass when home help service results exist and active grant exists', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = Carbon::parse('2020-10');
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $this->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::physicalCare(),
                        ]),
                    ],
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'grants' => [
                        $this->examples->dwsCertifications[0]->grants[0]->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            'activatedOn' => $providedIn->subMonth(),
                            'deactivatedOn' => $providedIn->addMonth(),
                        ]),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when home help service results exist and active grant does not exists', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = Carbon::parse('2020-10');
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $this->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::physicalCare(),
                        ]),
                    ],
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'grants' => [
                        $this->examples->dwsCertifications[0]->grants[0]->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            'activatedOn' => $providedIn->addMonth(),
                            'deactivatedOn' => $providedIn->addMonths(2),
                        ]),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('pass when visiting care for pwsd results exist and active grant exists', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = Carbon::parse('2020-10');
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $this->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        ]),
                    ],
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'grants' => [
                        $this->examples->dwsCertifications[0]->grants[0]->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                            'activatedOn' => $providedIn->subMonth(),
                            'deactivatedOn' => $providedIn->addMonth(),
                        ]),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when visiting care for pwsd results exist and active grant does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = Carbon::parse('2020-10');
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $this->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        ]),
                    ],
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'grants' => [
                        $this->examples->dwsCertifications[0]->grants[0]->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                            'activatedOn' => $providedIn->addMonth(),
                            'deactivatedOn' => $providedIn->addMonths(2),
                        ]),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('pass when visiting care for pwsd results and home help service results do not exist and active grant does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = Carbon::parse('2020-10');
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => Carbon::parse('2020-10'),
                    'results' => [
                        $this->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::ownExpense(),
                        ]),
                    ],
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'grants' => [],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                ['userId' => "has_active_certification_grant:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
    }
}
