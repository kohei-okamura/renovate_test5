/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare namespace BacklogApp {
  // See https://github.com/nulab/backlog-js/blob/master/dist/backlog.d.ts
  type PostIssueParams = {
    projectId: number
    summary: string
    priorityId: number
    issueTypeId: number
    parentIssueId?: number
    description?: string
    startDate?: string
    dueDate?: string
    estimatedHours?: number
    actualHours?: number
    categoryId?: number[]
    versionId?: number[]
    milestoneId?: number[]
    assigneeId?: number
    notifiedUserId?: number[]
    attachmentId?: number[]
  }
  type PatchIssueParams = {
    summary?: string
    parentIssueId?: number
    description?: string
    statusId?: number
    resolutionId?: number
    startDate?: string
    dueDate?: string
    estimatedHours?: number
    actualHours?: number
    issueTypeId?: number
    categoryId?: number[]
    versionId?: number[]
    milestoneId?: number[]
    priorityId?: number
    assigneeId?: number
    notifiedUserId?: number[]
    attachmentId?: number[]
    comment?: string
    [customField_: string]: any
  }
  type Issue = {
    id: number
    issueKey: string
    summary: string
    parentIssueId: number
    description: string
    statusId: number
    resolutionId: number
    startDate: string
    dueDate: string
    estimatedHours: number
    actualHours: number
    issueTypeId: number
    categoryId: number[]
    versionId: number[]
    milestoneId: number[]
    priorityId: number
    assigneeId: number
    notifiedUserId: number[]
    attachmentId: number[]
    comment: string
  }

  type Query = Record<string, number | string | Array<number | string>>

  type JsonValue = string | number | boolean | null | { [key: string]: JsonValue } | JsonValue[]

  type Item = Record<string, JsonValue>

  type User = {
    id: number
    name: string
    [key: string]: JsonValue
  }

  type Status = {
    id: number
    name: string
    [key: string]: JsonValue
  }

  type CreateClientParams = {
    apiKey: string
    projectKey: string
    url: string
  }

  type Client = {
    createIssue (params: PostIssueParams): Item
    getIssue (issueKey: string): Issue
    getIssues (query?: Query): Issue[]
    getIssueTypes (projectKey?: string): Item[]
    getMilestones (projectKey?: string): Item[]
    getNotifications (query?: Query): Item[]
    getPriorities (projectKey?: string): Item[]
    getProject (projectKey?: string): Item
    getProjectKey (): string
    getStatuses (projectKey?: string): Status[]
    getUsers (projectKey?: string): User[]
    markAsRead (id: number): void
    url (path?: string, query?: Query): string
    updateIssue (issueKey: string, params: PatchIssueParams): Item
  }

  // noinspection JSUnusedGlobalSymbols
  const createClient: (params: CreateClientParams) => Client
}
