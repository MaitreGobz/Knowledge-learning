import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { AuthService, ApiMessageError, ApiValidationError, RegisterResponse } from '../../services/auth.service';

@Component({
  selector: 'app-register-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './register-page.component.html',
  styleUrl: './register-page.component.scss'
})
export class RegisterPageComponent {
  // Prevents multiple submissions and allows UI feedback (disabled button, spinner, etc.)
  isSubmitting = false;

  // Successful registration response (used to display confirmation message)
  success: RegisterResponse | null = null;

  /**
   * Field-level validation errors returned by the API
   */
  fieldErrors: Record<string, string[] | undefined> = {};

  // Global error message
  globalError: string | null = null;

  // Reactive form definition
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  
  form = this.fb.nonNullable.group(
    {
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
    });

  /**
   * Handles form submission.
   */
  submit(): void {
    // Reset previous state
    this.success = null;
    this.globalError = null;
    this.fieldErrors = {};

    // Stop if the form is invalid
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.isSubmitting = true;

    // Extract form values
    const { email, password } = this.form.getRawValue();
    
    //// Build payload expected by the API
    const payload = { email, password };

    this.auth.register(payload).subscribe({
      next: (res) => {
        // Registration successful
        this.success = res;
        this.isSubmitting = false;
        this.form.controls.password.reset('');
      },
      error: (err: HttpErrorResponse) => {
        this.isSubmitting = false;

        /**
         * 422 Unprocessable Entity: Validation errors returned by the API
         */
        if (err.status === 422 && err.error && (err.error as ApiValidationError).errors) {
          this.fieldErrors = (err.error as ApiValidationError).errors ?? {};
          return;
        }

        /**
         * 409 Conflict : Email already exists or similar business rule violation
         */
        if (err.status === 409 && err.error && (err.error as ApiMessageError).message) {
          this.globalError = (err.error as ApiMessageError).message;
          return;
        }

        // Fallback for unexpected errors
        this.globalError = 'Une erreur est survenue. Veuillez réessayer.';
      }
    });
  }

  /**
   * Template helper to determine whether a form control
   * should be displayed as invalid.
   */
  hasError(controlName: 'email' | 'password'): boolean {
    const control = this.form.controls[controlName];
    return control.touched && control.invalid;
  }
}
