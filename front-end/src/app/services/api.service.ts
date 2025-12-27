import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams, HttpContext } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

/**
 * Generic options for API calls.
 * 
 * - headers: custom HTTP headers
 * - params: query parameters (pagination, filters, etc.)
 * - responseType: useful for files (blob, text)
 */
type ParamsValue = string | number | boolean;
type HeadersRecord = Record<string, string | string[]>;
type ParamsRecord = Record<string, ParamsValue | ParamsValue[]>;

export interface ApiOptions {
  headers?: HttpHeaders | HeadersRecord;
  params?: HttpParams | ParamsRecord;
  context?: HttpContext;
  observe?: 'body';
  responseType?: 'json';
};

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);

  private readonly apiBaseUrl = environment.apiBaseUrl;

  /**
   * Constructs a complete URL from an API endpoint.
   */
   private buildUrl(path: string): string {
  if (!path || path.trim().length === 0) {
    throw new Error('ApiService: path is required');
  }

  const base = this.apiBaseUrl.replace(/\/+$/, '');
  const endpoint = path.startsWith('/') ? path : `/${path}`;

  return `${base}${endpoint}`;
}
  
  /**
   * Force the sending of Symfony session cookies
   */
  private withCredentials(options: ApiOptions = {}): ApiOptions & { withCredentials: true } {
    return {
      ...options,
      withCredentials: true
    };
  }

  /**
   * Request GET
   */
  get<T>(path: string, options?: ApiOptions): Observable<T> {
    return this.http.get<T>(
      this.buildUrl(path),
      this.withCredentials(options ?? {})
    );
  }

  /**
   * Request POST 
   */
  post<T>(path: string, body: unknown, options?: ApiOptions): Observable<T> {
    return this.http.post<T>(
      this.buildUrl(path),
      body,
      this.withCredentials(options ?? {})
    );
  }

  /**
   * Request PUT
   */
  put<T>(path: string, body: unknown, options?: ApiOptions): Observable<T> {
    return this.http.put<T>(
      this.buildUrl(path),
      body,
      this.withCredentials(options ?? {})
    );
  }

  /**
   * Request PATCH
   */
  patch<T>(path: string, body: unknown, options?: ApiOptions): Observable<T> {
    return this.http.patch<T>(
      this.buildUrl(path),
      body,
      this.withCredentials(options ?? {})
    );
  }

  /**
   * Request DELETE
   */
  delete<T>(path: string, options?: ApiOptions): Observable<T> {
    return this.http.delete<T>(
      this.buildUrl(path),
      this.withCredentials(options ?? {})
    );
  }
}