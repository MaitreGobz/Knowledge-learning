import { Routes } from '@angular/router';

export const routes: Routes = [
    { path: '', loadComponent: () => import('./pages/home-page/home-page.component').then(m => m.HomePageComponent) },
    { path: 'login', loadComponent: () => import('./pages/login-page/login-page.component').then(m => m.LoginPageComponent) },
    { path: 'register', loadComponent: () => import('./pages/register-page/register-page.component').then(m => m.RegisterPageComponent) },
    { path: 'verify-email', loadComponent: () => import('./pages/verify-email-page/verify-email-page.component').then(m => m.VerifyEmailPageComponent) },
    { path: '**', loadComponent: () => import('./pages/not-found-page/not-found-page.component').then(m => m.NotFoundPageComponent) },
];
