export interface LessonPreview {
    id: number;
    title: string;
    price: number;
    position: number;
}

export interface CursusLessonPreview {
    id: number;
    title: string;
    description: string | null;
    price: number;
    lessons: LessonPreview[];
}