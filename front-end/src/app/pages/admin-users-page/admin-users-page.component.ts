import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { AdminUserService } from '../../services/admin-user.service';
import { AdminUserListItem, CreateUserPayload, UpdateUserPayload } from '../../models/admin-users.model';
import { UserFormModalComponent } from '../../components/user-form-modal/user-form-modal.component';
import { UserDetailsModalComponent } from '../../components/user-details-modal/user-details-modal.component';
import { UsersTableComponent } from '../../components/users-table/users-table.component';
import { PaginationComponent } from '../../components/pagination/pagination.component';

@Component({
  selector: 'app-admin-users-page',
  standalone: true,
  imports: [CommonModule, UserFormModalComponent, UserDetailsModalComponent, UsersTableComponent, PaginationComponent],
  templateUrl: './admin-users-page.component.html',
  styleUrl: './admin-users-page.component.scss'
})
export class AdminUsersPageComponent implements OnInit {
  // State signals
  loading = signal(false);
  errorMessage = signal<string | null>(null);
  users = signal<AdminUserListItem[]>([]);
  meta = signal<{ page: number; limit: number; totalItems: number; totalPages: number } | null>(null);
  page = signal(1);
  limit = signal(20);

  // Modal state signals
  modalOpen = signal(false);
  detailsModalOpen = signal(false);
  selectedUserForDetails = signal<AdminUserListItem | null>(null);
  modalMode = signal<'create' | 'edit'>('create');
  editingUser = signal<AdminUserListItem | null>(null);

  private adminUser = inject(AdminUserService);

  // On component initialization, load the list of users
  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.adminUser
      .listUsers({
        page: this.page(),
        limit: this.limit()
      })
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: (res) => {
          this.users.set(res.items);
          this.meta.set(res.meta);
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors du chargement.');
        }
      });
  }

  // Handle user desactivation action
  onDesactivate(user: AdminUserListItem): void {
    const ok = window.confirm(`Êtes-vous sûr de vouloir désactiver "${user.email}" ?`);
    if (!ok) return;

    this.loading.set(true);
    this.adminUser
      .desactivateUser(user.id)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.loadUsers();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la désactivation.');
        }
      });
  }

  // Handle page change action
  onPageChange(page: number): void {
    const totalPages = this.meta()?.totalPages ?? 1;
    const newPage = Math.min(Math.max(1, page), totalPages);
    this.page.set(newPage);
    this.loadUsers();
  }

  // Handle modal open actions
  openCreate(): void {
    this.modalMode.set('create');
    this.editingUser.set(null);
    this.modalOpen.set(true);
  }

  // Handle modal open actions
  openEdit(user: AdminUserListItem): void {
    this.modalMode.set('edit');
    this.editingUser.set(user);
    this.modalOpen.set(true);
  }

  // Handle details modal open action
  openDetails(user: AdminUserListItem): void {
    this.selectedUserForDetails.set(user);
    this.detailsModalOpen.set(true);
  }

  // Handle modal close actions
  closeDetails(): void {
    this.detailsModalOpen.set(false);
    this.selectedUserForDetails.set(null);
  }

  closeModal(): void {
    this.modalOpen.set(false);
  }

  // Handle user creation action
  onCreate(payload: CreateUserPayload): void {
    this.loading.set(true);
    this.adminUser
      .createUser(payload)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.closeModal();
          this.loadUsers();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la création.');
        }
      });
  }

  // Handle user update action
  onUpdate(payload: UpdateUserPayload): void {
    const user = this.editingUser();
    if (!user) return;

    this.loading.set(true);
    this.adminUser
      .updateUser(user.id, payload)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.closeModal();
          this.loadUsers();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la mise à jour.');
        }
      });
  }
}
