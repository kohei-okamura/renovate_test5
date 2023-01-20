/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  aws_ec2 as ec2
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerVpcStackProps } from '~aws/lib/props'
import { ZINGER_PRIVATE_SUBNET_NAME, ZINGER_PUBLIC_SUBNET_NAME } from '~aws/variables'

export class ZingerVpcStack extends Stack {
  public vpc: ec2.Vpc

  constructor (scope: Construct, id: string, props: ZingerVpcStackProps) {
    super(scope, id, props)

    //
    // VPC 定義
    //
    this.vpc = new ec2.Vpc(this, 'ZingerVpc', {
      cidr: props.cidr,
      natGateways: props.natGateways,
      maxAzs: 2,
      subnetConfiguration: [
        {
          cidrMask: 24,
          subnetType: ec2.SubnetType.PUBLIC,
          name: ZINGER_PUBLIC_SUBNET_NAME
        },
        {
          cidrMask: 24,
          subnetType: ec2.SubnetType.PRIVATE_WITH_NAT,
          name: ZINGER_PRIVATE_SUBNET_NAME
        }
      ]
    })
    this.vpc.addFlowLog('vpcFlowLog', {
      destination: ec2.FlowLogDestination.toS3(props.logStorage)
    })
    const endpointSubnet: ec2.SubnetSelection = {
      onePerAz: props.onePerAz,
      subnetType: ec2.SubnetType.PRIVATE_WITH_NAT
    }

    //
    // VPC エンドポイント（Gateway型）
    //
    this.vpc.addGatewayEndpoint('ZingerGatewayEndpointS3', {
      service: ec2.GatewayVpcEndpointAwsService.S3,
      subnets: [endpointSubnet]
    })

    //
    // VPC エンドポイント（Interface型）
    //
    const endpoints = [
      ['ZingerInterfaceEndpointCloudwatchLogs', ec2.InterfaceVpcEndpointAwsService.CLOUDWATCH_LOGS],
      ['ZingerInterfaceEndpointEcr', ec2.InterfaceVpcEndpointAwsService.ECR],
      ['ZingerInterfaceEndpointEcrDocker', ec2.InterfaceVpcEndpointAwsService.ECR_DOCKER],
      ['ZingerInterfaceEndpointSsm', ec2.InterfaceVpcEndpointAwsService.SSM]
    ] as const
    const subnets = endpointSubnet
    endpoints.forEach(([id, service]) => {
      this.vpc.addInterfaceEndpoint(id, { service, subnets })
    })
  }
}
