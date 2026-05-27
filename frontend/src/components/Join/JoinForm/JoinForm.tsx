"use client"

import {z} from "zod";
import {Controller, useForm} from "react-hook-form";
import {zodResolver} from "@hookform/resolvers/zod";
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";
import {Field, FieldError, FieldGroup, FieldLabel} from "@/components/ui/field";
import {Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle} from "@/components/ui/card";


const schema = z.object({
  email: z.email("Пожалуйста, введите корректный email адрес."),
  password: z.string().min(6, "Пароль должен содержать минимум 6 символов.").max(18, "Пароль должен содержать максимум 18 символов.")
})

type FormData = z.infer<typeof schema>

export default function JoinForm() {

  const form = useForm({
    mode: 'onBlur',
    resolver: zodResolver(schema),
    defaultValues: {
      email: "",
      password: ""
    }
  });

  function onSubmit(values: FormData) {
    console.log("Отправка на сервер:", values);

  }
  return (
    <Card className="w-full max-w-md mx-auto shadow-sm">
      <CardHeader className="text-center space-y-2">
        <CardTitle className="text-2xl font-semibold tracking-tight">Вход в систему</CardTitle>
          <CardDescription>
            Введите email и пароль для доступа к аккаунту
          </CardDescription>
      </CardHeader>
      <CardContent>
        <form id="login-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
          <FieldGroup>
            <Controller
              name="email"
              control={form.control}
              render={({field, fieldState}) => (
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
                  {fieldState.invalid && (
                    <FieldError errors={[fieldState.error]}/>
                  )}
                </Field>
              )}
            />
            <Controller
              name="password"
              control={form.control}
              render={({field, fieldState}) => (
                <Field data-invalid={fieldState.invalid}>
                  <FieldLabel htmlFor="login-password">Пароль</FieldLabel>
                  <Input
                    {...field}
                    id="login-password"
                    type="password"
                    value={field.value}
                    placeholder="Пароль"
                    aria-invalid={fieldState.invalid}
                    autoComplete="current-password"
                  />
                  {fieldState.invalid && (
                    <FieldError errors={[fieldState.error]}/>
                  )}
                </Field>
              )}
            />
          </FieldGroup>
        </form>
      </CardContent>
      <CardFooter>
        <Button
          type="submit"
          form="login-form"
          className="w-full"
          disabled={form.formState.isSubmitting}
        >
          {form.formState.isSubmitting ? "Вход..." : "Войти"}
        </Button>
      </CardFooter>
    </Card>

  );
}
