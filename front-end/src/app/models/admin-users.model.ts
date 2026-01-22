export type AdminRole = 'ROLE_USER' | 'ROLE_ADMIN';

export interface AdminUserListItem {
    id: number;
    email: string;
    roles: AdminRole[];
    isVerified: boolean;
    createdAt: string;
    updatedAt: string | null;
    createdBy: string | null;
    updatedBy: string | null;
}

export interface PaginatedAdmin {
    page: number;
    limit: number;
    totalItems: number;
    totalPages: number;
}

export interface AdminUsersListResponse {
    items: AdminUserListItem[];
    meta: PaginatedAdmin;
}

export interface ListUsersParams {
    page?: number;
    limit?: number;
}

export interface CreateUserPayload {
    email: string;
    password: string;
    roles: AdminRole[];
    isActive?: boolean;
    isVerified?: boolean;
}

export interface UpdateUserPayload {
    email?: string;
    password?: string;
    roles?: AdminRole[];
    isVerified?: boolean;
}