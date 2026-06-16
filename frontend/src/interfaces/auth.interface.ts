export interface JoinData {
  email: string;
  password: string;
  confirm_password?: string;
}

export interface LoginData {
  email: string;
  password: string;
}
export interface ProfileDTO {
  id: string
  email: string,
  networks: []
}
