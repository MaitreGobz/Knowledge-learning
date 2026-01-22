import { Component, EventEmitter, Input, Output, OnChanges, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { HostListener, OnDestroy, OnInit } from '@angular/core';
import { CreateLessonPayload, UpdateLessonPayload, AdminCursusOption, AdminLessonListItem } from '../../models/admin-lessons.model';
import { AdminLessonService } from '../../services/admin-lesson.service';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-lesson-form-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './lesson-form-modal.component.html',
  styleUrl: './lesson-form-modal.component.scss'
})
export class LessonFormModalComponent implements OnChanges, OnInit, OnDestroy {
  // Input property to determine if the modal is in create or edit mode
  @Input({ required: true }) mode!: 'create' | 'edit';
  @Input() lesson: AdminLessonListItem | null = null;

  // Output event emitters for creating, updating, and closing the modal
  @Output() createLesson = new EventEmitter<CreateLessonPayload>();
  @Output() updateLesson = new EventEmitter<UpdateLessonPayload>();
  @Output() closeModal = new EventEmitter<void>();

  private fb = inject(FormBuilder);
  private adminLesson = inject(AdminLessonService);

  // Cursus options for the select dropdown (create only)
  cursusOptions: AdminCursusOption[] = [];
  loadingCursus = false;
  errorMessage: string | null = null;

  // Reactive form group for the lesson form
  lessonForm = this.fb.group({
    title: ['', [Validators.required, Validators.maxLength(255)]],
    price: [0, [Validators.required, Validators.min(0)]],
    content: ['', [Validators.required, Validators.minLength(10)]],
    cursusId: [null as number | null],
  });

  ngOnChanges(): void {
    // Reset errors when modal changes
    this.errorMessage = null;

    // Validators for cursusId depending on mode
    if (this.mode === 'create') {
      this.lessonForm.controls.cursusId.setValidators([Validators.required]);
      this.lessonForm.controls.cursusId.enable({ emitEvent: false });
    } else {
      this.lessonForm.controls.cursusId.clearValidators();
      this.lessonForm.controls.cursusId.disable({ emitEvent: false });
    }
    this.lessonForm.controls.cursusId.updateValueAndValidity({ emitEvent: false });

    // Patch form values based on mode and lesson data
    if (this.mode === 'edit' && this.lesson) {
      this.lessonForm.patchValue({
        title: this.lesson.title,
        price: this.lesson.price,
        content: this.lesson.content,
      }, { emitEvent: false });
    } else if (this.mode === 'create') {
      this.lessonForm.reset({
        title: '',
        price: 0,
        content: '',
        cursusId: null,
      }, { emitEvent: false });
    }
  }

  ngOnInit(): void {
    // Prevent scrolling behind the modal
    document.body.style.overflow = 'hidden';
    // Load cursus list only in create mode
    if (this.mode === 'create') {
      this.fetchCursusOptions();
    }
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

  // Close when clicking outside the modal content
  onBackdropMouseDown(_: MouseEvent): void {
    this.closeModal.emit();
  }

  private fetchCursusOptions(): void {
    this.loadingCursus = true;
    this.errorMessage = null;

    this.adminLesson.listCursusOptions()
      .pipe(finalize(() => (this.loadingCursus = false)))
      .subscribe({
        next: (items) => {
          this.cursusOptions = items ?? [];
        },
        error: (err) => {
          this.errorMessage = err?.error?.message ?? 'Erreur lors du chargement des cursus.';
        }
      });
  }

  // Method to handle form submission
  onSubmit(): void {
    if (this.lessonForm.invalid) {
      this.lessonForm.markAllAsTouched();
      return;
    }

    const title = this.lessonForm.value.title!.trim();
    const price = this.lessonForm.value.price as number;
    const content = this.lessonForm.value.content?.trim() as string;
    
    if (this.mode === 'create') {
      const cursusId = this.lessonForm.value.cursusId;

      if (!cursusId) {
        this.lessonForm.controls.cursusId.markAsTouched();
        this.errorMessage = 'Veuillez sélectionner un cursus.';
        return;
      }

      // Emit appropriate event based on mode
      this.createLesson.emit({
        title,
        price,
        content,
        cursusId,
      });
      return;
    }

    // Update payload for edit mode
    const payload: UpdateLessonPayload = {
      title,
      price,
      content,
    };
    this.updateLesson.emit(payload);
  }
}