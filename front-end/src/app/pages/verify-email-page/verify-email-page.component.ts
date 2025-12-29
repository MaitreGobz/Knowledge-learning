import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { HttpErrorResponse, HttpParams } from '@angular/common/http';
import { AuthService, ApiMessageError } from '../../services/auth.service';

@Component({
  selector: 'app-verify-email-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './verify-email-page.component.html',
  styleUrl: './verify-email-page.component.scss'
})
export class VerifyEmailPageComponent implements OnInit {
  // Access to the current route to read query parameters from the URL
  private route = inject(ActivatedRoute);
  // Authentication service responsible for email verification
  private auth = inject(AuthService);

  // UI state flags
  isLoading = true;
  isSuccess = false;
  // Error message displayed to the user
  errorMessage: string | null = null;

  ngOnInit(): void {
    /**
     * Read all query parameters from the URL
     */
    const queryParamMap = this.route.snapshot.queryParamMap;

    /**
     * Rebuild a clean query string
     */
    const paramsObject: Record<string, string> = {};

    for (const key of queryParamMap.keys) {
      const value = queryParamMap.get(key);
      if (value !== null) {
        paramsObject[key] = value;
      }
    }

    const queryString = new HttpParams({ fromObject: paramsObject }).toString();

    /**
     * If no query string is present, immediately show an error
     */
    if (!queryString) {
      this.isLoading = false;
      this.isSuccess = false;
      this.errorMessage = 'Lien de vérification invalide.'
    }

    /**
     * Call the API to verify the email address
     */
    this.auth.verifyEmail(queryString).subscribe({
      next: () => {
        // Email successfully verified
        this.isLoading = false;
        this.isSuccess = true;
        this.errorMessage = null;
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.isSuccess = false;
        const apiMessage = err.error && (err.error as ApiMessageError).message;
        this.errorMessage = apiMessage ?? 'Lien de vérification invalide ou expiré.';
      },
    });
  }
}
