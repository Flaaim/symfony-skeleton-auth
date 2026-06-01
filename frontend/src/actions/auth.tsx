"use server";

import { JoinData, LoginData } from "@/interfaces/auth.interface";
import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { ActionResponse } from "@/interfaces/response.interface";

async function handleApiResponse(
  response: Response
): Promise<{ ok: boolean; data: any; error?: string }> {
  const text = await response.text();
  let data;

  try {
    data = text ? JSON.parse(text) : {};
  } catch (parseError) {
    console.error("Ошибка парсинга ответа API:", text);
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
  return { ok: true, data };
}
export async function JoinAction(data: JoinData): Promise<ActionResponse> {
  const { confirm_password, ...payload } = data;

  try {
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/v1/auth/join/request`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });
    const parsed = await handleApiResponse(response);
    if (!parsed.ok) {
      return { success: false, error: parsed.error };
    }
    return { success: true };
  } catch (error) {
    console.error("JoinAction Fetch error:", error);
    return { success: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function LoginAction(data: LoginData): Promise<ActionResponse> {
  const searchParams = new URLSearchParams({
    username: data.email,
    password: data.password,
    grant_type: "password",
    client_id: String(process.env.CLIENT_ID),
    client_secret: String(process.env.CLIENT_SECRET),
  });

  try {
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/token`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body: searchParams.toString(),
    });

    const parsed = await handleApiResponse(response);

    if (!parsed.ok) {
      return { success: false, error: parsed.error };
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
    return { success: true };
  } catch (error) {
    console.error("LoginAction Fetch error:", error);
    return { success: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function RefreshSessionAction(refreshToken: string) {
  try {
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/token`, {
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
    console.error("Auth API Error:", error);
    return null;
  }
}

export async function Logout() {
  const cookieStore = await cookies();
  cookieStore.delete("refresh_token");
  cookieStore.delete("access_token");

  redirect("/join/login");
}
