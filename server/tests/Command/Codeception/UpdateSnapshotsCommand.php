<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Command\Codeception;

use Codeception\Command\Run;
use Codeception\Command\Shared\Config;
use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * スナップショットを更新してテストを実行するカスタムコマンド.
 */
class UpdateSnapshotsCommand extends Run implements CustomCommandInterface
{
    use Config;

    /** {@inheritdoc} */
    public static function getCommandName()
    {
        return 'updateSnapshots';
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return 'Run with snapshot updates';
    }

    /** {@inheritdoc} */
    public function run(InputInterface $input, OutputInterface $output)
    {
        array_push($_SERVER['argv'], '--update-snapshots');
        return parent::run($input, $output);
    }
}
