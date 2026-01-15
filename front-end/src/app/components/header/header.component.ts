import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { AuthStateService } from '../../services/auth-state.service';
import { AsyncPipe } from '@angular/common';
import { map } from 'rxjs/operators';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, AsyncPipe],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss'
})
export class HeaderComponent {
  // Observable used by the template to display Login or Logout
  private auth = inject(AuthService);
  private authState = inject(AuthStateService);
  private router = inject(Router);

  // Expose observables to the template
  readonly isLoggedIn$ = this.authState.isLoggedIn$;

  // Admin is derived from user$ (no snapshot needed in the template)
  readonly isAdmin$ = this.authState.user$.pipe(
    map(user => !!user?.roles?.includes('ROLE_ADMIN'))
  );

  logout(): void {
    this.auth.logout().subscribe({
      next: () => this.finishLogout(),
      error: () => this.finishLogout()
    });
  }

  private finishLogout(): void {
    this.authState.setLoggedOut();
    this.router.navigateByUrl('/');
  }
}
