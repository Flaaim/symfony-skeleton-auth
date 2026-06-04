"use client";

import { z } from "zod";
import { useSearchParams } from "next/navigation";
import {Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle} from "@/components/ui/card";
import { JSX, Suspense, useEffect, useRef, useState } from "react";
import {Check, Loader2, XCircle} from "lucide-react";
import { joinConfirm } from "@/actions/auth";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import {ApiResponse} from "@/interfaces/response.interface";
const tokenSchema = z.uuid("Неверный формат токена");

const ConfirmEmailContent = (): JSX.Element => {
  const searchParams = useSearchParams();
  const [error, setError] = useState<string | null>(null);
  const [result, setResult] = useState<ApiResponse | null>(null);
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

      if (!parsed.success) {
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

  if(loading){
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
              <Loader2 className="h-10 w-10 animate-spin text-blue-600" />
            </div>
            <h1>Проверка данных...</h1>
          </CardTitle>
        </CardHeader>
        <CardContent className="flex flex-col items-center justify-center space-y-4 pt-6">
          <p className="text-sm text-muted-foreground">Подождите, мы проверяем ваш токен.</p>
        </CardContent>
      </Card>
    );
  }
  if(result && result.ok){
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader>
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <Check className="h-10 w-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Успех!</h1>
          </CardTitle>
        </CardHeader>
        <CardDescription className="flex flex-col items-center justify-center space-y-4 pt-6">
            Ваша почта успешно подтверждена!
        </CardDescription>
        <CardFooter>
          <Button variant="link" className="w-full">
            <Link className="link" href="/join/login">
              Вернуться на страницу входа
            </Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }
  if(error || (result && !result.ok)){
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-red-100 p-4">
            <XCircle className="h-10 w-10 text-red-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Что-то пошло не так.</h1>
          </CardTitle>
          <CardDescription className="text-base text-red-600">
            {error || result?.error || "Произошла неизвестная ошибка."}
          </CardDescription>
        </CardHeader>
        <CardFooter className="justify-center">
          <div>
            <span>Попробуйте обновить страницу. Если ничего не помогает, напишите сообщение в</span>{" "}
            <Link className="link" href="https://t.me/flaaim">
              поддержку.
            </Link>
          </div>
        </CardFooter>
      </Card>
    );
  }
};

export default function ConfirmEmail() {
  return (
    <Suspense fallback={<div className="flex justify-center p-8"><Loader2 className="h-8 w-8 animate-spin text-gray-400" /></div>}>
      <ConfirmEmailContent />
    </Suspense>
  );
}
