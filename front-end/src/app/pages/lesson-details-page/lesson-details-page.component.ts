import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { LessonService } from '../../services/lesson.service';
import { LessonDetailsDto } from '../../models/lesson-details.dto';

@Component({
  selector: 'app-lesson-details-page',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './lesson-details-page.component.html',
  styleUrl: './lesson-details-page.component.scss'
})
export class LessonDetailsPageComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private lessonService = inject(LessonService);

  lesson: LessonDetailsDto | null = null;
  videoSrc: string | null = null;

  // State variables
  loading = true;
  errorMessage: string | null = null;

  validated = false;
  validationInfo: string | null = null;

  validating = false;
  validateError: string | null = null;

  // Method to handle lesson validation
  ngOnInit(): void {
    const lessonId = Number(this.route.snapshot.paramMap.get('id'));

    // Fetch lesson details
    this.lessonService.getLessonDetails(lessonId).subscribe({
      next: (data) => {
        this.lesson = data;
        this.loading = false;
        // After loading lesson details, check validation state
        this.lessonService.getValidationState(lessonId).subscribe({
          next: (state) => {
            this.validated = state.validated;
            if (this.validated) {
              this.validationInfo = 'La leçon est déjà validée.';
            }
          }
        });
      },
      error: (err: HttpErrorResponse) => {
        this.loading = false;
        this.errorMessage = err.status === 404 ? 'Leçon non trouvée.' : 'Une erreur est survenue lors du chargement de la leçon.';
      }
    });
  }

  // Method to handle lesson validation
  onValidate(): void {
    if (!this.lesson || this.validating || this.validated) return;

    this.validating = true;
    this.validateError = null;

    // Call the validation API
    this.lessonService.validateLesson(this.lesson.id).subscribe({
      next: () => {
        this.validated = true;
        this.validationInfo = 'Leçon validée avec succès !';

        this.validating = false;

        this.router.navigate(['/my-account']);
      },
      // Handle validation errors
      error: (err: HttpErrorResponse) => {
        this.validating = false;

        if (err.status === 409) {
          this.validated = true;
          this.validationInfo = 'La leçon est déjà validée.';
          return;
        }

        this.validateError =
          err.status === 403 ? 'Accès interdit : achat requis.' : 'Erreur lors de la validation.';
      }
    });
  }
}