import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MyAccountService } from '../../services/my-account.service';
import { MyLessonDto } from '../../models/my-lesson.dto';
import { CertificationDto } from '../../models/certification.dto';

@Component({
  selector: 'app-my-account-page',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './my-account-page.component.html',
  styleUrl: './my-account-page.component.scss'
})
export class MyAccountPageComponent implements OnInit {
  private myAccountService = inject(MyAccountService);

  // User's lessons and certifications
  lessons: MyLessonDto[] = [];
  certifications: CertificationDto[] = [];

  // Loading and error states
  loadingLessons = true;
  loadingCertifications = true;
  errorLessons: string | null = null;
  errorCertifications: string | null = null;

  // Fetch user's lessons and certifications on component initialization
  ngOnInit(): void {
    this.myAccountService.getMyLessons().subscribe({
      next: (data) => {
        this.lessons = data;
        this.loadingLessons = false;
      },
      error: () => {
        this.errorLessons = 'Impossible de charger vos leçons.';
        this.loadingLessons = false;
      }
    });

    this.myAccountService.getMyCertifications().subscribe({
      next: (data) => {
        this.certifications = data;
        this.loadingCertifications = false;
      },
      error: () => {
        this.errorCertifications = 'Impossible de charger vos certifications.';
        this.loadingCertifications = false;
      }
    });
  }
}
