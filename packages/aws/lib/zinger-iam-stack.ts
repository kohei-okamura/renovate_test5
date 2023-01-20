/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { aws_iam as iam, aws_ssm as ssm, Stack, StackProps } from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { mackerelExternalId } from '~aws/scripts/ssm-parameters/names'
import {
  ZINGER_IAM_BASTION_ROLE_ID,
  ZINGER_IAM_BASTION_ROLE_NAME,
  ZINGER_IAM_ECS_EVENTS_ROLE_ID,
  ZINGER_IAM_ECS_EVENTS_ROLE_NAME,
  ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_ID,
  ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_NAME,
  ZINGER_IAM_ECS_TASK_ROLE_ID,
  ZINGER_IAM_ECS_TASK_ROLE_NAME,
  ZINGER_IAM_MACKEREL_ROLE_ID,
  ZINGER_IAM_MACKEREL_ROLE_NAME
} from '~aws/variables'

export class ZingerIamStack extends Stack {
  public bastionRole: iam.IRole

  constructor (scope: Construct, id: string, props: StackProps) {
    super(scope, id, props)

    const role = (id: string, roleProps: iam.RoleProps): iam.IRole => {
      return new iam.Role(this, id, {
        ...roleProps
      })
    }

    //
    // EC2 Bastion用 ROLE
    //
    this.bastionRole = role(ZINGER_IAM_BASTION_ROLE_ID, {
      assumedBy: new iam.ServicePrincipal('ec2.amazonaws.com'),
      managedPolicies: [
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonS3FullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSQSFullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMManagedInstanceCore')
      ],
      roleName: ZINGER_IAM_BASTION_ROLE_NAME
    })

    //
    // ECS バッチ処理用 ROLE
    //
    role(ZINGER_IAM_ECS_EVENTS_ROLE_ID, {
      assumedBy: new iam.ServicePrincipal('events.amazonaws.com'),
      managedPolicies: [
        iam.ManagedPolicy.fromAwsManagedPolicyName('service-role/AmazonEC2ContainerServiceEventsRole')
      ],
      roleName: ZINGER_IAM_ECS_EVENTS_ROLE_NAME
    })

    //
    // ECS タスク定義 実行用 ROLE（ECSコンテナエージェントが使用するロール）
    //
    role(ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_ID, {
      assumedBy: new iam.ServicePrincipal('ecs-tasks.amazonaws.com'),
      managedPolicies: [
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonECS_FullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMFullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('CloudWatchFullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('service-role/AmazonECSTaskExecutionRolePolicy')
      ],
      roleName: ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_NAME
    })

    //
    // ECS タスク定義用 ROLE（アプリケーションが使用するROLE）
    //
    role(ZINGER_IAM_ECS_TASK_ROLE_ID, {
      assumedBy: new iam.ServicePrincipal('ecs-tasks.amazonaws.com'),
      managedPolicies: [
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonS3FullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSQSFullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMDirectoryServiceAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('AmazonSSMManagedInstanceCore'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('CloudWatchFullAccess'),
        iam.ManagedPolicy.fromAwsManagedPolicyName('service-role/AmazonEC2ContainerServiceEventsRole')
      ],
      roleName: ZINGER_IAM_ECS_TASK_ROLE_NAME
    })

    //
    // Mackerel AWSインテグレーション ROLE
    //
    const mackerelPolicyJson = {
      Version: '2012-10-17',
      Statement: [
        {
          Action: [
            'cloudwatch:Get*',
            'cloudwatch:List*',
            'ec2:DescribeInstances',
            'ecs:DescribeClusters',
            'ecs:List*',
            'elasticache:Describe*',
            'elasticache:ListTagsForResource',
            'elasticloadbalancing:Describe*',
            'iam:GetUser',
            'rds:Describe*',
            'rds:ListTagsForResource'
          ],
          Effect: 'Allow',
          Resource: '*'
        }
      ]
    }
    const mackerelPolicy = new iam.ManagedPolicy(this, 'ZingerMackerelPolicy', {
      document: iam.PolicyDocument.fromJson(mackerelPolicyJson),
      managedPolicyName: 'ZingerMackerelPolicy'
    })
    const { stringValue: externalId } = ssm.StringParameter.fromStringParameterAttributes(this, 'ZingerExternalId', {
      parameterName: mackerelExternalId
    })
    role(ZINGER_IAM_MACKEREL_ROLE_ID, {
      assumedBy: new iam.AccountPrincipal('217452466226'), // Mackerel AWSインテグレーション のアカウントID
      externalIds: [externalId],
      managedPolicies: [mackerelPolicy],
      roleName: ZINGER_IAM_MACKEREL_ROLE_NAME
    })
  }
}
