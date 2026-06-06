const isServer = typeof window === "undefined";
const BASE_URL = isServer
  ? process.env.INTERNAL_BACKEND_URL || process.env.NEXT_PUBLIC_BACKEND_URL || "http://api"
  : process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8081";

export const API = {
  auth: {
    joinByEmail: () => BASE_URL + `/v1/auth/join/request`,
    login: () => BASE_URL + `/token`,
    refreshToken: () => BASE_URL + `/token`,
    revokeToken: () => BASE_URL + `/v1/auth/token/revoke`,
    joinConfirm: () => BASE_URL + `/v1/auth/join/confirm`,
    passwordResetRequest: () => BASE_URL + `/v1/auth/password/reset/request`,
    passwordResetConfirm: () => BASE_URL + `/v1/auth/password/reset`,
    requestEmailChange: () => BASE_URL + `/v1/auth/email/change/request`,
    confirmEmailChange: () => BASE_URL + `/v1/auth/email/change/confirm`,
    yandexLogin: () => BASE_URL + `/token`
  },
  user: {
    profile: () => BASE_URL + `/v1/user/profile`
  }
};
