/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

const SUBNET_TYPE_PRIVATE = 'Private'
const TAG_KEY_SUBNET_NAME = 'Name'
const TAG_KEY_SUBNET_TYPE = 'aws-cdk:subnet-type'

export const describePrivateSubnetIds = async (): Promise<string[]> => {
  const ec2 = createEc2Service()

  const data = await runAwsCommand(() => ec2.describeSubnets())

  const xs = (data.Subnets ?? [])
  const subnets = xs
    .map(subnet => {
      const tags = subnet.Tags ?? []
      return {
        subnetId: subnet.SubnetId ?? '',
        subnetName: tags.find(tag => tag.Key === TAG_KEY_SUBNET_NAME)?.Value ?? '',
        subnetType: tags.find(tag => tag.Key === TAG_KEY_SUBNET_TYPE)?.Value ?? ''
      }
    })
    .filter(x => x.subnetType === SUBNET_TYPE_PRIVATE)
    .sort((a, b) => a.subnetName.localeCompare(b.subnetName))
    .map(x => x.subnetId)
  assert(subnets.length > 0, 'Failed to describe subnets')
  return subnets
}
