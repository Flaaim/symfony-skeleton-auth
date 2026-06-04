"use server";

import { apiFetch } from "@/lib/apiClient";

export async function fetchEmail() {
  const response = await apiFetch("/v1/user/profile");

  if (!response.ok) {
    let errorMessage = "Произошла ошибка при запросе";
    try {
      const errorData = await response.json();
      if (errorData && errorData.message) {
        errorMessage = errorData.message;
      }
    } catch {}
    throw new Error(errorMessage);
  }

  return await response.json();
}
