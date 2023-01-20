<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContractFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserStatusCanUpdateToFalseRule} のテスト.
 */
final class UserStatusCanUpdateToFalseRuleTest extends Test
{
    use ContractFinderMixin;
    use ExamplesConsumer;
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
            $self->contractFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$self->examples->contracts[0]], Pagination::create([])))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserStatusCanUpdateToFalse(): void
    {
        $this->should('pass when No Contract Exists', function (): void {
            $this->contractFinder
                ->expects('find')
                ->andReturn(FinderResult::from([], Pagination::create([])));
            $validator = $this->buildCustomValidator(
                [
                    'id' => $this->examples->users[0]->id,
                    'isEnabled' => false,
                ],
                ['isEnabled' => 'user_status_can_update_to_false:id']
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when isEnabled is true', function (): void {
            $this->contractFinder
                ->expects('find')
                ->never();
            $validator = $this->buildCustomValidator(
                [
                    'id' => $this->examples->users[0]->id,
                    'isEnabled' => true,
                ],
                ['isEnabled' => 'user_status_can_update_to_false:id']
            );
            $this->assertTrue($validator->passes());
        });
    }
}
