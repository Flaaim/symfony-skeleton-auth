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
    changePassword: () => BASE_URL + `/v1/auth/user/password/change`,
    requestEmailChange: () => BASE_URL + `/v1/auth/email/change/request`,
    confirmEmailChange: () => BASE_URL + `/v1/auth/email/change/confirm`,
    socialLogin: () => BASE_URL + `/token`,
    attachNetwork: () => BASE_URL + `/v1/auth/network/attach`,
  },
  user: {
    profile: () => BASE_URL + `/v1/user/profile`,
  },
};
