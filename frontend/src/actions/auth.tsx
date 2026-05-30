"use server"

import { JoinData, LoginData } from "@/interfaces/auth.interface";
import {cookies} from "next/headers";

export async function JoinAction(data: JoinData) {
  const { confirm_password, ...payload } = data;

  try {
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/v1/auth/join/request`, {
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
    grant_type: process.env.GRANT_TYPE,
    client_id: process.env.CLIENT_ID,
    client_secret: process.env.CLIENT_SECRET
  });

  try{
    const response = await fetch(`${process.env.INTERNAL_BACKEND_URL}/token`, {
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
    }
    return { success: true };
  }catch (error) {
    console.error("Fetch error:", error);
    return { success: false, error: "Не удалось подключиться к серверу API" };
  }
}
