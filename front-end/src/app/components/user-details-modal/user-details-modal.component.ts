import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HostListener } from '@angular/core';
import { AdminUserListItem, AdminRole } from '../../models/admin-users.model';

@Component({
  selector: 'app-user-details-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './user-details-modal.component.html',
  styleUrl: './user-details-modal.component.scss'
})
export class UserDetailsModalComponent {
  // Input property to receive the user details
  @Input({ required: true }) user!: AdminUserListItem;

  // Output event emitter to notify when the modal should be closed
  @Output() closeModal = new EventEmitter<void>();

  // Method to define which role to display based on the roles array
  formatRoles(roles: AdminRole[]): string {
    return roles.includes('ROLE_ADMIN') ? 'Administrateur' : 'Utilisateur';
  }

  // Host listener to close the modal on Escape key press
  @HostListener('document:keydown.escape')
  onEscape(): void {
    this.closeModal.emit();
  }

  // Close when clicking outside the dialog
  onBackdropMouseDown(_: MouseEvent): void {
    this.closeModal.emit();
  }
}
