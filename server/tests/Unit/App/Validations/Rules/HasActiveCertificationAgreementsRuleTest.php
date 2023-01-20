<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Schedule;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\HasActiveCertificationAgreementsRule} のテスト.
 */
final class HasActiveCertificationAgreementsRuleTest extends Test
{
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private DwsProvisionReport $provisionReport;
    private DwsCertification $certification;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $provisionReport = $self->examples->dwsProvisionReports[0];
            $self->provisionReport = $provisionReport->copy([
                'results' => [
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::physicalCare(),
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::housework(),
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::accompany(),
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                    $provisionReport->results[0]->copy([
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'movingDurationMinutes' => 30,
                        'schedule' => Schedule::create([
                            'date' => $provisionReport->providedIn->firstOfMonth()->addDays(10),
                        ]),
                    ]),
                ],
            ]);
            $self->certification = $self->examples->dwsCertifications[0]->copy([
                'agreements' => [
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::housework(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::accompanyWithPhysicalCare(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::accompany(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                    DwsCertificationAgreement::create([
                        'officeId' => $self->provisionReport->officeId,
                        'dwsCertificationAgreementType' => DwsCertificationAgreementType::outingSupportForPwsd(),
                        'agreedOn' => $self->provisionReport->providedIn->subMonth(),
                        'expiredOn' => $self->provisionReport->providedIn->addMonth(),
                    ]),
                ],
            ]);
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->provisionReport))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->certification))
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
    public function describe_validateHasActiveCertificationAgreements(): void
    {
        $this->should('use GetDwsProvisionReportUseCase', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some($this->provisionReport));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $validator->validate();
        });
        $this->should('pass when provision report does not exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('use IdentifyDwsCertificationUseCase', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $userId, equalTo($providedIn))
                ->andReturn(Option::some($this->certification));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $validator->validate();
        });
        $this->should('fail when any agreements do not exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any physicalCare agreements do not exist although physicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::physicalCare())
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any physicalCare agreements with active agreedOn do not exist although physicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::physicalCare()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::physicalCare()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any physicalCare agreements with active expiredOn do not exist although physicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::physicalCare()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::physicalCare()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any housework agreements do not exist although housework results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::housework())
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any housework agreements with active agreedOn do not exist although housework results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::housework()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::housework()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any housework agreements with active expiredOn do not exist although housework results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::housework()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::housework()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompanyWithPhysicalCare agreements do not exist although accompanyWithPhysicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompanyWithPhysicalCare())
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompanyWithPhysicalCare agreements with active agreedOn do not exist although accompanyWithPhysicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompanyWithPhysicalCare()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::accompanyWithPhysicalCare()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompanyWithPhysicalCare agreements with active expiredOn do not exist although accompanyWithPhysicalCare results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompanyWithPhysicalCare()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::accompanyWithPhysicalCare()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompany agreements do not exist although accompany results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompany())
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompany agreements with active agreedOn do not exist although accompany results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompany()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::accompany()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any accompany agreements with active expiredOn do not exist although accompany results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::accompany()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::accompany()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any visitingCareForPwsd agreements do not exist although visitingCareForPwsd results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => !in_array(
                            $x->dwsCertificationAgreementType,
                            [
                                DwsCertificationAgreementType::visitingCareForPwsd1(),
                                DwsCertificationAgreementType::visitingCareForPwsd2(),
                                DwsCertificationAgreementType::visitingCareForPwsd3(),
                            ],
                            true
                        ))
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any visitingCareForPwsd agreements with active agreedOn do not exist although visitingCareForPwsd results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => !in_array(
                                $x->dwsCertificationAgreementType,
                                [
                                    DwsCertificationAgreementType::visitingCareForPwsd1(),
                                    DwsCertificationAgreementType::visitingCareForPwsd2(),
                                    DwsCertificationAgreementType::visitingCareForPwsd3(),
                                ],
                                true
                            )
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => in_array(
                                $x->dwsCertificationAgreementType,
                                [
                                    DwsCertificationAgreementType::visitingCareForPwsd1(),
                                    DwsCertificationAgreementType::visitingCareForPwsd2(),
                                    DwsCertificationAgreementType::visitingCareForPwsd3(),
                                ],
                                true
                            )
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any visitingCareForPwsd agreements with active expiredOn do not exist although visitingCareForPwsd results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => !in_array(
                                $x->dwsCertificationAgreementType,
                                [
                                    DwsCertificationAgreementType::visitingCareForPwsd1(),
                                    DwsCertificationAgreementType::visitingCareForPwsd2(),
                                    DwsCertificationAgreementType::visitingCareForPwsd3(),
                                ],
                                true
                            )
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => in_array(
                                $x->dwsCertificationAgreementType,
                                [
                                    DwsCertificationAgreementType::visitingCareForPwsd1(),
                                    DwsCertificationAgreementType::visitingCareForPwsd2(),
                                    DwsCertificationAgreementType::visitingCareForPwsd3(),
                                ],
                                true
                            )
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any outingSupportForPwsd agreements do not exist although visitingCareForPwsd results with moving exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => Seq::fromArray($this->certification->agreements)
                        ->filter(fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::outingSupportForPwsd())
                        ->toArray(),
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any outingSupportForPwsd agreements with active agreedOn do not exist although visitingCareForPwsd results with moving exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::outingSupportForPwsd()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::outingSupportForPwsd()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'agreedOn' => $providedIn->addMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when any outingSupportForPwsd agreements with active expiredOn do not exist although outingSupportForPwsd results exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->certification->copy([
                    'agreements' => [
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType !== DwsCertificationAgreementType::outingSupportForPwsd()
                        ),
                        ...Seq::fromArray($this->certification->agreements)->filter(
                            fn (DwsCertificationAgreement $x): bool => $x->dwsCertificationAgreementType === DwsCertificationAgreementType::outingSupportForPwsd()
                        )->map(fn (DwsCertificationAgreement $x) => $x->copy([
                            'expiredOn' => $providedIn->subMonth(),
                        ])),
                    ],
                ])));

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('pass when active agreements exist', function (): void {
            $officeId = $this->provisionReport->officeId;
            $userId = $this->provisionReport->userId;
            $providedIn = $this->provisionReport->providedIn;

            $validator = $this->buildCustomValidator(
                ['userId' => $userId],
                ['userId' => "has_active_certification_agreements:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
            );
            $this->assertTrue($validator->passes());
        });
    }
}
