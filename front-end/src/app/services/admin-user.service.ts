// src/app/admin/users/services/admin-user.service.ts

import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService, ParamsRecord } from '../services/api.service';
import {
  AdminUsersListResponse,
  ListUsersParams,
  CreateUserPayload,
  UpdateUserPayload,
  AdminUserListItem
} from '../models/admin-users.model';

@Injectable({ providedIn: 'root' })
export class AdminUserService {
    private api = inject(ApiService);

    /**
     * GET /api/admin/users
     */
    listUsers(params: ListUsersParams): Observable<AdminUsersListResponse> {
        return this.api.get<AdminUsersListResponse>('/api/admin/users', {
            params: this.toParamsRecord(params)
        });
    }

    /**
     * GET /api/admin/users/{id}
     */
    getUser(id: number): Observable<AdminUserListItem> {
        return this.api.get<AdminUserListItem>(`/api/admin/users/${id}`);
    }

    /**
     * POST /api/admin/users
     */
    createUser(payload: CreateUserPayload): Observable<AdminUserListItem> {
        return this.api.post<AdminUserListItem>('/api/admin/users', payload);
    }

    /**
     * PATCH /api/admin/users/{id}
     */
    updateUser(id: number, payload: UpdateUserPayload): Observable<AdminUserListItem> {
        return this.api.patch<AdminUserListItem>(`/api/admin/users/${id}`, payload);
    }

    /**
     * DELETE /api/admin/users/{id}
     */
    desactivateUser(id: number): Observable<{ message: string }> {
        return this.api.delete<{ message: string }>(`/api/admin/users/${id}`);
    }

    /**
     * Convert ListUsersParams to ParamsRecord
     */
    private toParamsRecord(params: ListUsersParams): ParamsRecord {
        const record: ParamsRecord = {};
        
        // Add only defined parameters
        if (params.page !== undefined) record['page'] = params.page;
        if (params.limit !== undefined) record['limit'] = params.limit;


        return record;
    }
}

