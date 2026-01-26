import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { ThemeService } from '../../services/theme.service';
import { ThemeCursusPreview } from '../../models/theme.model';
import { PaymentService } from '../../services/payment.service';

@Component({
  selector: 'app-themes-page',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './themes-page.component.html',
  styleUrl: './themes-page.component.scss'
})
export class ThemesPageComponent implements OnInit{
  themes: ThemeCursusPreview[] = [];
  isLoading = true;
  errorMessage: string | null = null;

  // State for handling cursus purchase
  isBuying = false;
  buyErrorMessage: string | null = null;

  private themeService = inject(ThemeService);
  private paymentService = inject(PaymentService);
  private router = inject(Router);

  // On component initialization, fetch themes with cursus previews
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

  // Handle cursus purchase action
  onBuyCursus(cursusId: number): void {
    this.buyErrorMessage = null;

    // Validate cursus ID
    if (!cursusId || Number.isNaN(cursusId)) {
      this.buyErrorMessage = "Identifiant de cursus invalide.";
      return;
    }

    this.isBuying = true;

    //Create checkout session
    this.paymentService.createCheckout('cursus', cursusId).subscribe({
      next: (res) => {
        window.location.href = res.checkoutUrl;
      },
      error: (err) => {
        this.isBuying = false;

        // Error handling based on status codes
        if (err?.status === 401) {
          this.router.navigate(['/login']);
          return;
        }
        if (err?.status === 403) {
          this.buyErrorMessage = "Votre compte n'est pas activé ou vous n'êtes pas autorisé à acheter.";
          return;
        }
        if (err?.status === 409) {
          this.buyErrorMessage = "Ce cursus a déjà été acheté.";
          return;
        }
        if (err?.status === 404) {
          this.buyErrorMessage = "Cursus introuvable.";
          return;
        }
        this.buyErrorMessage = "Impossible de démarrer le paiement. Réessayez.";
      }
    });
  }
}
