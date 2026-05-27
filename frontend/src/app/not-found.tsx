import Link from "next/link";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Страница не найдена",
  description: "Запрашиваемая страница не может быть найдена...",
};

export default function NotFound() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-gray-100">
      <h1 className="text-4xl font-extrabold text-gray-600 sm:text-5xl">Страница не найдена</h1>
      <p className="mt-2 text-base text-gray-500">
        Запрашиваемая страница не может быть найдена...
      </p>
      <Link href="/" className="text-decoration-underline mt-2 text-base">
        Вернуться на главную
      </Link>
    </div>
  );
}
