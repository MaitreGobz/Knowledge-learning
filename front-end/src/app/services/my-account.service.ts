import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { MyLessonDto } from '../models/my-lesson.dto';
import { CertificationDto } from '../models/certification.dto';

@Injectable({ providedIn: 'root' })
// Service to fetch user's lessons and certifications
export class MyAccountService {
  private api = inject(ApiService);

  getMyLessons(): Observable<MyLessonDto[]> {
    return this.api.get<MyLessonDto[]>('/api/private/my-lessons');
  }

  getMyCertifications(): Observable<CertificationDto[]> {
    return this.api.get<CertificationDto[]>('/api/certifications');
  }
}
