/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import * as AWS from 'aws-sdk'
import { randomBytes } from 'crypto'
import { AwsLogStream } from '~aws/scripts/utils/aws-log-stream'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { describeEcsNetworkConfiguration } from '~aws/scripts/utils/describe-ecs-network-configuration'
import { describeEcsTaskDefinition } from '~aws/scripts/utils/describe-ecs-task-definition'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_ECS_CLUSTER } from '~aws/variables'

type Request = AWS.ECS.Types.RunTaskRequest

type RunTaskRequestParams = {
  taskDefinition: string
  containerOverride: AWS.ECS.ContainerOverride
}
const runTaskRequest = async ({ taskDefinition, containerOverride }: RunTaskRequestParams): Promise<Request> => ({
  cluster: ZINGER_ECS_CLUSTER,
  launchType: 'FARGATE',
  networkConfiguration: await describeEcsNetworkConfiguration(),
  platformVersion: '1.4.0',
  taskDefinition,
  overrides: {
    containerOverrides: [containerOverride]
  }
})

const makeEndOfStreamIdentifier = () => randomBytes(16).reduce((p, i) => p + (i % 36).toString(36), '')

const makeCommand = (args: string[], endOfStreamIdentifier: string): string[] => {
  const command = [
    `sh -c "${(args.length > 0 ? args : ['php', 'artisan']).join(' ')}"`,
    'EXIT_CODE=$?',
    `echo "TASK FINISHED: $(echo -n ${endOfStreamIdentifier} | base64), EXIT_CODE: $EXIT_CODE"`
  ]
  return ['sh', '-c', command.join(';')]
}

export type RunTaskParams = {
  command: string[]
  containerName: string
  taskDefinition: string
}
const runTask = async ({ command, containerName, taskDefinition }: RunTaskParams, endOfStreamIdentifier: string) => {
  const ecs = createEcsService()
  const requestParams: RunTaskRequestParams = {
    taskDefinition,
    containerOverride: {
      name: containerName,
      command: makeCommand(command, endOfStreamIdentifier)
    }
  }
  const request = await runTaskRequest(requestParams)
  return runAwsCommand(() => ecs.runTask(request))
}

type GetLogOptionsParams = {
  containerName: string
  taskDefinitionArn: string
}
const getLogOptions = async ({ containerName, taskDefinitionArn }: GetLogOptionsParams) => {
  const task = await describeEcsTaskDefinition(taskDefinitionArn)
  const containers = task.containerDefinitions ?? []
  const container = containers.find(x => x.name === containerName)
  assert(container !== undefined, `container(${containerName}) not found`)

  const { logConfiguration } = container
  assert(logConfiguration !== undefined, 'logConfiguration not found')

  const { logDriver, options } = logConfiguration
  assert(logDriver === 'awslogs', `logDriver(${logDriver}) not supported`)
  assert(options !== undefined, 'logConfiguration.options not found')

  return options
}

export type RunEcsTaskParams = {
  command: string[]
  containerName: string
  taskDefinition: string
}
export const runEcsTask = async (params: RunEcsTaskParams) => {
  const endOfStreamIdentifier = makeEndOfStreamIdentifier()
  const data = await runTask(params, endOfStreamIdentifier)

  const tasks = data.tasks ?? []
  await Promise.all(tasks.map(async ({ taskArn, taskDefinitionArn }) => {
    assert(taskArn !== undefined, 'taskArn not detected')
    assert(taskDefinitionArn !== undefined, 'taskDefinitionArn not detected')

    const { containerName } = params
    const logOptions = await getLogOptions({
      containerName,
      taskDefinitionArn
    })

    const taskId = taskArn.substring(taskArn.lastIndexOf('/') + 1)
    const logStream = new AwsLogStream({
      logGroup: logOptions['awslogs-group'],
      logStream: `${logOptions['awslogs-stream-prefix']}/${containerName}/${taskId}`,
      endOfStreamIdentifier
    })

    logStream.pipe(process.stdout).on('error', reason => {
      throw reason
    })
  }))
}
