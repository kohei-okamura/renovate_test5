/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { wait } from '@zinger/helpers'
import { Task } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { isNotTaskDefined, ZingerEcsServiceType } from '~aws/scripts/utils/is-not-task-defined'
import { onError } from '~aws/scripts/utils/on-error'
import { outputAsJson } from '~aws/scripts/utils/output-as-json'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'
import { ZINGER_ECS_CLUSTER, ZINGER_ECS_QUEUE, ZINGER_ECS_SERVICE } from '~aws/variables'

type Options = RunCommandOptions & {
  task: Task
}

// 最大リトライ回数 = 60
const MAX_RETRIES = 60

// リトライ間隔 = 10秒
const retryIntervalMilliseconds = 10000

const waitForTaskDeleted = async (service: ZingerEcsServiceType, retry: number): Promise<boolean> => {
  if (retry <= 0) {
    return false
  } else if (await isNotTaskDefined(service)) {
    return true
  } else {
    await wait(retryIntervalMilliseconds)
    return waitForTaskDeleted(service, retry - 1)
  }
}

const main = (options: Options) => runCommand(options, withConfirm(
  'DELETE ECS SERVICES?',
  async () => {
    const ecs = createEcsService()
    const service: ZingerEcsServiceType = (options.task === 'queue') ? ZINGER_ECS_QUEUE : ZINGER_ECS_SERVICE
    const data = runAwsCommand(() => ecs.deleteService({
      cluster: ZINGER_ECS_CLUSTER,
      service,
      force: true
    }))
    outputAsJson(data)

    await waitForTaskDeleted(service, MAX_RETRIES)
      ? console.log('ECS services deleted successfully.')
      : onError('Failed to delete ECS services.')
  }
))

const options = createCommand()
  .requiredOption('-t, --task [task]', 'e.g. queue, service')
  .parse(process.argv)
  .opts<Options>()

main(options)
