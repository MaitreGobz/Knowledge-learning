import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { Theme } from '../models/theme.model';

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private api = inject(ApiService);

  getAll(): Observable<Theme[]> {
    return this.api.get<Theme[]>('/api/themes');
  }
}
