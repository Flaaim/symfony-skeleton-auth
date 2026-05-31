"use server"

import {JoinData, LoginData} from "@/interfaces/auth.interface";
import {cookies} from "next/headers";
import {redirect} from "next/navigation";
import {apiFetch} from "@/lib/apiClient";

export async function JoinAction(data: JoinData) {
  const { confirm_password, ...payload } = data;

  try {
    const response = await apiFetch('/v1/auth/join/request', {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
      },
      body: JSON.stringify(payload),
    })
    const responseText = await response.text();

    let result;
    try {
      result = responseText ? JSON.parse(responseText) : {};
    } catch (parseError) {
      console.error("Ошибка парсинга:", responseText);
      return { success: false, error: "Сервер вернул некорректный ответ." };
    }

    if(!response.ok){
      let errorMessage = "Произошла ошибка при регистрации.";

      if (response.status === 409 && result.message === "User already exists.") {
        errorMessage = "Пользователь с таким email уже зарегистрирован.";
      } else if (result.message) {
        errorMessage = result.message;
      }
      return { success: false, error: errorMessage };
    }

  return { success: true };

  }catch (Error) {
    console.error("Fetch error:", Error);
    return { success: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function LoginAction(data: LoginData){
  const searchParams = new URLSearchParams({
    username: data.email,
    password: data.password,
    grant_type: "password",
    client_id: String(process.env.CLIENT_ID),
    client_secret: String(process.env.CLIENT_SECRET)
  });

  try{
    const response = await apiFetch<void>('/token', {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Accept": "application/json",
      },
      body: searchParams.toString()
    })

    const responseText = await response.text();

    let result;
    try {
      result = responseText ? JSON.parse(responseText) : {};
    } catch (parseError) {
      console.error("Ошибка парсинга:", responseText);
      return { success: false, error: "Сервер вернул некорректный ответ." };
    }

    if(!response.ok){
      let errorMessage = "Произошла ошибка при авторизации.";
      if (result.error_description) {
        errorMessage = result.error_description;
      } else if (response.status === 401) {
        errorMessage = "Неверный логин или пароль.";
      }
      return { success: false, error: errorMessage };
    }
    if(response.ok && result.access_token) {
      const cookieStore = await cookies()
      cookieStore.set({
        name: 'access_token',
        value: String(result.access_token),
        httpOnly: true,
        path: '/',
        secure: process.env.NODE_ENV === 'production',
        maxAge: Number(result.expires_in),
      })
      cookieStore.set({
        name: 'refresh_token',
        value: String(result.refresh_token),
        httpOnly: true,
        path: '/',
        secure: process.env.NODE_ENV === 'production',
        maxAge: 2592000
      })

    }
    return { success: true };
  }catch (error) {
    console.error("Fetch error:", error);
    return { success: false, error: "Не удалось подключиться к серверу API" };
  }
}

export async function RefreshSessionAction(refreshToken: string) {
  try{
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/token`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
      },
      body: JSON.stringify({
        refresh_token: refreshToken,
        grant_type: "refresh_token",
        client_id: String(process.env.CLIENT_ID),
        client_secret: String(process.env.CLIENT_SECRET)
      })
    });

    if(!response.ok){
      return null;
    }
    return await response.json();
  }catch (error){
    console.error("Auth API Error:", error);
    return null;
  }

}

export async function Logout() {
  const cookieStore = await cookies();
  cookieStore.delete('refresh_token');
  cookieStore.delete('access_token');

  redirect("/join/login");
}
