/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { runEcsTask } from '~aws/scripts/utils/run-ecs-task'
import { ZINGER_APP_MIGRATION_TASK_DEFINITION } from '~aws/variables'

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  await runEcsTask({
    command: ['php', 'artisan', 'migrate', '--force'],
    containerName: 'app-batch',
    taskDefinition: ZINGER_APP_MIGRATION_TASK_DEFINITION
  })
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)
