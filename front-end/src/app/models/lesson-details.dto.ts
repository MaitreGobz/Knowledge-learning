export interface LessonDetailsDto {
    id: number;
    title: string;
    content: string;
    videoUrl?: string | null;
}