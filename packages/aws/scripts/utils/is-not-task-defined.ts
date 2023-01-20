/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_ECS_CLUSTER, ZINGER_ECS_QUEUE, ZINGER_ECS_SERVICE } from '~aws/variables'

export type ZingerEcsServiceType = typeof ZINGER_ECS_QUEUE | typeof ZINGER_ECS_SERVICE

export const isNotTaskDefined = async (service: ZingerEcsServiceType): Promise<boolean> => {
  const ecs = createEcsService()

  const data = await runAwsCommand(() => ecs.describeServices({
    cluster: ZINGER_ECS_CLUSTER,
    services: [service]
  }))

  const services = data.services ?? []
  return services.every(({ pendingCount, runningCount, desiredCount }) => {
    return pendingCount === 0 && runningCount === 0 && desiredCount === 0
  })
}
