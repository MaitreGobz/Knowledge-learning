import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { ThemeCursusPreview } from '../models/theme.model';

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private api = inject(ApiService);

  // Public catalog endpoint to get all themes, return themes with a preview list of cursus
  getThemesWithCursusPreview(): Observable<ThemeCursusPreview[]> {
    return this.api.get<ThemeCursusPreview[]>('/api/themes');
  }

  // General endpoint to get all themes
  getAll(): Observable<ThemeCursusPreview[]> {
    return this.getThemesWithCursusPreview();
  }
}