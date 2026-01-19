import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { CursusService } from '../../services/cursus.service';
import { CursusLessonPreview } from '../../models/cursus.model';

@Component({
  selector: 'app-cursus-page',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './cursus-page.component.html',
  styleUrl: './cursus-page.component.scss'
})
export class CursusPageComponent implements OnInit {
  cursus: CursusLessonPreview | null = null;
  isLoading = true
  errorMessage: string | null = null;

  private cursusService = inject(CursusService);
  private route = inject(ActivatedRoute);

  // On component initialization, fetch cursus with lessons preview
  ngOnInit(): void {
    // Get cursus ID from route parameters
    const idParam = this.route.snapshot.paramMap.get('id');
    const id = Number(idParam);

    // Validate cursus ID
    if (!id || Number.isNaN(id)) {
      this.errorMessage = 'Identifiant de cursus invalide.';
      this.isLoading = false;
      return;
    }

    // Fetch cursus with lessons preview
    this.cursusService.getCursusWithLessonsPreview(id).subscribe({
      next: (cursus) => {
        this.cursus = cursus;
        this.isLoading = false;
      },
      error: () => {
        this.errorMessage = 'Cursus introuvable.';
        this.isLoading = false;
      }
    });
  }

  // Handle cursus purchase action
  onBuyCursus(cursusId: number): void {
    // Logic to handle cursus purchase, now just a console log
    console.log(`Achat du cursus avec l'ID`, cursusId);
  }

  // Handle lesson purchase action
  onBuyLesson(lessonId: number): void {
    // Logic to handle lesson purchase, now just a console log
    console.log('Acheter la leçon', lessonId);
  }
}
