<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsCertificationBelongsToUserRule} のテスト.
 */
final class DwsCertificationBelongsToUserRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupDwsCertificationUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsCertificationBelongsToUser(): void
    {
        $this->should('pass rule', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->dwsCertifications[0]->id,
                        'userId' => $this->examples->users[1]->id,
                    ],
                    ['value' => 'dws_certification_belongs_to_user:userId,' . Permission::viewUsers()],
                )->passes()
            );
        });
        $this->should('return true when userId is empty', function (): void {
            $this->lookupDwsCertificationUseCase
                ->allows('handle')
                ->never();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->dwsCertifications[0]->id,
                    ],
                    ['value' => 'dws_certification_belongs_to_user:userId,' . Permission::viewUsers()],
                )->passes()
            );
        });
        $this->should('fail rule', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => $this->examples->dwsCertifications[1]->id,
                        'userId' => $this->examples->users[0]->id,
                    ],
                    ['value' => 'dws_certification_belongs_to_user:userId,' . Permission::viewUsers()],
                )->fails()
            );
        });
    }
}
