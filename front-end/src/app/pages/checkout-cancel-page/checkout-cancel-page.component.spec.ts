import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CheckoutCancelPageComponent } from './checkout-cancel-page.component';

describe('CheckoutCancelPageComponent', () => {
  let component: CheckoutCancelPageComponent;
  let fixture: ComponentFixture<CheckoutCancelPageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CheckoutCancelPageComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CheckoutCancelPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
