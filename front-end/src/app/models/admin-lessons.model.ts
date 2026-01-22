export interface AdminLessonListItem {
    id: number;
    title: string;
    price: number;
    content: string;
    position: number;
    cursusTitle: string | null;
    themeTitle: string | null;
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

export interface AdminLessonsListResponse {
    items: AdminLessonListItem[];
    meta: PaginatedAdmin;
}

export interface ListLessonsParams {
    page?: number;
    limit?: number;
}

export interface AdminLessonWriteResponse {
  message: string;
  id: number;
  title: string;
  price: number;
  content: string;
  position?: number;
  isActive?: boolean;
  createdAt: string;
  updatedAt: string | null;
  createdBy: string | null;
  updatedBy: string | null;
}

export interface CreateLessonPayload {
    title: string;
    price: number;
    content: string;
    cursusId: number;
}

export interface UpdateLessonPayload {
    title?: string;
    price?: number;
    content?: string;
}

export interface AdminCursusOption {
    id: number;
    title: string;
}