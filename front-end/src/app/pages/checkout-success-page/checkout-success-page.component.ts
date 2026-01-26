import { Component, inject } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';

@Component({
  selector: 'app-checkout-success-page',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './checkout-success-page.component.html',
  styleUrl: './checkout-success-page.component.scss'
})
export class CheckoutSuccessPageComponent {
  private route = inject(ActivatedRoute);

  // Get session ID from query parameters
  sessionId = this.route.snapshot.queryParamMap.get('session_id');
}
