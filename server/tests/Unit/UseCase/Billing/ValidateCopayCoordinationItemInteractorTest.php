<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ValidateCopayCoordinationItemInteractor;

/**
 * {@link \UseCase\Billing\ValidateCopayCoordinationItemInteractor} Test.
 */
class ValidateCopayCoordinationItemInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private Seq $items;
    private array $appropriatedItemsValue;
    private array $notCoordinatedItemsValue;
    private array $coordinatedItemsValue;
    private CopayCoordinationResult $result;
    private int $userId;
    private int $dwsBillingId;
    private int $dwsBillingBundleId;
    private ValidateCopayCoordinationItemInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ValidateCopayCoordinationItemInteractorTest $self): void {
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();

            $self->items = Seq::fromArray($self->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ]);
            $self->appropriatedItemsValue = [
                [
                    'itemNumber' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->itemNumber,
                    'officeId' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->office->officeId,
                    'subtotal' => [
                        'fee' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->subtotal->fee,
                        'copay' => $self->examples->dwsCertifications[0]->copayLimit + 1,
                        'coordinatedCopay' => $self->examples->dwsCertifications[0]->copayLimit,
                    ],
                ],
                [
                    'itemNumber' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->itemNumber,
                    'officeId' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->office->officeId,
                    'subtotal' => [
                        'fee' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->subtotal->fee,
                        'copay' => $self->examples->dwsCertifications[0]->copayLimit + 1,
                        'coordinatedCopay' => 0,
                    ],
                ],
            ];
            $self->notCoordinatedItemsValue = $self->items->toArray();
            $self->coordinatedItemsValue = [
                [
                    'itemNumber' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->itemNumber,
                    'officeId' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->office->officeId,
                    'subtotal' => [
                        'fee' => $self->examples->dwsBillingCopayCoordinations[0]->items[0]->subtotal->fee,
                        'copay' => $self->examples->dwsCertifications[0]->copayLimit - 1,
                        'coordinatedCopay' => $self->examples->dwsCertifications[0]->copayLimit - 1,
                    ],
                ],
                [
                    'itemNumber' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->itemNumber,
                    'officeId' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->office->officeId,
                    'subtotal' => [
                        'fee' => $self->examples->dwsBillingCopayCoordinations[0]->items[1]->subtotal->fee,
                        'copay' => $self->examples->dwsCertifications[0]->copayLimit - 1,
                        'coordinatedCopay' => 1,
                    ],
                ],
            ];
            $self->result = $self->examples->dwsBillingCopayCoordinations[0]->result;
            $self->userId = $self->examples->dwsBillingCopayCoordinations[0]->user->userId;
            $self->dwsBillingId = $self->examples->dwsBillings[0]->id;
            $self->dwsBillingBundleId = $self->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId;
            $self->interactor = app(ValidateCopayCoordinationItemInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return true when copay is null', function (): void {
            $items = Seq::fromArray($this->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => null,
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ]);

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('return true when coordinatedCopay is null', function (): void {
            $items = Seq::fromArray($this->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => null,
                    ],
                ]);

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('return true when officeId is null', function (): void {
            $items = Seq::fromArray($this->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => null,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ]);

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('return true when copay is not int', function (): void {
            $items = Seq::fromArray($this->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => 'error',
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ]);

            $this->interactor->handle(
                $this->context,
                $items,
                $this->result,
                $this->userId,
                $this->dwsBillingId,
                $this->dwsBillingBundleId,
                Permission::createBillings()
            );
        });
        $this->should('return true when coordinatedCopay is not int', function (): void {
            $items = Seq::fromArray($this->examples->dwsBillingCopayCoordinations[0]->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => 'error',
                    ],
                ]);

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), $this->dwsBillingId)
                ->andReturn(Seq::from($this->examples->dwsBillings[0]));

            $this->interactor->handle(
                $this->context,
                $this->items,
                $this->result,
                $this->userId,
                $this->dwsBillingId,
                $this->dwsBillingBundleId,
                Permission::createBillings()
            );
        });
        $this->should('return true when LookupDwsBillingUseCase return empty seq', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $this->items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), $this->dwsBillingId, $this->dwsBillingBundleId)
                ->andReturn(Seq::from($this->examples->dwsBillingBundles[0]));

            $this->interactor->handle(
                $this->context,
                $this->items,
                $this->result,
                $this->userId,
                $this->dwsBillingId,
                $this->dwsBillingBundleId,
                Permission::createBillings()
            );
        });
        $this->should('return true when LookupDwsBillingBundleUseCase return empty seq', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $this->items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('use IdentifyDwsCertificationUseCase', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->userId, $this->examples->dwsBillingBundles[0]->providedIn)
                ->andReturn(Option::from($this->examples->dwsCertifications[0]));

            $this->interactor->handle(
                $this->context,
                $this->items,
                $this->result,
                $this->userId,
                $this->dwsBillingId,
                $this->dwsBillingBundleId,
                Permission::createBillings()
            );
        });
        $this->should('return true when IdentifyDwsCertificationUseCase return none', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    $this->items,
                    $this->result,
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should('return true when result is appropriated and validation is passed', function (): void {
            $this->assertTrue(
                $this->interactor->handle(
                    $this->context,
                    Seq::fromArray($this->appropriatedItemsValue),
                    CopayCoordinationResult::appropriated(),
                    $this->userId,
                    $this->dwsBillingId,
                    $this->dwsBillingBundleId,
                    Permission::createBillings()
                )
            );
        });
        $this->should(
            'return false when result is appropriated and items does not contain copay coordination office',
            function (): void {
                $this->appropriatedItemsValue[0]['officeId'] = self::NOT_EXISTING_ID;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->appropriatedItemsValue),
                        CopayCoordinationResult::appropriated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            "return false when result is appropriated and copay coordination office's copay is less than copayLimit",
            function (): void {
                $this->appropriatedItemsValue[0]['subtotal']['copay'] = $this->examples->dwsCertifications[0]->copayLimit - 1;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->appropriatedItemsValue),
                        CopayCoordinationResult::appropriated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            "return false when result is appropriated and copay coordination office's coordinatedCopay is not equal to copayLimit",
            function (): void {
                $this->appropriatedItemsValue[0]['subtotal']['coordinatedCopay'] = $this->examples->dwsCertifications[0]->copayLimit + 1;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->appropriatedItemsValue),
                        CopayCoordinationResult::appropriated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            "return false when result is appropriated and not copay coordination office's coordinatedCopay is not 0",
            function (): void {
                $this->appropriatedItemsValue[1]['subtotal']['coordinatedCopay'] = 100;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->appropriatedItemsValue),
                        CopayCoordinationResult::appropriated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return true when result is notCoordinated and validation is passed',
            function (): void {
                $this->assertTrue(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->notCoordinatedItemsValue),
                        CopayCoordinationResult::notCoordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return false when result is notCoordinated and sum of copay is greater than copayLimit',
            function (): void {
                $this->notCoordinatedItemsValue[0]['subtotal']['copay'] = $this->examples->dwsCertifications[0]->copayLimit - 1;
                $this->notCoordinatedItemsValue[1]['subtotal']['copay'] = 2;
                $this->notCoordinatedItemsValue[0]['subtotal']['coordinatedCopay'] = $this->examples->dwsCertifications[0]->copayLimit - 1;
                $this->notCoordinatedItemsValue[1]['subtotal']['coordinatedCopay'] = 2;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->notCoordinatedItemsValue),
                        CopayCoordinationResult::notCoordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return false when result is notCoordinated and copay is not equal to coordinatedCopay',
            function (): void {
                $this->notCoordinatedItemsValue[1]['subtotal']['coordinatedCopay'] = $this->notCoordinatedItemsValue[1]['subtotal']['copay'] + 1;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->notCoordinatedItemsValue),
                        CopayCoordinationResult::notCoordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return true when result is coordinated and validation is passed',
            function (): void {
                $this->assertTrue(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->coordinatedItemsValue),
                        CopayCoordinationResult::coordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            "return false when result is coordinated and copay coordination office's copay is greater equal copayLimit",
            function (): void {
                $this->coordinatedItemsValue[0]['subtotal']['copay'] = $this->examples->dwsCertifications[0]->copayLimit;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->coordinatedItemsValue),
                        CopayCoordinationResult::coordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            "return false when result is coordinated and copay coordination office's copay is not equal to coordinatedCopay",
            function (): void {
                $this->coordinatedItemsValue[0]['subtotal']['copay'] = $this->coordinatedItemsValue[0]['subtotal']['coordinatedCopay'] + 1;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->coordinatedItemsValue),
                        CopayCoordinationResult::coordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return false when result is coordinated and sum of copay is less than copayLimit',
            function (): void {
                $this->coordinatedItemsValue[0]['subtotal']['copay'] = $this->examples->dwsCertifications[0]->copayLimit - 1;
                $this->coordinatedItemsValue[1]['subtotal']['copay'] = 0;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->coordinatedItemsValue),
                        CopayCoordinationResult::coordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
        $this->should(
            'return false when result is coordinated and sum of coordinatedCopay is not equal to copayLimit',
            function (): void {
                $this->coordinatedItemsValue[0]['subtotal']['coordinatedCopay'] = $this->examples->dwsCertifications[0]->copayLimit - 1;
                $this->coordinatedItemsValue[1]['subtotal']['coordinatedCopay'] = 0;

                $this->assertFalse(
                    $this->interactor->handle(
                        $this->context,
                        Seq::fromArray($this->coordinatedItemsValue),
                        CopayCoordinationResult::coordinated(),
                        $this->userId,
                        $this->dwsBillingId,
                        $this->dwsBillingBundleId,
                        Permission::createBillings()
                    )
                );
            }
        );
    }
}
