import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly apiBaseUrl = environment.apiBaseUrl;

  constructor(private http: HttpClient) { }
  
  get<T>(path: string) {
    return this.http.get<T>(`${this.apiBaseUrl}${path}`);
  }

  post<T>(path: string, body: unknown) {
    return this.http.post<T>(`${this.apiBaseUrl}${path}`, body);
  }
}