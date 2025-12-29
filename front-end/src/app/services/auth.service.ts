import { Injectable, inject } from '@angular/core';
import { Observable, BehaviorSubject, tap } from 'rxjs';
import { ApiService } from './api.service';


/**
 * Payload sent to the API when registering a new user.
 */
export interface RegisterPayload {
  email: string;
  password: string;
}

/**
 * Response received from the API after registering a new user.
 */
export interface RegisterResponse {
    id: number;
    email: string;
    status: 'PENDING_VERIFICATION';
    message: string;
}

/**
 * Validation error format returned by the API.
 */
export interface ApiValidationError {
    errors: Record<string, string[]>;
}

/**
 * Generic error message format returned by the API.
 */
export interface ApiMessageError {
    message: string;
}

/**
 * Payload sent to the API when authenticating a user.
 */
export interface LoginPayload {
  email: string;
  password: string;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
    private api = inject(ApiService);

    /**
     * Simple UI auth state management.
     * This is used by the Header to toggle "Connexion" / "Se déconnecter".
     */
    private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);
    readonly isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

    /**
     * Registers a new user account.
     * 
     * The backend is responsible for validation and email verification.
     */
    register(payload: RegisterPayload): Observable<RegisterResponse> {
        return this.api.post<RegisterResponse>('/api/auth/register', payload);
    }

    /**
     * Authenticates a user and starts a session (cookie-based authentication).
     * On success, we update the UI auth state.
     */
    login(payload: LoginPayload): Observable<unknown> {
        return this.api.post('/api/auth/login', payload).pipe(
            tap(() => this.setAuthenticated(true))
        );
    }

    /**
     * Verifies the user's email address.
     * 
     * The query string is provided by the backend email link
    */
    verifyEmail(queryString: string): Observable<unknown> {
        return this.api.get(`/api/auth/verify-email?${queryString}`);
    }

    /**
     * Logout the current user and invalidates the server-side session.
     * On success, we update the UI auth state.
     */
    logout(): Observable<unknown> {
        return this.api.post('/api/auth/logout', {}).pipe(
            tap(() => this.setAuthenticated(false))
        );
    }

    /**
     * Allows components/pages to manually set the UI auth state if needed.
     */
    setAuthenticated(value: boolean): void {
        this.isAuthenticatedSubject.next(value);
    }
}
