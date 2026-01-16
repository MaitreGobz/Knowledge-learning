export interface CursusPreview {
  id: number;
  title: string;
  description: string | null;
  price: number;
}

export interface ThemeCursusPreview {
  id: number;
  title: string;
  slug?: string;
  description?: string | null;
  cursus: CursusPreview[];
}