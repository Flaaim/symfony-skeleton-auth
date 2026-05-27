"use client"

import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";

import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";

const loginSchema = z.object({
  email: z.string().email({
    message: "Пожалуйста, введите корректный email адрес.",
  }),
  password: z.string().min(6, {
    message: "Пароль должен содержать минимум 6 символов.",
  }),
});

type LoginFormValues = z.infer<typeof loginSchema>;

export function JoinForm() {
  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: "",
      password: "",
    },
  })

}
