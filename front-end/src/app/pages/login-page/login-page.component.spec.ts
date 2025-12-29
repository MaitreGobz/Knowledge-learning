import { TestBed } from '@angular/core/testing';
import { LoginPageComponent } from './login-page.component';
import { AuthService } from '../../services/auth.service';
import { AuthStateService } from '../../services/auth-state.service';
import { Router } from '@angular/router';
import { of, throwError } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';

describe('LoginPageComponent', () => {
  let authService: jasmine.SpyObj<AuthService>;
  let authState: jasmine.SpyObj<AuthStateService>;
  let router: jasmine.SpyObj<Router>;

  beforeEach(async () => {
    authService = jasmine.createSpyObj<AuthService>('AuthService', ['login']);
    authState = jasmine.createSpyObj<AuthStateService>('AuthStateService', ['refresh', 'setLoggedIn']);
    router = jasmine.createSpyObj<Router>('Router', ['navigateByUrl']);

    await TestBed.configureTestingModule({
      imports: [LoginPageComponent], // standalone component
      providers: [
        { provide: AuthService, useValue: authService },
        { provide: AuthStateService, useValue: authState },
        { provide: Router, useValue: router }
      ],
    }).compileComponents();
  });

  it('connecte l\'utilisateur et redirige vers / en cas de succès', () => {
    const fixture = TestBed.createComponent(LoginPageComponent);
    const component = fixture.componentInstance;

    component.form.setValue({ email: 'user@test.com', password: 'Password123!' });

    authService.login.and.returnValue(of({}));
    authState.refresh.and.returnValue(of(true));

    component.submit();

    expect(authService.login).toHaveBeenCalledWith({
      email: 'user@test.com',
      password: 'Password123!'
    });

    expect(authState.refresh).toHaveBeenCalled();

    expect(router.navigateByUrl).toHaveBeenCalledWith('/');
  });

  it('affiche un message adapté quand le backend répond 401', () => {
    const fixture = TestBed.createComponent(LoginPageComponent);
    const component = fixture.componentInstance;

    component.form.setValue({ email: 'unverified@test.com', password: 'Password123!' });

    authService.login.and.returnValue(
      throwError(() => new HttpErrorResponse({ status: 401 }))
    );

    component.submit();

    expect(component.errorMessage).toContain('Identifiants incorrects');
  });
});
