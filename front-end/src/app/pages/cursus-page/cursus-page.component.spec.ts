import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CursusPageComponent } from './cursus-page.component';

describe('CursusPageComponent', () => {
  let component: CursusPageComponent;
  let fixture: ComponentFixture<CursusPageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CursusPageComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CursusPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
