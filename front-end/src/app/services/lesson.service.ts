import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { LessonDetailsDto } from '../models/lesson-details.dto';
import { LessonValidationStateDto } from '../models/lesson-validation-state.dto';
import { LessonValidateResponseDto } from '../models/lesson-validate-response.dto';

@Injectable({ providedIn: 'root' })
export class LessonService {
    private api = inject(ApiService);

    getLessonDetails(id: number): Observable<LessonDetailsDto> {
        return this.api.get<LessonDetailsDto>(`/api/lessons/${id}`);
    }

    getValidationState(id: number): Observable<LessonValidationStateDto> {
        return this.api.get<LessonValidationStateDto>(`/api/private/lessons/${id}/validated`);
    }

    validateLesson(id: number): Observable<LessonValidateResponseDto> {
        return this.api.post<LessonValidateResponseDto>(`/api/private/lessons/${id}/validate`, {});
    }
}
