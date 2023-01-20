<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Codeception\Util\HttpCode;
use Domain\Staff\Staff;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /** @var bool ログが検証されたかのフラグ */
    private bool $isLogAssert;

    /**
     * ログの件数を検証する(wrapper).
     *
     * @param int $expected
     */
    public function seeLogCount(int $expected): void
    {
        $this->isLogAssert = true;
        $this->seeLogCountExec($expected);
    }

    /**
     * ログメッセージを検証する(wrapper).
     *
     * @param int $pos
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function seeLogMessage(int $pos, string $level, string $message, array $context = []): void
    {
        $this->isLogAssert = true;
        $this->seeLogMessageExec($pos, $level, $message, $context);
    }

    /**
     * テストをスキップする(wrapper).
     *
     * @param string $message
     */
    public function skipTest(string $message = ''): void
    {
        $this->isLogAssert = true; // テストは実行しないのでログ検証は済みにしてしまう
        $this->skipTestExec($message);
    }

    /**
     * ログ検証済みをクリアする.
     */
    public function resetAssertLog(): void
    {
        $this->isLogAssert = false;
    }

    /**
     * ログ検証済みかを確認する.
     */
    public function checkAssertLog(): void
    {
        $this->assertTrue($this->isLogAssert, 'ログのアサートがされていません');
    }

    /**
     * 指定したStaffでログインとする.
     *
     * @param Staff $staff
     */
    public function actingAs(Staff $staff): void
    {
        $this->sendPOST('/sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
        ]);
        $this->seeResponseCodeIs(HttpCode::CREATED);
        $this->setCookieFromResponse();
    }
}
