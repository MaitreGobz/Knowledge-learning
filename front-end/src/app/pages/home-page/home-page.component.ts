import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ThemeService } from '../../services/theme.service';
import { Theme } from '../../models/theme.model';

@Component({
  selector: 'app-home-page',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home-page.component.html',
  styleUrl: './home-page.component.scss'
})
export class HomePageComponent implements OnInit{
  themes: Theme[] = [];
  isLoading = true;
  errorMessage: string | null = null;

  private themeService = inject(ThemeService);

  ngOnInit(): void {
    this.themeService.getAll().subscribe({
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

  getThemeImage(theme: Theme): string {
    // Slug mapped to image filename
    return `assets/themes/${theme.slug}.jpg`;
  }

  onThemeImageError(event: Event): void {
    (event.target as HTMLImageElement).src = 'assets/themes/default.jpg';
  }
}