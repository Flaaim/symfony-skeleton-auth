export interface JoinData {
  email: string;
  password: string;
  confirm_password?: string;
}

export interface LoginData {
  email: string;
  password: string;
}

interface TokenResponse {
  token_type: string;
  expires_in: number;
  access_token: string;
  refresh_token: string;
}
