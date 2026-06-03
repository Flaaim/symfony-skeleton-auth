"use client";

import { z } from "zod";
import { Controller, useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { LoginAction } from "@/actions/auth";
import { useRouter } from "next/navigation";

const schema = z.object({
  email: z.email("Пожалуйста, введите корректный email адрес."),
  password: z
    .string()
    .min(8, "Пароль должен содержать минимум 8 символов.")
    .max(18, "Пароль должен содержать максимум 18 символов."),
});

type FormData = z.infer<typeof schema>;

export default function Login() {
  const router = useRouter();

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      email: "",
      password: "",
    },
  });

  async function onSubmit(values: FormData) {
    const result = await LoginAction(values);

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }

    router.push("/user/dashboard");
  }

  return (
    <div className="flex h-screen items-center justify-center">
      <Card className="mx-auto w-full max-w-md shadow-sm">
        <CardHeader className="space-y-2 text-center">
          <CardTitle className="text-2xl font-semibold tracking-tight">Вход в систему</CardTitle>
          <CardDescription>
            Войдите в свой аккаунт, чтобы продолжить работу и управлять вашими данными.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form id="login-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
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
              />
              <Controller
                name="password"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="login-password">Пароль</FieldLabel>
                    <Input
                      {...field}
                      id="login-password"
                      type="password"
                      value={field.value}
                      placeholder="Пароль"
                      aria-invalid={fieldState.invalid}
                      autoComplete="password"
                    />
                    <div className="items-rigth flex">
                      <Link
                        href="/join/reset/request"
                        className="ml-auto inline-block text-sm underline-offset-4 hover:underline"
                      >
                        Забыли свой пароль?
                      </Link>
                    </div>
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
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
                form="login-form"
                disabled={form.formState.isSubmitting}
                className="cursor-pointer py-2"
              >
                {form.formState.isSubmitting ? "Загрузка..." : "Войти"}
              </Button>
            </div>
            <div className="space-y-4 pt-4">
              Нет аккаунта?{" "}
              <Link className="link" href="/join/register">
                Зарегистрироваться
              </Link>
            </div>
          </div>
        </CardFooter>
      </Card>
    </div>
  );
}
