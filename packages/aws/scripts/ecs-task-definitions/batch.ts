/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { environments } from '~aws/scripts/ecs-task-definitions/environment'
import { getImageName, taskDefinition, TaskParams } from '~aws/scripts/ecs-task-definitions/functions'
import { secrets } from '~aws/scripts/ecs-task-definitions/secrets'
import { region } from '~aws/variables'

type Options = {
  command: string[]
  cpu?: string
  memory?: string
}

/**
 * タスク定義：バッチ処理用（artisan コマンドの定期実行および手動実行用）.
 */
export const batch = (family: string, options: Options) => (params: TaskParams) => taskDefinition({
  family,
  containerDefinitions: [
    {
      name: 'app-batch',
      image: getImageName('zinger/app-cli', params),
      command: options.command,
      essential: true,
      logConfiguration: {
        logDriver: 'awslogs',
        options: {
          'awslogs-create-group': 'true',
          'awslogs-group': 'app-batch-watch',
          'awslogs-region': region,
          'awslogs-stream-prefix': 'app-batch'
        }
      },
      environment: environments[params.environment],
      secrets
    }
  ],
  cpu: options.cpu ?? '512',
  memory: options.memory ?? '2048'
})
