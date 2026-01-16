import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ThemeService } from '../../services/theme.service';
import { ThemeCursusPreview } from '../../models/theme.model';

@Component({
  selector: 'app-themes-page',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './themes-page.component.html',
  styleUrl: './themes-page.component.scss'
})
export class ThemesPageComponent implements OnInit{
  themes: ThemeCursusPreview[] = [];
  isLoading = true;
  errorMessage: string | null = null;

  private themeService = inject(ThemeService);

  ngOnInit(): void {
    this.themeService.getThemesWithCursusPreview().subscribe({
      next: (themes) => {
        this.themes = themes;
        this.isLoading = false;
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les thèmes.';
        this.isLoading = false;
      }
    });
  }

  onBuyCursus(cursusId: number): void {
    // Logic to handle cursus purchase, now just a console log
    console.log(`Achat du cursus avec l'ID`, cursusId);
  }
}
