export interface ApiResponse<T = unknown> {
  ok: boolean;
  data?: T | null;
  error?: string;
}
