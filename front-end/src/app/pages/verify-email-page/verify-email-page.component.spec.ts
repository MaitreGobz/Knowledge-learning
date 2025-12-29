import { TestBed } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { VerifyEmailPageComponent } from './verify-email-page.component';

describe('VerifyEmailPageComponent', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VerifyEmailPageComponent, RouterTestingModule],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              queryParamMap: convertToParamMap({
                id: '1',
                expires: '9999999999',
                signature: 'abc',
                hash: 'def',
              }),
            },
          },
        },
      ],
    }).compileComponents();
  });

  it('should create', () => {
    const fixture = TestBed.createComponent(VerifyEmailPageComponent);
    expect(fixture.componentInstance).toBeTruthy();
  });
});