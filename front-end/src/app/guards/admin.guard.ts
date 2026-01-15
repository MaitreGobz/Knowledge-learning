import { inject } from '@angular/core';
import { CanActivateFn, Router, UrlTree } from '@angular/router';
import { catchError, map, of, switchMap } from 'rxjs';
import { AuthStateService } from '../services/auth-state.service';

/**
 * Guard to restrict access to admin routes.
 */
const ADMIN_DENIED_MESSAGE =
  "Être connecté en tant qu'admin est nécessaire pour acceder au backoffice";

export const adminGuard: CanActivateFn = (_route, state): ReturnType<CanActivateFn> => {
  const authState = inject(AuthStateService);
  const router = inject(Router);

  // The refresh covers: F5 / direct access / expired session
  return authState.refresh().pipe(
    switchMap(() => authState.user$),
    map((user): boolean | UrlTree => {
      const isAdmin = !!user?.roles?.includes('ROLE_ADMIN');
      if (isAdmin) return true;

      // Not an admin: redirect to home with query params
      return router.createUrlTree(['/'], {
        queryParams: {
          error: ADMIN_DENIED_MESSAGE,
          returnUrl: state.url
        }
      });
    }),
    catchError(() =>
      of(
        router.createUrlTree(['/'], {
          queryParams: {
            error: ADMIN_DENIED_MESSAGE,
            returnUrl: state.url
          }
        })
      )
    )
  );
};