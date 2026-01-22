import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminLessonListItem } from '../../models/admin-lessons.model';

@Component({
  selector: 'app-lessons-table',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './lessons-table.component.html',
  styleUrl: './lessons-table.component.scss'
})
export class LessonsTableComponent {

  // Input property to receive the list of lessons to display in the table
  @Input({ required: true }) lessons: AdminLessonListItem[] = [];

  // Output event emitter to notify when a lesson is to be edited
  @Output() editLesson = new EventEmitter<AdminLessonListItem>();
  @Output() desactivateLesson = new EventEmitter<AdminLessonListItem>();
  @Output() viewLesson = new EventEmitter<AdminLessonListItem>();
}
