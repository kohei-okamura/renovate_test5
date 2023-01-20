/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { aws_rds as rds } from 'aws-cdk-lib'

export const region = process.env.CDK_DEFAULT_REGION ?? process.env.AWS_DEFAULT_REGION ?? 'ap-northeast-1'

// 各種ポート番号
export const PORT_HTTP = 80
export const PORT_HTTPS = 443
export const PORT_MYSQL = 3306
export const PORT_NGINX = 55080
export const PORT_REDIS = 6379

// Redash
export const REDASH_ELASTIC_IP = 'RedashElasticIp'
export const REDASH_INSTANCE = 'ZingerRedash'
export const REDASH_TARGET_GROUP = 'RedashTargetGroup'

// アカウントID
export const ZINGER_ACCOUNT = '647127556763'
export const ZINGER_STAGING_ACCOUNT = '160644003485'
export const ZINGER_SANDBOX_ACCOUNT = '869997810708'

// ALB/ELB
export const ZINGER_ALB = 'ZingerAlb'
export const ZINGER_HTTP_LISTENER = 'ZingerHttpListener'
export const ZINGER_HTTP_LISTENER_RULE = 'ZingerHttpListenerRule'
export const ZINGER_HTTPS_LISTENER = 'ZingerHttpsListener'
export const ZINGER_TARGET_GROUP = 'ZingerTargetGroup'

// Bastion
export const ZINGER_BASTION = 'ZingerBastion'
export const ZINGER_BASTION_ELASTIC_IP_ASSOCIATION = 'ZingerBastionElasticIpAssociation'
export const ZINGER_BASTION_ELASTIC_IP = 'ZingerBastionElasticIp'

// RDS
export const ZINGER_MYSQL = 'ZingerMysql'
export const ZINGER_MYSQL_DATABASE_NAME = 'zinger'
export const ZINGER_MYSQL_INSTANCE_IDENTIFIER = 'zinger-mysql'
export const ZINGER_MYSQL_MASTER_USER_NAME = 'zinger_master'
export const ZINGER_MYSQL_PARAMETER_GROUP = 'ZingerMysqlParameterGroup'
export const ZINGER_MYSQL_VERSION = rds.MysqlEngineVersion.VER_8_0_28

// ElastiCache (Redis)
export const ZINGER_REDIS_VERSION = '6.x'

// Route 53
export const ZINGER_A_RECORD = 'ZingerARecord'
export const ZINGER_AAAA_RECORD = 'ZingerAaaaRecord'
export const ZINGER_CERTIFICATE = 'ZingerCertificate'
export const ZINGER_HOSTED_ZONE = 'ZingerHostedZone'

// ECR
export const ZINGER_APP_CLI_PROD = 'ZingerAppCliProd'
export const ZINGER_APP_CLI_PROD_REPOSITORY_NAME = 'zinger/app-cli-prod'
export const ZINGER_APP_CLI_STAGING = 'ZingerAppCliStaging'
export const ZINGER_APP_CLI_STAGING_REPOSITORY_NAME = 'zinger/app-cli-staging'
export const ZINGER_APP_CLI_SANDBOX = 'ZingerAppCliSandbox'
export const ZINGER_APP_CLI_SANDBOX_REPOSITORY_NAME = 'zinger/app-cli-sandbox'
export const ZINGER_APP_SERVER_PROD = 'ZingerAppServerProd'
export const ZINGER_APP_SERVER_PROD_REPOSITORY_NAME = 'zinger/app-server-prod'
export const ZINGER_APP_SERVER_STAGING = 'ZingerAppServerStaging'
export const ZINGER_APP_SERVER_STAGING_REPOSITORY_NAME = 'zinger/app-server-staging'
export const ZINGER_APP_SERVER_SANDBOX = 'ZingerAppServerSandbox'
export const ZINGER_APP_SERVER_SANDBOX_REPOSITORY_NAME = 'zinger/app-server-sandbox'
export const ZINGER_WEB_PROD = 'ZingerWebProd'
export const ZINGER_WEB_PROD_REPOSITORY_NAME = 'zinger/web-prod'
export const ZINGER_WEB_STAGING = 'ZingerWebStaging'
export const ZINGER_WEB_STAGING_REPOSITORY_NAME = 'zinger/web-staging'
export const ZINGER_WEB_SANDBOX = 'ZingerWebSandbox'
export const ZINGER_WEB_SANDBOX_REPOSITORY_NAME = 'zinger/web-sandbox'

