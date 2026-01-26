import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

export type CheckoutType = 'cursus' | 'lesson';

export interface CheckoutResponse {
  sessionId: string;
  checkoutUrl: string;
}

@Injectable({ providedIn: 'root' })
export class PaymentService {
  private api = inject(ApiService);

  // Create a checkout session for a cursus or lesson purchase
  createCheckout(type: CheckoutType, id: number): Observable<CheckoutResponse> {
    return this.api.post<CheckoutResponse>('/api/payments/checkout', { type, itemId: id });
  }
}
