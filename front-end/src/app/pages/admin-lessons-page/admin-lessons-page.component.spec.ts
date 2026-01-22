import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminLessonsPageComponent } from './admin-lessons-page.component';

describe('AdminLessonsPageComponent', () => {
  let component: AdminLessonsPageComponent;
  let fixture: ComponentFixture<AdminLessonsPageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminLessonsPageComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AdminLessonsPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
