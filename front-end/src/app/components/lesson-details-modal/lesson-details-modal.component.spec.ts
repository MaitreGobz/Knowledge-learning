import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LessonDetailsModalComponent } from './lesson-details-modal.component';

describe('LessonDetailsModalComponent', () => {
  let component: LessonDetailsModalComponent;
  let fixture: ComponentFixture<LessonDetailsModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LessonDetailsModalComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LessonDetailsModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
