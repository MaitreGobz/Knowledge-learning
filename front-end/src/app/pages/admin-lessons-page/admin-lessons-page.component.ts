import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { finalize } from 'rxjs';
import { AdminLessonService } from '../../services/admin-lesson.service';
import { AdminLessonListItem, CreateLessonPayload, UpdateLessonPayload } from '../../models/admin-lessons.model';
import { LessonFormModalComponent } from '../../components/lesson-form-modal/lesson-form-modal.component';
import { LessonDetailsModalComponent } from '../../components/lesson-details-modal/lesson-details-modal.component';
import { LessonsTableComponent } from '../../components/lessons-table/lessons-table.component';
import { PaginationComponent } from '../../components/pagination/pagination.component';

@Component({
  selector: 'app-admin-lessons-page',
  standalone: true,
  imports: [CommonModule, LessonFormModalComponent, LessonDetailsModalComponent, LessonsTableComponent, PaginationComponent],
  templateUrl: './admin-lessons-page.component.html',
  styleUrl: './admin-lessons-page.component.scss'
})
export class AdminLessonsPageComponent implements OnInit {
  //State signals
  loading = signal(false);
  errorMessage = signal<string | null>(null);
  lessons = signal<AdminLessonListItem[]>([]);
  meta = signal<{ page: number; limit: number; totalItems: number; totalPages: number } | null>(null)
  page = signal(1);
  limit = signal(20);

  //Modal state signals
  modalOpen = signal(false);
  detailsModalOpen = signal(false);
  selectedLessonForDetails = signal<AdminLessonListItem | null>(null);
  modalMode = signal<'create' | 'edit'>('create');
  editingLesson = signal<AdminLessonListItem | null>(null);

  private adminLesson = inject(AdminLessonService);

  // On component initialization, load the list of lessons
  ngOnInit(): void {
    this.loadLessons();
  }

  loadLessons(): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.adminLesson
      .listLessons({
        page: this.page(),
        limit: this.limit()
      })
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: (res) => {
          this.lessons.set(res.items);
          this.meta.set(res.meta);
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors du chargement.');
        }
      });
  }

  //Handle lesson desactivation action
  onDesactivate(lesson: AdminLessonListItem): void {
    const ok = window.confirm(`Êtes-vous sûr de vouloir désactiver la leçon "${lesson.title}" ?`);
    if (!ok) {
      return;
    }

    this.loading.set(true);
    this.adminLesson
      .desactivateLesson(lesson.id)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.loadLessons();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la désactivation.');
        }
      });
  }

  //Handle page change action
  onPageChange(page: number): void {
    const totalPages = this.meta()?.totalPages ?? 1;
    const newPage = Math.min(Math.max(1, page), totalPages);
    this.page.set(newPage);
    this.loadLessons();
  }

  //Handle modal open actions to create lessons
  openCreate(): void {
    this.modalMode.set('create');
    this.editingLesson.set(null);
    this.modalOpen.set(true);
  }

  //Handle modal open actions to edit lessons
  openEdit(lesson: AdminLessonListItem): void {
    this.modalMode.set('edit');
    this.editingLesson.set(lesson);
    this.modalOpen.set(true);
  }


  // Handle details modal open action
  openDetails(lesson: AdminLessonListItem): void {
    this.selectedLessonForDetails.set(lesson);
    this.detailsModalOpen.set(true);
  }

  // Handle modal close actions
  closeDetails(): void {
    this.detailsModalOpen.set(false);
    this.selectedLessonForDetails.set(null);
  }

  closeModal(): void {
    this.modalOpen.set(false);
  }

  // Handle lesson creation action
  onCreate(payload: CreateLessonPayload): void {
    this.loading.set(true);
    this.adminLesson
      .createLesson(payload)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.closeModal();
          this.loadLessons();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la création.');
        }
      });
  }

  // Handle user update action
  onUpdate(payload: UpdateLessonPayload): void {
    const lesson = this.editingLesson();
    if (!lesson) return;

    this.loading.set(true);
    this.adminLesson
      .updateLesson(lesson.id, payload)
      .pipe(finalize(() => this.loading.set(false)))
      .subscribe({
        next: () => {
          this.closeModal();
          this.loadLessons();
        },
        error: (err) => {
          this.errorMessage.set(err?.error?.message ?? 'Erreur lors de la mise à jour.');
        }
      });
  }
}