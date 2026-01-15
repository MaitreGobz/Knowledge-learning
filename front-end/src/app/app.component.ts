import { Component, OnInit, inject } from '@angular/core';
import { RouterOutlet, Router, NavigationEnd, ActivatedRoute } from '@angular/router';
import { filter } from 'rxjs/operators';
import { AuthStateService } from './services/auth-state.service';
import { HeaderComponent } from "./components/header/header.component";
import { FooterComponent } from "./components/footer/footer.component";


@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, HeaderComponent, FooterComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit {
  appName = 'Knowledge Learning';

  // To display error messages (e.g. admin guard redirect)
  errorMessage: string | null = null;
  
  private authState = inject(AuthStateService);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  ngOnInit(): void {
    // On application startup, refresh the authentication state
    this.authState.refresh().subscribe();

    // Read "error" from query params after each navigation end
    this.router.events
      .pipe(filter((event): event is NavigationEnd => event instanceof NavigationEnd))
      .subscribe(() => {
        // We want the deepest active route to read its query params
        let r: ActivatedRoute = this.route;
        while (r.firstChild) {
          r = r.firstChild;
        }

        const error = r.snapshot.queryParamMap.get('error');
        this.errorMessage = error ? error : null;
      });
  }
}
