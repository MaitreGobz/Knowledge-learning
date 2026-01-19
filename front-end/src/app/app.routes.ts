import { Routes } from '@angular/router';
import { adminGuard } from './guards/admin.guard';

export const routes: Routes = [
    { path: '', loadComponent: () => import('./pages/home-page/home-page.component').then(m => m.HomePageComponent) },
    { path: 'login', loadComponent: () => import('./pages/login-page/login-page.component').then(m => m.LoginPageComponent) },
    { path: 'register', loadComponent: () => import('./pages/register-page/register-page.component').then(m => m.RegisterPageComponent) },
    { path: 'verify-email', loadComponent: () => import('./pages/verify-email-page/verify-email-page.component').then(m => m.VerifyEmailPageComponent) },
    { path: 'themes', loadComponent: () => import('./pages/themes-page/themes-page.component').then(m => m.ThemesPageComponent) },
    { path: 'cursus/:id', loadComponent: () => import('./pages/cursus-page/cursus-page.component').then(m => m.CursusPageComponent) },
    {
        path: 'admin', canActivate: [adminGuard], children: [
            {
                path: '', loadComponent: () => import('./pages/admin-dashboard-page/admin-dashboard-page.component').then(m => m.AdminDashboardPageComponent),
            },
            {
                path: 'users', loadComponent: () => import('./pages/admin-users-page/admin-users-page.component').then(m => m.AdminUsersPageComponent),
            },
        ]
    },
    { path: '**', loadComponent: () => import('./pages/not-found-page/not-found-page.component').then(m => m.NotFoundPageComponent) },
];
