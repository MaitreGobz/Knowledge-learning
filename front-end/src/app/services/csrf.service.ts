import { Injectable, inject } from '@angular/core';
import { Observable, of, throwError } from 'rxjs';
import { catchError, map, shareReplay, tap } from 'rxjs/operators';
import { ApiService } from './api.service';

interface CsrfResponse {
  csrfToken: string;
}

@Injectable({ providedIn: 'root' })
export class CsrfService {
    private csrfToken: string | null = null;
    
    private csrfTokenRequest$: Observable<string> | null = null;

    private api = inject(ApiService);

    /**
     * Returns a CSRF token:
     * 
     * - if already in memory => returns immediately
     * - otherwise => call /api/auth/csrf and cache
     */
    getCsrfToken(): Observable<string> {
        // Memory cache
        if (this.csrfToken) {
            return of(this.csrfToken);
        }

        // Request already in progress: avoid duplicates
        if (this.csrfTokenRequest$) {
            return this.csrfTokenRequest$;
        }

        // 3) New API request
        this.csrfTokenRequest$ = this.api
            .get<CsrfResponse>('/api/auth/csrf')
            .pipe(
                map((res) => res.csrfToken),
                tap((token) => (this.csrfToken = token)),

                // shareReplay(1) allows:
                // - sharing the same request between multiple subscribers
                // - replaying the last token received
                shareReplay(1),
                catchError((err) => {

                // If there is an error, the state is reset.
                this.csrfTokenRequest$ = null;
                this.csrfToken = null;
                return throwError(() => err);
                }),
                tap({
                complete: () => {
                    // Once finished, the "in-flight" is reset to null.
                    this.csrfTokenRequest$ = null;
                }
            })
        );

        return this.csrfTokenRequest$;
    }

    /**
     * Invalidates the cached token.
     * Call this if the backend responds "CSRF invalid/expired".
     */
    invalidateToken(): void {
        this.csrfToken = null;
        this.csrfTokenRequest$ = null;
    }
}