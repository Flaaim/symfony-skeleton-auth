"use client";

import { JSX, Suspense, useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { z } from "zod";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Check, Loader2, Wrench, XCircle } from "lucide-react";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { passwordResetConfirm } from "@/actions/auth";

const tokenSchema = z.uuid("Неверный формат токена");

const schema = z
  .object({
    password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
    confirm_password: z
      .string()
      .min(8, "Пароль должен содержать минимум 8 символов.")
      .max(18, "Пароль должен содержать максимум 18 символов."),
  })
  .superRefine(({ confirm_password, password }, ctx) => {
    if (confirm_password !== password) {
      ctx.addIssue({
        code: "custom",
        message: "Пароли не совпадают",
        path: ["confirm_password"],
      });
    }
  });

type FormData = z.infer<typeof schema>;

const ResetPasswordFormContent = (): JSX.Element => {
  const searchParams = useSearchParams();
  const token = searchParams.get("token");

  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [isSuccess, setIsSuccess] = useState<boolean>(false);

  useEffect(() => {
    setLoading(true);
    setError(null);

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
    setLoading(false);
  }, [token]);

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      password: "",
      confirm_password: "",
    },
  });

  async function onSubmit(values: FormData) {
    if (!token) return;
    const result = await passwordResetConfirm(token, values.password);

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }
    setIsSuccess(true);
  }

  if (loading) {
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardContent className="flex flex-col items-center justify-center space-y-4 pt-6">
          <Loader2 className="h-10 w-10 animate-spin text-blue-600" />
          <p className="text-sm text-muted-foreground">Проверка безопасного соединения...</p>
        </CardContent>
      </Card>
    );
  }

  if (isSuccess) {
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <Check className="h-10 w-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">Успешно</CardTitle>
          <CardDescription className="text-base">
            Ваш пароль успешно изменен. Используйте его для входа на сайт.
          </CardDescription>
        </CardHeader>
        <CardFooter className="justify-center">
          <Button variant="outline">
            <Link href="/join/login">Вернуться на страницу входа</Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }
  if (error) {
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-red-100 p-4">
            <XCircle className="h-10 w-10 text-red-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Ошибка доступа</h1>
          </CardTitle>
          <CardDescription className="text-base text-red-600">{error}</CardDescription>
        </CardHeader>
        <CardFooter className="justify-center">
          <Button variant="outline">
            <Link href="/join/login">Вернуться на страницу входа</Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }
  return (
    <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
      <CardHeader className="space-y-4">
        <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
          <Wrench className="h-10 w-10 text-green-600" />
        </div>
        <CardTitle className="text-2xl font-semibold tracking-tight">
          <h1>Восстановление доступа</h1>
        </CardTitle>
        <CardDescription>
          Для того чтобы восстановить доступ к аккаунту, необходимо придумать новый надежный пароль
          и подтвердить его.
        </CardDescription>
      </CardHeader>
      <CardContent className="text-start">
        <form id="new-password-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
          <FieldGroup>
            <Controller
              name="password"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="new-password">Пароль</FieldLabel>
                  <Input
                    {...field}
                    id="new-password"
                    type="password"
                    value={field.value}
                    placeholder="Укажите пароль"
                    aria-invalid={fieldState.invalid}
                    autoComplete="current-password"
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />

            <Controller
              name="confirm_password"
              control={form.control}
              render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="join-confirm-password">Подтвердите пароль</FieldLabel>
                  <Input
                    {...field}
                    id="join-confirm-password"
                    type="password"
                    value={field.value}
                    placeholder="Подтвердите пароль"
                    aria-invalid={fieldState.invalid}
                    autoComplete="current-password"
                  />
                  {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                </Field>
              )}
            />
          </FieldGroup>
        </form>
      </CardContent>
      <CardFooter className="text-start">
        <div className="flex flex-col">
          <div className="space-y-4 pt-4">
            {form.formState.errors.root && (
              <div className="text-destructive bg-destructive/10 rounded-md p-2 text-center text-sm font-medium">
                {form.formState.errors.root.message}
              </div>
            )}
            <Button
              type="submit"
              form="new-password-form"
              disabled={form.formState.isSubmitting}
              className="cursor-pointer py-2"
            >
              {form.formState.isSubmitting ? "Загрузка..." : "Обновить пароль"}
            </Button>
          </div>
          <div className="space-y-4 pt-4">
            Вернуться на{" "}
            <Link className="link" href="/join/login">
              страницу входа
            </Link>
          </div>
        </div>
      </CardFooter>
    </Card>
  );
};

export default function ResetPasswordForm() {
  return (
    <Suspense
      fallback={
        <div className="flex justify-center p-8">
          <Loader2 className="h-8 w-8 animate-spin text-gray-400" />
        </div>
      }
    >
      <ResetPasswordFormContent />
    </Suspense>
  );
}
