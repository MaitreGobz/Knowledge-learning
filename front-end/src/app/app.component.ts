import { Component, OnInit, inject } from '@angular/core';
import { AuthStateService } from './services/auth-state.service';
import { RouterOutlet } from '@angular/router';
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

  private authState = inject(AuthStateService);

  ngOnInit(): void {
    // On application startup, refresh the authentication state
    this.authState.refresh().subscribe();
  }
}
