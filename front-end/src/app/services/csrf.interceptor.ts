import { inject, Injectable } from '@angular/core';
import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, switchMap } from 'rxjs/operators';
import { CsrfService } from './csrf.service';

@Injectable()
export class CsrfInterceptor implements HttpInterceptor {
    // Service responsible for fetching and caching the CSRF token
    private csrf = inject(CsrfService);

    intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
        // Do not modify the GET/HEAD/OPTIONS requests.
        if (req.method === 'GET' || req.method === 'HEAD' || req.method === 'OPTIONS') {
        return next.handle(req);
        }

        // If the request already contains a CSRF header, leave it untouched (useful for tests or special cases)
        if (req.headers.has('X-CSRF-TOKEN')) {
        return next.handle(req);
        }

        // 3) For POST/PUT/PATCH/DELETE, we fetch the token and inject the header
        return this.csrf.getCsrfToken().pipe(
            switchMap((token) => {
                // Clone the request to add the new CSRF header
                const cloned = req.clone({
                    setHeaders: {
                        'X-CSRF-TOKEN': token
                    }
                });

                return next.handle(cloned);
            }),
            catchError((err: HttpErrorResponse) => {
            // If the backend rejects the request due to an invalid CSRF token, invalidate the cached token so it can be refreshed on the next request
                if (err.status === 403 || err.status === 419) {
                this.csrf.invalidateToken();
                }
                
                return throwError(() => err);
            })
        );
    }
}
