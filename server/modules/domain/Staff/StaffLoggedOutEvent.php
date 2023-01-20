<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Context\Context;
use Domain\Event\Event;
use ScalikePHP\Option;

/**
 * スタッフログアウトイベント.
 */
final class StaffLoggedOutEvent extends Event
{
    private Option $rememberTokenId;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \ScalikePHP\Option $rememberTokenId
     */
    public function __construct(Context $context, Option $rememberTokenId)
    {
        parent::__construct($context);
        $this->rememberTokenId = $rememberTokenId;
    }

    /**
     * スタッフリメンバートークンのIDを取得する.
     */
    public function rememberTokenId(): Option
    {
        return $this->rememberTokenId;
    }
}
