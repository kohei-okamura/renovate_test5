/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Duration,
  SecretValue,
  Stack,
  aws_rds as rds,
  aws_ssm as ssm
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerDbStackProps } from '~aws/lib/props'
import { dbHost } from '~aws/scripts/ssm-parameters/names'
import {
  ZINGER_PRIVATE_SUBNET_NAME,
  ZINGER_MYSQL,
  ZINGER_MYSQL_DATABASE_NAME,
  ZINGER_MYSQL_INSTANCE_IDENTIFIER,
  ZINGER_MYSQL_MASTER_USER_NAME,
  ZINGER_MYSQL_PARAMETER_GROUP,
  ZINGER_MYSQL_VERSION,
  ZINGER_SSM_MYSQL_HOST
} from '~aws/variables'

export class ZingerDbStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerDbStackProps) {
    super(scope, id, props)
    const { vpc, securityGroup } = props
    const engine = rds.DatabaseInstanceEngine.mysql({ version: ZINGER_MYSQL_VERSION })

    //
    // RDS (MySQL)
    //
    const { dbInstanceEndpointAddress } = new rds.DatabaseInstance(this, ZINGER_MYSQL, {
      autoMinorVersionUpgrade: false,
      backupRetention: Duration.days(7),
      cloudwatchLogsExports: ['error', 'slowquery'],
      credentials: rds.Credentials.fromPassword(
        ZINGER_MYSQL_MASTER_USER_NAME,
        SecretValue.ssmSecure(
          props.masterUserPassword.parameterName,
          `${props.masterUserPassword.version}`
        )
      ),
      databaseName: ZINGER_MYSQL_DATABASE_NAME,
      deletionProtection: true,
      engine,
      instanceIdentifier: ZINGER_MYSQL_INSTANCE_IDENTIFIER,
      instanceType: props.instanceType,
      multiAz: props.multiAz,
      parameterGroup: new rds.ParameterGroup(this, ZINGER_MYSQL_PARAMETER_GROUP, {
        engine,
        parameters: {
          log_bin_trust_function_creators: '1',
          long_query_time: '1'
        }
      }),
      preferredBackupWindow: '20:05-20:35',
      securityGroups: [securityGroup],
      vpc,
      vpcSubnets: { subnetGroupName: ZINGER_PRIVATE_SUBNET_NAME }
    })

    //
    // SSM にエンドポイントを登録
    //
    new ssm.StringParameter(this, ZINGER_SSM_MYSQL_HOST, {
      parameterName: dbHost,
      stringValue: dbInstanceEndpointAddress
    })
  }
}
