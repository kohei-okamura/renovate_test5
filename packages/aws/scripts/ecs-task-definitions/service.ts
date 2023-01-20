/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { environments } from '~aws/scripts/ecs-task-definitions/environment'
import { getImageName, taskDefinition, TaskParams } from '~aws/scripts/ecs-task-definitions/functions'
import { mackerelEnvironments, mackerelSecrets } from '~aws/scripts/ecs-task-definitions/mackerel'
import { secrets } from '~aws/scripts/ecs-task-definitions/secrets'
import { PORT_NGINX, region, ZINGER_TASK_DEFINITION } from '~aws/variables'

/**
 * タスク定義：常駐タスク用.
 */
export const service = (params: TaskParams) => taskDefinition({
  family: ZINGER_TASK_DEFINITION,
  containerDefinitions: [
    {
      name: 'web',
      image: getImageName('zinger/web', params),
      portMappings: [{
        protocol: 'tcp',
        containerPort: PORT_NGINX
      }],
      dependsOn: [
        { containerName: 'app-server', condition: 'START' }
      ],
      essential: true,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'web-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'web'
        }
      },
      volumesFrom: [{
        readOnly: true,
        sourceContainer: 'app-server'
      }]
    },
    {
      name: 'app-server',
      image: getImageName('zinger/app-server', params),
      essential: true,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'app-server-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'app-server'
        }
      },
      environment: environments[params.environment],
      secrets
    },
    {
      name: 'mackerel-service-container-agent',
      image: 'mackerel/mackerel-container-agent:latest',
      essential: false,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'mackerel-service-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'mackerel-service'
        }
      },
      memory: 128,
      environment: mackerelEnvironments[params.environment],
      secrets: mackerelSecrets
    }
  ],
  cpu: '512',
  memory: '1024'
})