// ECS
export const ZINGER_APP_BATCH_TASK_DEFINITION = 'ZingerAppBatchTaskDefinition'
export const ZINGER_APP_CREATE_USER_BILLING_TASK_DEFINITION = 'ZingerAppCreateUserBillingTaskDefinition'
export const ZINGER_APP_MIGRATION_TASK_DEFINITION = 'ZingerAppMigrationTaskDefinition'
export const ZINGER_APP_QUEUE_TASK_DEFINITION = 'ZingerAppQueueTaskDefinition'
export const ZINGER_ECS_CLUSTER = 'ZingerEcsCluster'
export const ZINGER_ECS_QUEUE = 'ZingerEcsQueue'
export const ZINGER_ECS_SERVICE = 'ZingerEcsService'
export const ZINGER_TASK_DEFINITION = 'ZingerTaskDefinition'
export const ZINGER_TASK_DESIRED_COUNT_PROD = 2
export const ZINGER_TASK_DESIRED_COUNT_STAGING = 2
export const ZINGER_TASK_DESIRED_COUNT_SANDBOX = 1

// EventBridge
export const ZINGER_CREATE_USER_BILLING_EVENT_TARGET = 'ZingerCreateUserBillingEventTarget'
export const ZINGER_CREATE_USER_BILLING_EVENT_RULE = 'ZingerCreateUserBillingEventRule'

// IAM
export const ZINGER_IAM_BASTION_ROLE_ID = 'ZingerIamBastionRole'
export const ZINGER_IAM_BASTION_ROLE_NAME = 'ZingerBastionRole'
export const ZINGER_IAM_ECS_EVENTS_ROLE_ID = 'ZingerIamEcsEventsRole'
export const ZINGER_IAM_ECS_EVENTS_ROLE_NAME = 'ecsEventsRole'
export const ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_ID = 'ZingerIamEcsTaskExecutionRole'
export const ZINGER_IAM_ECS_TASK_EXECUTION_ROLE_NAME = 'ZingerEcsTaskExecutionRole'
export const ZINGER_IAM_ECS_TASK_ROLE_ID = 'ZingerIamEcsTaskRole'
export const ZINGER_IAM_ECS_TASK_ROLE_NAME = 'ZingerEcsTaskRole'
export const ZINGER_IAM_MACKEREL_ROLE_ID = 'ZingerMackerelRole'
export const ZINGER_IAM_MACKEREL_ROLE_NAME = 'ZingerMackerelRole'
export const ZINGER_S3_GROUP_NAME = 'ZingerS3Group'
export const ZINGER_S3_USER_NAME = 'ZingerS3User'
export const ZINGER_GITHUB_ACTIONS_OIDC_PROVIDER_ID = 'ZingerGitHubActionsOidcProvider'
export const ZINGER_GITHUB_ACTIONS_ROLE_ID = 'ZingerGitHubActionsRole'
export const ZINGER_GITHUB_ACTIONS_ROLE_NAME = 'ZingerGitHubActionsRole'

// S3
export const ZINGER_APP_STORAGE = 'zinger-app-storage'
export const ZINGER_LOG_STORAGE = 'zinger-log-storage'
export const ZINGER_OPS_STORAGE = 'zinger-ops-storage'
export const ZINGER_PUBLIC_STORAGE = 'zinger-public-storage'

// SecurityGroup
export const ZINGER_SECURITY_GROUP_ALB = 'ZingerSecurityGroupAlb'
export const ZINGER_SECURITY_GROUP_BASTION = 'ZingerSecurityGroupBastion'
export const ZINGER_SECURITY_GROUP_ECS = 'ZingerSecurityGroupEcs'
export const ZINGER_SECURITY_GROUP_MYSQL = 'ZingerSecurityGroupMysql'
export const ZINGER_SECURITY_GROUP_REDIS = 'ZingerSecurityGroupRedis'

// SQS
export const ZINGER_QUEUE = 'ZingerQueue'
export const ZINGER_QUEUE_NAME = `${ZINGER_QUEUE}.fifo`

// SSM
export const ZINGER_SSM_MYSQL_HOST = 'ZingerSsmMysqlHost'
export const ZINGER_SSM_REDIS_HOST = 'ZingerSsmRedisHost'

// VPC
export const ZINGER_PRIVATE_SUBNET_NAME = 'Private'
export const ZINGER_PUBLIC_SUBNET_NAME = 'Public'
