import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { CursusLessonPreview } from '../models/cursus.model';

@Injectable({ providedIn: 'root' })
export class CursusService {
    private api = inject(ApiService);
    
    // Public catalog endpoint to get a cursus with a preview list of lessons
    getCursusWithLessonsPreview(cursusId: number): Observable<CursusLessonPreview> {
        return this.api.get<CursusLessonPreview>(`/api/cursus/${cursusId}`);
    }
}