/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  SecretValue,
  Stack,
  aws_elasticache as elasticache,
  aws_ssm as ssm
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerRedisStackProps } from '~aws/lib/props'
import { redisHost } from '~aws/scripts/ssm-parameters/names'
import { ZINGER_REDIS_VERSION, ZINGER_SSM_REDIS_HOST } from '~aws/variables'

export class ZingerRedisStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerRedisStackProps) {
    super(scope, id, props)
    const { authToken, cacheNodeType, securityGroup, subnetGroupName, vpc } = props

    //
    // サブネットグループ
    //
    const cfnSubnetGroup = new elasticache.CfnSubnetGroup(this, 'ZingerRedisSubnetGroup', {
      // cacheSubnetGroupName は AWS 側で常に kebab-case にされるため PascalCase ではなく kebab-case を用いる
      cacheSubnetGroupName: 'zinger-redis-subnet-group',
      description: 'subnet is private',
      subnetIds: vpc.selectSubnets({ subnetGroupName }).subnetIds
    })

    //
    // ElastiCache (Redis)
    //
    const redis = new elasticache.CfnReplicationGroup(this, 'ZingerReplicationGroup', {
      authToken: SecretValue.ssmSecure(authToken.parameterName, `${authToken.version}`).toString(),
      automaticFailoverEnabled: props.automaticFailoverEnabled,
      cacheNodeType,
      cacheSubnetGroupName: cfnSubnetGroup.cacheSubnetGroupName,
      engine: 'redis',
      engineVersion: ZINGER_REDIS_VERSION,
      multiAzEnabled: props.multiAzEnabled,
      numCacheClusters: props.numCacheClusters,
      replicationGroupDescription: 'Zinger redis replication',
      securityGroupIds: [securityGroup.securityGroupId],
      transitEncryptionEnabled: true
    })
    redis.addDependsOn(cfnSubnetGroup)

    //
    // SSM にエンドポイントを登録
    //
    new ssm.StringParameter(this, ZINGER_SSM_REDIS_HOST, {
      parameterName: redisHost,
      stringValue: redis.attrPrimaryEndPointAddress
    })
  }
}
