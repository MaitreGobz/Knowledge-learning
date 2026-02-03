import { inject } from '@angular/core';
import { CanActivateFn, Router, UrlTree } from '@angular/router';
import { catchError, map, of, switchMap } from 'rxjs';
import { AuthStateService } from '../services/auth-state.service';

// Message displayed when an unauthenticated user attempts to access a protected route
const LOGIN_REQUIRED_MESSAGE = 'Vous devez être connecté pour accéder à cette page.';

// Guard to ensure the user is authenticated before accessing certain routes
export const userGuard: CanActivateFn = (_route, state): ReturnType<CanActivateFn> => {
  const authState = inject(AuthStateService);
  const router = inject(Router);

    // Refresh the authentication state and check if the user is logged in
    return authState.refresh().pipe(
        switchMap(() => authState.user$),
        map((user): boolean | UrlTree => {
            if (user) return true;

            // Redirect unauthenticated users to the login page with an error message and return URL
            return router.createUrlTree(['/login'], {
                queryParams: {
                    error: LOGIN_REQUIRED_MESSAGE,
                    returnUrl: state.url
                }
            });
        }),

        // Handle any errors during the authentication check by redirecting to the login page
        catchError(() =>
            of(
                router.createUrlTree(['/login'], {
                    queryParams: {
                        error: LOGIN_REQUIRED_MESSAGE,
                        returnUrl: state.url
                    }
                })
            )
        )
    );
};
