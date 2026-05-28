"use server"

import { cookies } from "next/headers";

interface JoinData {
  email: string,
  password: string,
  confirm_password?: string
}

export default async function JoinAction(data: JoinData) {
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
