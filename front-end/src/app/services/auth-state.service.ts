import { Injectable, inject } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { ApiService } from './api.service';
import { MeResponse } from '../models/me.model';

// Type representing the authenticated user (or null when logged out)
type AuthUser = MeResponse['user'] | null;

@Injectable({ providedIn: 'root' })
export class AuthStateService {
  /**
   * Internal subjects holding the authentication state.
   */
  private isLoggedInSubject = new BehaviorSubject<boolean>(false);
  private userSubject = new BehaviorSubject<AuthUser>(null);

  /**
   * Public observables exposed to the rest of the application
   */
  isLoggedIn$ = this.isLoggedInSubject.asObservable();
  user$ = this.userSubject.asObservable();

  private api = inject(ApiService);

  /**
   * Checks if a user session is active by calling the backend.
   * Returns true if authenticated, false otherwise.
   */
  refresh(): Observable<boolean> {
    return this.api.get<MeResponse>('/api/auth/me').pipe(
        map((res) => {
            const isLoggedIn = !!res?.authenticated;
            this.isLoggedInSubject.next(isLoggedIn);
            this.userSubject.next(isLoggedIn ? res.user : null);
            return isLoggedIn;
        }),
        catchError(() => {
            this.setLoggedOut();
            return of(false);
        })
        );
    }

    /**
     * Sets the authentication state after a successful login.
     */
    setLoggedIn(user: AuthUser): void {
        this.isLoggedInSubject.next(true);
        this.userSubject.next(user);
    }

    /**
     * Resets the authentication state (logout or invalid session).
     */
    setLoggedOut(): void {
        this.isLoggedInSubject.next(false);
        this.userSubject.next(null);
    }

    /**
     * Synchronous accessors (snapshots)
     */
    get userSnapshot(): AuthUser {
        return this.userSubject.value;
    }

    get isLoggedInSnapshot(): boolean {
        return this.isLoggedInSubject.value;
    }

    /**
     * Return the post-login redirect URL based on user role.
     */
    getPostLoginRedirectUrl(): string {
        const user = this.userSnapshot;
        const isAdmin = !!user?.roles?.includes('ROLE_ADMIN');
        return isAdmin ? '/admin' : '/';
    }
}
