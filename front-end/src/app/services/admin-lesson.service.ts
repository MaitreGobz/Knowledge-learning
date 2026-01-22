import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService, ParamsRecord } from '../services/api.service';
import {
    AdminLessonsListResponse,
    ListLessonsParams,
    CreateLessonPayload,
    UpdateLessonPayload,
    AdminCursusOption,
    AdminLessonWriteResponse,
    AdminLessonListItem
} from '../models/admin-lessons.model';

@Injectable({ providedIn: 'root' })
export class AdminLessonService {
  private api = inject(ApiService);

  /**
   * GET /api/admin/lessons
   */
  listLessons(params: ListLessonsParams): Observable<AdminLessonsListResponse> {
    return this.api.get<AdminLessonsListResponse>('/api/admin/lessons', {
      params: this.toParamsRecord(params),
    });
  }

  /**
   * GET /api/admin/lessons/{id}
   */
  getLesson(id: number): Observable<AdminLessonListItem> {
    return this.api.get<AdminLessonListItem>(`/api/admin/lessons/${id}`);
  }

  /**
   * POST /api/admin/lessons
   */
  createLesson(payload: CreateLessonPayload): Observable<AdminLessonWriteResponse> {
    return this.api.post<AdminLessonWriteResponse>('/api/admin/lessons', payload);
  }

  /**
   * PATCH /api/admin/lessons/{id}
   */
  updateLesson(id: number, payload: UpdateLessonPayload): Observable<AdminLessonWriteResponse> {
    return this.api.patch<AdminLessonWriteResponse>(`/api/admin/lessons/${id}`, payload);
  }

  /**
   * DELETE /api/admin/lessons/{id}
   */
  desactivateLesson(id: number): Observable<{ message: string }> {
    return this.api.delete<{ message: string }>(`/api/admin/lessons/${id}`);
  }

  /**
   * GET /api/admin/cursus
   * Used to populate the <select> cursus when creating a lesson
   */
  listCursusOptions(): Observable<AdminCursusOption[]> {
    return this.api.get<AdminCursusOption[]>('/api/admin/cursus');
  }

  /**
   * Convert ListLessonsParams to ParamsRecord
   */
  private toParamsRecord(params: ListLessonsParams): ParamsRecord {
    const record: ParamsRecord = {};

    if (params.page !== undefined) record['page'] = params.page;
    if (params.limit !== undefined) record['limit'] = params.limit;

    return record;
  }
}
