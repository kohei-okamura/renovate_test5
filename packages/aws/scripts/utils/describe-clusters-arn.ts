/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_ECS_CLUSTER } from '~aws/variables'

export const describeClustersArn = async (): Promise<string> => {
  const ecs = createEcsService()

  const data = await runAwsCommand(() => ecs.describeClusters({
    clusters: [ZINGER_ECS_CLUSTER]
  }))

  const clusters = data.clusters ?? []
  const arn = clusters.find(x => x.clusterName === ZINGER_ECS_CLUSTER)?.clusterArn
  assert(typeof arn === 'string', 'Failed to describe cluster arn')
  return arn
}
