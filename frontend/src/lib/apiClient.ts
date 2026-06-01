import { cookies, headers } from "next/headers";

export async function apiFetch(url: string, options: RequestInit = {}): Promise<Response> {
  const { headers: customHeaders, ...restOptions } = options;

  const headersList = await headers();
  const cookieStore = await cookies();

  const access_token = headersList.get("x-access-token") || cookieStore.get("access_token")?.value;

  const fetchHeaders = new Headers(customHeaders);

  if (access_token) {
    fetchHeaders.set("Authorization", `Bearer ${access_token}`);
  }

  return await fetch(`${process.env.INTERNAL_BACKEND_URL}${url}`, {
    ...restOptions,
    headers: Object.fromEntries(fetchHeaders.entries()),
  });
}
