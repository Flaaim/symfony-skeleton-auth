"use client";

import { Controller, useForm } from "react-hook-form";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useState } from "react";
import { MailCheck } from "lucide-react";
import Link from "next/link";
import {passwordResetRequest} from "@/actions/auth";

const schema = z.object({
  email: z.email("Пожалуйста, введите корректный email адрес."),
});

type FormData = z.infer<typeof schema>;

export default function RequestResetPassword() {
  const [isSuccess, setSuccess] = useState(false);

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      email: "",
    },
  });
  async function onSubmit(values: FormData) {
    const result = await passwordResetRequest(values.email)
    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }
    setSuccess(true);
  }
  if (isSuccess) {
    return (
      <div className="flex h-screen items-center justify-center">
        <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
          <CardHeader className="space-y-4">
            <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
              <MailCheck className="h-10 w-10 text-green-600" />
            </div>
            <CardTitle className="text-2xl font-semibold tracking-tight">
              Проверьте вашу почту
            </CardTitle>
            <CardDescription className="text-base">
              Мы отправили письмо со ссылкой для сброса пароля на адрес
              <br />
              <strong className="text-foreground">{form.getValues("email")}</strong>.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-muted-foreground text-sm">
              Пожалуйста, перейдите по ссылке в письме, чтобы сбросить текущий пароль и установить
              новый.
            </p>
          </CardContent>
          <CardFooter>
            <Button variant="link" className="w-full">
              <Link className="link" href="/join/login">
                Вернуться на страницу входа
              </Link>
            </Button>
          </CardFooter>
        </Card>
      </div>
    );
  }
  return (
    <div className="flex h-screen items-center justify-center">
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <CardTitle className="text-2xl font-semibold tracking-tight">
            Восстановление пароля
          </CardTitle>
          <CardDescription>
            Введите в поле ниже email, который вы использовали при регистрации. На него будет
            направлена ссылка для восстановления пароля.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form id="reset-password-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
            <FieldGroup>
              <Controller
                name="email"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="login-email">Электронная почта</FieldLabel>
                    <Input
                      {...field}
                      id="login-email"
                      value={field.value}
                      placeholder="Email"
                      aria-invalid={fieldState.invalid}
                      autoComplete="email"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              ></Controller>
            </FieldGroup>
          </form>
        </CardContent>
        <CardFooter>
          <div className="flex flex-col">
            <div className="space-y-2 pt-2">
              {form.formState.errors.root && (
                <div className="text-destructive bg-destructive/10 rounded-md p-2 text-center text-sm font-medium">
                  {form.formState.errors.root.message}
                </div>
              )}
              <Button
                type="submit"
                form="reset-password-form"
                disabled={form.formState.isSubmitting}
                className="cursor-pointer py-2"
              >
                {form.formState.isSubmitting ? "Загрузка..." : "Отправить запрос"}
              </Button>
            </div>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
}
