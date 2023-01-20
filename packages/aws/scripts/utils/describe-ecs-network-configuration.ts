/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NetworkConfiguration } from 'aws-sdk/clients/ecs'
import { describeEcsSecurityGroupIds } from '~aws/scripts/utils/describe-ecs-security-group-ids'
import { describePrivateSubnetIds } from '~aws/scripts/utils/describe-private-subnet-ids'

export const describeEcsNetworkConfiguration = async (): Promise<NetworkConfiguration> => ({
  awsvpcConfiguration: {
    assignPublicIp: 'DISABLED',
    securityGroups: await describeEcsSecurityGroupIds(),
    subnets: await describePrivateSubnetIds()
  }
})
