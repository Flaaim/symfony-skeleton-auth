"use server";

import { JoinData, LoginData } from "@/interfaces/auth.interface";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { ApiResponse } from "@/interfaces/response.interface";
import { API } from "@/app/api";
import {apiFetch} from "@/lib/apiClient";

interface TokenResponseData {
  access_token: string;
  refresh_token: string;
  expires_in: number;
  token_type: string;
}
async function handleApiResponse(response: Response): Promise<ApiResponse<T>> {
  const text = await response.text();
  let data;

  try {
    data = text ? JSON.parse(text) : {};
  } catch (parseError) {
    console.error("Ошибка парсинга ответа API:", parseError);
    return { ok: false, data: null, error: "Сервер вернул некорректный ответ." };
  }

  if (!response.ok) {
    let errorMessage =
      data.error_description || data.message || "Произошла ошибка при выполнении запроса.";
    if (response.status === 409) {
      errorMessage = data.message;
    } else if (response.status === 401 && !data.error_description) {
      errorMessage = "Ошибка авторизации";
    }
    return { ok: false, data, error: errorMessage };
  }
  return { ok: true, data: data as T };
}
export async function JoinAction(data: JoinData): Promise<ApiResponse> {
  try {
    const response = await fetch(API.auth.joinByEmail(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        email: data.email,
        password: data.password,
      }),
    });
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    return { ok: true };
  } catch (error) {
    console.error("JoinAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function LoginAction(data: LoginData): Promise<ApiResponse> {
  const searchParams = new URLSearchParams({
    username: data.email,
    password: data.password,
    grant_type: "password",
    client_id: String(process.env.CLIENT_ID),
    client_secret: String(process.env.CLIENT_SECRET),
  });

  try {
    const response = await fetch(API.auth.login(), {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body: searchParams.toString(),
    });

    const parsed = await handleApiResponse<TokenResponseData>(response);

    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }

    if (parsed.data.access_token) {
      const cookieStore = await cookies();
      cookieStore.set({
        name: "access_token",
        value: String(parsed.data.access_token),
        httpOnly: true,
        path: "/",
        secure: process.env.NODE_ENV === "production",
        maxAge: Number(parsed.data.expires_in),
      });
      cookieStore.set({
        name: "refresh_token",
        value: String(parsed.data.refresh_token),
        httpOnly: true,
        path: "/",
        secure: process.env.NODE_ENV === "production",
        maxAge: 2592000,
      });
    }
    return { ok: true };
  } catch (error) {
    console.error("LoginAction Fetch error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function RefreshSessionAction(
  refreshToken: string
): Promise<TokenResponseData | null> {
  try {
    const response = await fetch(API.auth.refreshToken(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        refresh_token: refreshToken,
        grant_type: "refresh_token",
        client_id: String(process.env.CLIENT_ID),
        client_secret: String(process.env.CLIENT_SECRET),
      }),
    });

    if (!response.ok) {
      return null;
    }
    return await response.json();
  } catch (error) {
    console.error("RefreshSessionAction API Error:", error);
    return null;
  }
}

export async function Logout(): Promise<never> {
  const cookieStore = await cookies();
  const refreshToken = cookieStore.get("refresh_token")?.value;

  if (refreshToken) {
    try {
      await fetch(`${process.env.INTERNAL_BACKEND_URL}/v1/auth/token/revoke`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ token: refreshToken }),
      });
    } catch (error) {
      console.error("Revoke API Error:", error);
    }
  }

  cookieStore.delete("refresh_token");
  cookieStore.delete("access_token");

  redirect("/join/login");
}

export async function joinConfirm(token: string): Promise<ApiResponse> {
  try {
    const response = await fetch(API.auth.joinConfirm(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ token: token }),
    });
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    return { ok: true };
  } catch (error) {
    console.error("Join confirm token request error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function passwordResetRequest(email: string): Promise<ApiResponse> {
  try {
    const response = await fetch(API.auth.passwordResetRequest(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email: email }),
    });
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    return { ok: true };
  } catch (error) {
    console.error("Join confirm token request error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function passwordResetConfirm(token: string, password: string): Promise<ApiResponse> {
  try {
    const response = await fetch(API.auth.passwordResetConfirm(), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ token: token, password: password }),
    });
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { ok: false, error: parsed.error };
    }
    return { ok: true };
  } catch (error) {
    console.error("Join confirm password reset error:", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }
}

export async function fetchUser(): Promise<ApiResponse> {
  try{
    const response = await apiFetch(`/v1/user/profile`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      }
    });
    const parsed = await handleApiResponse(response);
    if(!parsed.ok){
      return { ok: false, error: parsed.error };
    }
    return { ok: true, data: parsed.data }
  }catch (error){
    console.error("Get user profile error", error);
    return { ok: false, error: "Не удалось подключиться к серверу API." };
  }


}
