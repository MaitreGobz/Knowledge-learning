import { Component, OnInit, inject } from '@angular/core';
import { ActivatedRoute, RouterModule, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { CursusService } from '../../services/cursus.service';
import { CursusLessonPreview } from '../../models/cursus.model';
import { PaymentService, CheckoutType } from '../../services/payment.service';

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

  // State for handling purchases
  isBuying = false;
  buyErrorMessage: string | null = null;

  private cursusService = inject(CursusService);
  private route = inject(ActivatedRoute);
  private paymentService = inject(PaymentService);
  private router = inject(Router);

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
    this.startCheckout('cursus', cursusId);
  }

  // Handle lesson purchase action
  onBuyLesson(lessonId: number): void {
    this.startCheckout('lesson', lessonId);
  }

  // Common method to start checkout for cursus or lesson
  private startCheckout(type: CheckoutType, itemId: number): void {
    this.buyErrorMessage = null;

    // Validate item ID
    if (!itemId || Number.isNaN(itemId)) {
      this.buyErrorMessage = "Identifiant invalide.";
      return;
    }

    this.isBuying = true;

    // Create checkout session
    this.paymentService.createCheckout(type, itemId).subscribe({
      next: (res) => {
        window.location.href = res.checkoutUrl;
      },
      // Error handling based on status codes
      error: (err) => {
        this.isBuying = false;

        if (err?.status === 401) {
          this.router.navigate(['/login']);
          return;
        }
        if (err?.status === 403) {
          this.buyErrorMessage = "Votre compte n'est pas activé ou vous n'êtes pas autorisé à acheter.";
          return;
        }
        if (err?.status === 409) {
          this.buyErrorMessage = type === 'cursus'
            ? "Ce cursus a déjà été acheté."
            : "Cette leçon a déjà été achetée.";
          return;
        }
        if (err?.status === 404) {
          this.buyErrorMessage = type === 'cursus'
            ? "Cursus introuvable."
            : "Leçon introuvable.";
          return;
        }

        this.buyErrorMessage = "Impossible de démarrer le paiement. Réessayez.";
      }
    });
  }
}
