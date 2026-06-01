"use client";

import { z } from "zod";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import Link from "next/link";
import { useState } from "react";
import { MailCheck } from "lucide-react";
import { JoinAction } from "@/actions/auth";

const schema = z
  .object({
    email: z.email("Пожалуйста, введите корректный email адрес."),
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

export default function JoinByEmail() {
  const [isSuccess, setIsSuccess] = useState(false);

  const form = useForm({
    mode: "onBlur",
    resolver: zodResolver(schema),
    defaultValues: {
      email: "",
      password: "",
      confirm_password: "",
    },
  });

  async function onSubmit(values: FormData) {
    const result = await JoinAction(values);

    if (!result.success) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }
    setIsSuccess(true);
  }
  if (isSuccess) {
    return (
      <Card className="w-full max-w-md mx-auto shadow-sm text-center py-6">
        <CardHeader className="space-y-4">
          <div className="mx-auto bg-green-100 p-4 rounded-full w-fit">
            <MailCheck className="w-10 h-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            Проверьте вашу почту
          </CardTitle>
          <CardDescription className="text-base">
            Мы отправили письмо со ссылкой для подтверждения на адрес
            <br />
            <strong className="text-foreground">{form.getValues("email")}</strong>.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Пожалуйста, перейдите по ссылке в письме, чтобы завершить регистрацию и активировать
            аккаунт.
          </p>
        </CardContent>
        <CardFooter>
          <Button variant="link" className="w-full">
            <Link href="/join/login">Вернуться на страницу входа</Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }
  return (
    <div className="flex h-screen items-center justify-center">
      <Card className="w-full max-w-md mx-auto shadow-sm">
        <CardHeader className="text-center space-y-2">
          <CardTitle className="text-2xl font-semibold tracking-tight">
            Регистрация в системе
          </CardTitle>
          <CardDescription>
            Зарегистрируйтесь, чтобы получить полный доступ к функциям системы и управлять вашими
            данными.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form id="join-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
            <FieldGroup>
              <Controller
                name="email"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="join-email">Электронная почта</FieldLabel>
                    <Input
                      {...field}
                      id="join-email"
                      value={field.value}
                      placeholder="Email"
                      aria-invalid={fieldState.invalid}
                      autoComplete="email"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
              <Controller
                name="password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="join-password">Пароль</FieldLabel>
                    <Input
                      {...field}
                      id="join-password"
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
        <CardFooter>
          <div className="flex flex-col">
            <div className="pt-4 space-y-4">
              {form.formState.errors.root && (
                <div className="text-sm font-medium text-destructive text-center bg-destructive/10 p-2 rounded-md">
                  {form.formState.errors.root.message}
                </div>
              )}
              <Button
                type="submit"
                form="join-form"
                disabled={form.formState.isSubmitting}
                className="py-2 cursor-pointer"
              >
                {form.formState.isSubmitting ? "Загрузка..." : "Присоединиться"}
              </Button>
            </div>

            <div className="pt-4 space-y-4">
              Есть аккаунт?{" "}
              <Link className="link" href="/join/login">
                Войти в систему
              </Link>
            </div>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
}
