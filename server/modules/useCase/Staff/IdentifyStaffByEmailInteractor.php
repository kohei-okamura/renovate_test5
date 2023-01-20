<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\StaffFinder;
use Domain\Staff\StaffStatus;
use ScalikePHP\Option;

/**
 * メールアドレスを用いたスタッフ情報取得ユースケース実装.
 */
final class IdentifyStaffByEmailInteractor implements IdentifyStaffByEmailUseCase
{
    private StaffFinder $finder;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffFinder $finder
     */
    public function __construct(StaffFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $email): Option
    {
        return $this->finder->find(
            [
                'email' => $email,
                'organizationId' => $context->organization->id,
                'statuses' => [StaffStatus::provisional(), StaffStatus::active()],
                'isEnable' => true,
            ],
            [
                'all' => true,
                'sortBy' => 'id',
            ]
        )->list->headOption();
    }
}
