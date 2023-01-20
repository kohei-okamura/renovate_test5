/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { runEcsTask } from '~aws/scripts/utils/run-ecs-task'
import { ZINGER_APP_BATCH_TASK_DEFINITION } from '~aws/variables'

type Options = RunCommandOptions & {
  command: string
}

const main = (options: Options) => runCommand(options, async () => {
  await runEcsTask({
    command: options.command.split(/\s+/),
    containerName: 'app-batch',
    taskDefinition: ZINGER_APP_BATCH_TASK_DEFINITION
  })
})

const options = createCommand()
  .requiredOption('-c, --command [command]', 'command')
  .parse(process.argv)
  .opts<Options>()

main(options)
