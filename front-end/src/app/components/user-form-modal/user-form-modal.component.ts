import { Component, EventEmitter, Input, Output, OnChanges, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { HostListener, OnDestroy, OnInit } from '@angular/core';
import { AdminRole, AdminUserListItem, CreateUserPayload, UpdateUserPayload } from '../../models/admin-users.model';

@Component({
  selector: 'app-user-form-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './user-form-modal.component.html',
  styleUrl: './user-form-modal.component.scss'
})
export class UserFormModalComponent implements OnChanges, OnInit, OnDestroy {
  // Input property to determine if the modal is in create or edit mode
  @Input({ required: true }) mode!: 'create' | 'edit';
  @Input() user: AdminUserListItem | null = null;

  // Output event emitters for creating, updating, and closing the modal
  @Output() createUser = new EventEmitter<CreateUserPayload>();
  @Output() updateUser = new EventEmitter<UpdateUserPayload>();
  @Output() closeModal = new EventEmitter<void>();

  private fb = inject(FormBuilder);

  // Reactive form group for the user form
  userForm = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: [''],
    roles: ['ROLE_USER' as AdminRole],
    isVerified: [false]
  });

  ngOnChanges(): void {
    // Validators password field based on mode
    if (this.mode === 'create') {
      this.userForm.controls.password.setValidators([Validators.required, Validators.minLength(8)]);
    } else {
      this.userForm.controls.password.clearValidators();
    }
    this.userForm.controls.password.updateValueAndValidity();

    // Patch form values based on mode and user data
    if (this.mode === 'edit' && this.user) {
      this.userForm.patchValue({
        email: this.user.email,
        password: '',
        roles: (this.user.roles?.[0] as AdminRole) ?? 'ROLE_USER',
        isVerified: this.user.isVerified
      }, { emitEvent: false });
    } else if (this.mode === 'create') {
      this.userForm.reset({
        email: '',
        password: '',
        roles: 'ROLE_USER',
        isVerified: false
      }, { emitEvent: false });
    }
  }

  ngOnInit(): void {
    // Prevent scrolling behind the modal
    document.body.style.overflow = 'hidden';
  }

  ngOnDestroy(): void {
    // Restore scrolling when the modal is destroyed
    document.body.style.overflow = 'auto';
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

  // Method to handle form submission
  onSubmit(): void {
    if (this.userForm.invalid) {
      this.userForm.markAllAsTouched();
      return;
    }

    const email = this.userForm.value.email!.trim();
    const password = (this.userForm.value.password ?? '').trim();
    const roles = this.userForm.value.roles as AdminRole;
    const isVerified = !!this.userForm.value.isVerified;

    // Emit appropriate event based on mode
    if (this.mode === 'create') {
      this.createUser.emit({
        email,
        password,
        roles: [roles],
        isVerified,
        isActive: true
      });
      return;
    }

    const payload: UpdateUserPayload = {
      email,
      roles: [roles],
      isVerified
    };

    if (password.length) {
      payload.password = password;
    }
    this.updateUser.emit(payload);
  }
}
