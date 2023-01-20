/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NetworkConfiguration } from 'aws-sdk/clients/eventbridge'
import { describeEcsSecurityGroupIds } from '~aws/scripts/utils/describe-ecs-security-group-ids'
import { describePrivateSubnetIds } from '~aws/scripts/utils/describe-private-subnet-ids'

export const describeEventsNetworkConfiguration = async (): Promise<NetworkConfiguration> => ({
  awsvpcConfiguration: {
    AssignPublicIp: 'DISABLED',
    SecurityGroups: await describeEcsSecurityGroupIds(),
    Subnets: await describePrivateSubnetIds()
  }
})
