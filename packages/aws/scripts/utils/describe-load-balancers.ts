/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LoadBalancer } from 'aws-sdk/clients/ecs'
import { describeElbTargetGroupArn } from '~aws/scripts/utils/describe-elb-target-group-arn'
import { PORT_NGINX } from '~aws/variables'

export const describeLoadBalancers = async (): Promise<LoadBalancer[]> => [{
  containerName: 'web',
  containerPort: PORT_NGINX,
  targetGroupArn: await describeElbTargetGroupArn()
}]
