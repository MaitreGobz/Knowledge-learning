import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-pagination',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './pagination.component.html',
  styleUrl: './pagination.component.scss'
})
export class PaginationComponent {
  // Input properties to receive the current page and total pages
  @Input({ required: true}) page!: number;
  @Input({ required: true }) totalPages!: number;
  
  // Output event emitter to notify when the page changes
  @Output() pageChange = new EventEmitter<number>();
}
