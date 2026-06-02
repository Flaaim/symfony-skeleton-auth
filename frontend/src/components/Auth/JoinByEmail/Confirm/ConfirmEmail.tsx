"use client";

import { z } from "zod";
import { useSearchParams } from "next/navigation";
import {
  Card,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { JSX, Suspense, useEffect, useRef, useState } from "react";
import { CheckCircle2, Loader2, MailOpen, XCircle } from "lucide-react";
import { joinConfirm } from "@/actions/auth";
import { ActionResponse } from "@/interfaces/response.interface";
import Link from "next/link";
import { Button } from "@/components/ui/button";
const tokenSchema = z.uuid("Неверный формат токена");

const ConfirmEmailContent = (): JSX.Element => {
  const searchParams = useSearchParams();
  const [error, setError] = useState<string | null>(null);
  const [result, setResult] = useState<ActionResponse | null>(null);
  const [loading, setLoading] = useState(true);

  const hasFetched = useRef(false);

  const token = searchParams.get("token");

  useEffect(() => {
    const initializeConfirmToken = async () => {
      if (hasFetched.current) return;
      hasFetched.current = true;

      setLoading(true);
      setError(null);
      setResult(null);

      if (!token) {
        setError("Токен отсутствует в ссылке.");
        setLoading(false);
        return;
      }

      const parsed = tokenSchema.safeParse(token);

      if (!token || !parsed.success) {
        setError(parsed.success ? "Токен отсутствует" : parsed.error.issues[0].message);
        setLoading(false);
        return;
      }

      const apiResult = await joinConfirm(token);
      setResult(apiResult);
      setLoading(false);
    };

    initializeConfirmToken();
  }, [token]);
  return (
    <Card className="w-full max-w-md mx-auto shadow-sm text-center py-6">
      <CardHeader className="space-y-4">
        <div
          className={`mx-auto p-4 rounded-full w-fit ${
            loading
              ? "bg-blue-100 text-blue-600"
              : error || (result && !result.success)
                ? "bg-red-100 text-red-600"
                : "bg-green-100 text-green-600"
          }`}
        >
          {loading && <Loader2 className="w-10 h-10 animate-spin" />}
          {(error || (result && !result.success)) && <XCircle className="w-10 h-10" />}
          {result?.success && <CheckCircle2 className="w-10 h-10" />}
        </div>
        <CardTitle className="text-2xl font-semibold tracking-tight">
          {loading ? "Проверка данных..." : "Подтверждение почты"}
        </CardTitle>
        <CardDescription>
          {loading && "Подождите, мы проверяем ваш токен."}
          {!loading && result?.success && "Ваша почта успешно подтверждена!"}
          {!loading && (error || !result?.success) && "Что-то пошло не так."}
        </CardDescription>
      </CardHeader>
      <CardFooter>
        {!loading && result?.success && (
          <Button variant="link" className="w-full">
            <Link className="link" href="/join/login">
              Вернуться на страницу входа
            </Link>
          </Button>
        )}
        {!loading && (error || !result?.success) && (
          <div>
            <span>Попробуйте обновить страницу. Если ничего не помогает, напишите сообщение в</span>{" "}
            <Link className="link" href="https://t.me/flaaim">
              поддержку.
            </Link>
          </div>
        )}
      </CardFooter>
    </Card>
  );
};

export default function ConfirmEmail() {
  return (
    <Suspense>
      <ConfirmEmailContent />
    </Suspense>
  );
}
