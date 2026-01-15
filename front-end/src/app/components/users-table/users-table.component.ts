import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminUserListItem } from '../../models/admin-users.model';

@Component({
  selector: 'app-users-table',
  standalone: true,
  imports: [ CommonModule],
  templateUrl: './users-table.component.html',
  styleUrl: './users-table.component.scss'
})
export class UsersTableComponent {

  // Input property to receive the list of users to display in the table
  @Input({ required: true }) users: AdminUserListItem[] = [];

  // Output event emitter to notify when a user is to be edited
  @Output() editUser = new EventEmitter<AdminUserListItem>();
  @Output() desactivateUser = new EventEmitter<AdminUserListItem>();
  @Output() viewUser = new EventEmitter<AdminUserListItem>();

  // Method to define wich role to display based on the roles array
  formatRoles(roles: string[]): string {
    const setRoles = roles ?? [];

    if(setRoles.includes('ROLE_ADMIN')) {
      return 'Administrateur';
    } else {
      return 'Utilisateur';
    }
  }
}
