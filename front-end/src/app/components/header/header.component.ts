import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { AuthStateService } from '../../services/auth-state.service';
import { AsyncPipe } from '@angular/common';

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

  isLoggedIn$ = this.authState.isLoggedIn$;

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
