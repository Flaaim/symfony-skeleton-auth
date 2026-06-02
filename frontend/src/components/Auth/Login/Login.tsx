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

    if (!result.success) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }

    router.push("/user/dashboard");
  }

  return (
    <div className="flex h-screen items-center justify-center">
      <Card className="w-full max-w-md mx-auto shadow-sm">
        <CardHeader className="text-center space-y-2">
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
                    <div className="flex items-rigth">
                      <Link
                        href="/join/reset"
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
            <div className="pt-2 space-y-2">
              {form.formState.errors.root && (
                <div className="text-sm font-medium text-destructive text-center bg-destructive/10 p-2 rounded-md">
                  {form.formState.errors.root.message}
                </div>
              )}
              <Button
                type="submit"
                form="login-form"
                disabled={form.formState.isSubmitting}
                className="py-2 cursor-pointer"
              >
                {form.formState.isSubmitting ? "Загрузка..." : "Войти"}
              </Button>
            </div>
            <div className="pt-4 space-y-4">
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
