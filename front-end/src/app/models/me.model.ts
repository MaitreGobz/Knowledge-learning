export interface MeResponse {
  authenticated: boolean;
  user?: {
    email?: string | null;
    roles?: string[];
  };
}
