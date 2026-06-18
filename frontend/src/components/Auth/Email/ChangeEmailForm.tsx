"use client";

import { z } from "zod";
import { ProfileDTO } from "@/interfaces/auth.interface";
import { useState } from "react";
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
import { ArrowLeft, Mail, MailCheck } from "lucide-react";
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import Link from "next/link";
import { requestEmailChange } from "@/actions/auth";

const schema = z.object({
  email: z.email("Пожалуйста, введите корректный email адрес."),
});

type FormData = z.infer<typeof schema>;
interface RequestChangeEmailProps {
  profile: ProfileDTO;
}

export default function RequestChangeEmail({ profile }: RequestChangeEmailProps) {
  const [isSuccess, setIsSuccess] = useState(false);

  const form = useForm({
    mode: "onSubmit",
    resolver: zodResolver(schema),
    defaultValues: {
      email: "",
    },
  });
  async function onSubmit(values: FormData) {
    const result = await requestEmailChange(values.email);

    if (!result.ok) {
      form.setError("root", { type: "server", message: result.error });
      return;
    }

    setIsSuccess(true);
  }
  if (isSuccess) {
    return (
      <Card className="mx-auto w-full max-w-md py-6 text-center shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-green-100 p-4">
            <MailCheck className="h-10 w-10 text-green-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight">
            <h1>Письмо отправлено</h1>
          </CardTitle>
          <CardDescription className="text-base">
            Мы отправили письмо со ссылкой для подтверждения на адрес
            <br />
            <strong className="text-foreground">{form.getValues("email")}</strong>.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-muted-foreground text-sm">
            Пожалуйста, перейдите по ссылке в письме, чтобы завершить привязку нового адреса к
            вашему аккаунту.
          </p>
        </CardContent>
        <CardFooter>
          <Button variant="link" className="w-full">
            <Link className="link" href="/user/profile">
              Вернуться в профиль
            </Link>
          </Button>
        </CardFooter>
      </Card>
    );
  }

  return (
    <div className="mx-auto max-w-md p-4 md:p-8 pt-12">
      <div className="mb-6">
        <Button
          variant="ghost"
          size="sm"
          className="pl-0 text-muted-foreground hover:bg-transparent hover:text-gray-900"
        >
          <Link href="/user/profile" className="inline-flex items-center">
            <ArrowLeft className="mr-2 h-4 w-4" />
            <span>Назад в профиль</span>
          </Link>
        </Button>
      </div>

      <Card className="shadow-sm">
        <CardHeader className="space-y-4">
          <div className="mx-auto w-fit rounded-full bg-blue-100 p-4">
            <Mail className="h-10 w-10 text-blue-600" />
          </div>
          <CardTitle className="text-2xl font-semibold tracking-tight text-center">
            Изменение Email
          </CardTitle>
          <CardDescription className="text-center">
            Введите новый адрес электронной почты. Мы отправим на него письмо с ссылкой для
            подтверждения.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form id="change-email-form" onSubmit={form.handleSubmit(onSubmit)} method="POST">
            <FieldGroup>
              <Controller
                name="email"
                control={form.control}
                render={({ field, fieldState }) => (
                  <Field data-invalid={fieldState.invalid}>
                    <FieldLabel htmlFor="new-email">Новый Email</FieldLabel>
                    <Input
                      {...field}
                      id="new-email"
                      type="email"
                      placeholder="name@example.com"
                      aria-invalid={fieldState.invalid}
                      autoComplete="email"
                    />
                    {fieldState.invalid && <FieldError errors={[fieldState.error]} />}
                  </Field>
                )}
              />
            </FieldGroup>
          </form>
        </CardContent>

        <CardFooter className="flex-col">
          {form.formState.errors.root && (
            <div className="w-full mb-4 text-destructive bg-destructive/10 rounded-md p-3 text-center text-sm font-medium">
              {form.formState.errors.root.message}
            </div>
          )}
          <Button
            type="submit"
            form="change-email-form"
            disabled={form.formState.isSubmitting}
            className="w-full cursor-pointer"
          >
            {form.formState.isSubmitting ? "Отправка..." : "Продолжить"}
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
}
