import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HostListener } from '@angular/core';
import { AdminLessonListItem } from '../../models/admin-lessons.model';

@Component({
  selector: 'app-lesson-details-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './lesson-details-modal.component.html',
  styleUrl: './lesson-details-modal.component.scss'
})
export class LessonDetailsModalComponent {
  // Input property to receive the lesson details
  @Input({ required: true }) lesson!: AdminLessonListItem;

  // Output event emitter to notify when the modal should be closed
  @Output() closeModal = new EventEmitter<void>();

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
