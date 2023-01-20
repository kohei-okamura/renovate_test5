/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { environments } from '~aws/scripts/ecs-task-definitions/environment'
import { getImageName, taskDefinition, TaskParams } from '~aws/scripts/ecs-task-definitions/functions'
import { mackerelEnvironments, mackerelSecrets } from '~aws/scripts/ecs-task-definitions/mackerel'
import { secrets } from '~aws/scripts/ecs-task-definitions/secrets'
import { region, ZINGER_APP_QUEUE_TASK_DEFINITION } from '~aws/variables'

/**
 * タスク定義：queueの常駐タスク用.
 */
export const queue = (params: TaskParams) => taskDefinition({
  family: ZINGER_APP_QUEUE_TASK_DEFINITION,
  containerDefinitions: [
    {
      name: 'app-queue',
      image: getImageName('zinger/app-cli', params),
      command: ['php', 'artisan', 'queue:work'],
      essential: true,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'app-queue-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'app-queue'
        }
      },
      environment: environments[params.environment],
      secrets
    },
    {
      name: 'mackerel-queue-container-agent',
      image: 'mackerel/mackerel-container-agent:latest',
      essential: false,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'mackerel-queue-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'mackerel-queue'
        }
      },
      memory: 128,
      environment: mackerelEnvironments[params.environment],
      secrets: mackerelSecrets
    }
  ],
  cpu: '1024',
  memory: '2048'
})
