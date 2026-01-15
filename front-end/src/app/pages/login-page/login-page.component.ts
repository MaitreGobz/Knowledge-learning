import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';
import { switchMap, finalize } from 'rxjs/operators';
import { AuthService } from '../../services/auth.service';
import { AuthStateService } from '../../services/auth-state.service';

@Component({
  selector: 'app-login-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login-page.component.html',
  styleUrl: './login-page.component.scss'
})
export class LoginPageComponent {
  // Indicates whether the login request is currently in progress
  isLoading = false;

  // Global error message displayed to the user
  errorMessage: string | null = null;

  // Reactive form definition with basic client-side validation
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private authState = inject(AuthStateService);
  private router = inject(Router);

  form = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required]]
  });

  /**
   * Handles form submission.
   * Performs client-side validation, sends credentials to the API,
   * and updates the UI based on the authentication result.
   */
  submit(): void {
    //Reset previous error state
    this.errorMessage = null;

    // Stop submission if the form is invalid
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.isLoading = true;

    // Extract form values and build the login payload
    const payload = {
      email: this.form.value.email!,
      password: this.form.value.password!
    };



    this.auth.login(payload).pipe(
      // After successful login, refresh the user state from /api/auth/me
      switchMap(() => this.authState.refresh()),
      // Always stop the loader (success or error)
      finalize(() => {
        this.isLoading = false;
      })
    ).subscribe({
      next: () => {
        // userSnapshot is up-to-date after refresh()
        const redirectUrl = this.authState.getPostLoginRedirectUrl();
        this.router.navigateByUrl(redirectUrl);
      },
      error: (err: HttpErrorResponse) => {
        // Login failed: map the error to a user-friendly message
        this.errorMessage = this.mapLoginError(err);
      }
    });
  }

  /**
   * Maps backend HTTP errors to user-frendly error messages
   */
  private mapLoginError(err: HttpErrorResponse): string {
    // 401 Unauthorized: invalid credentials
    if (err.status === 401) {
      return 'Identifiants incorrects. Vérifie ton email et ton mot de passe.';
    }

     // 403 Forbidden: account not verified or access denied
    if (err.status === 403) {
      return 'Compte non activé. Vérifie ton email d\'activation.';
    }

    // Fallback message for unexpected errors
    return 'Une erreur est survenue. Réessaie plus tard.';
  }

  /**
   * Template helpers to improve readability and avoid repeated lookups.
   */
  get email() {
    return this.form.get('email');
  }

  get password() {
    return this.form.get('password');
  }
}