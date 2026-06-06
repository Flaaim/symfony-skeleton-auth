"use client"


import {Suspense, useEffect, useRef, useState} from "react";
import {Loader2} from "lucide-react";
import {useRouter, useSearchParams} from "next/navigation";
import {googleLoginAction} from "@/actions/auth";


const GoogleCallbackContent = () => {
  const searchParams = useSearchParams();
  const router = useRouter();
  const [error, setError] = useState<string | null>(null);

  const hasFetched = useRef(false);
  const code = searchParams.get("code");

  useEffect(() => {
    const processGoogleAuth = async () => {
      if (hasFetched.current) return;
      hasFetched.current = true;

      if(!code){
        router.replace("/join/login?error=Отмена+авторизации+Google");
        return;
      }

      const result = await googleLoginAction(code);
      console.log(result)
      if(result.ok){
        router.replace("/user/dashboard");
      }else {
        setError(result.error || "Неизвестная ошибка авторизации");
        setTimeout(() => {
          router.replace("/join/login");
        }, 3000)
      }
    }
    processGoogleAuth()
  }, [code, router]);

  if(error){
    return (
      <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
        <div className="mx-auto mb-4 w-fit rounded-full bg-red-100 p-4">
          <Loader2 className="h-8 w-8 text-red-600" />
        </div>
        <h1 className="text-xl font-semibold text-red-600 mb-2">Ошибка авторизации</h1>
        <p className="text-muted-foreground">{error}</p>
        <p className="text-sm text-muted-foreground mt-4">Возвращаем на страницу входа...</p>
      </div>
    );
  }

  return (
    <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
      <div className="mx-auto mb-4 w-fit rounded-full bg-blue-100 p-4">
        <Loader2 className="h-10 w-10 animate-spin text-blue-600" />
      </div>
      <h1 className="text-2xl font-semibold tracking-tight mb-2">
        Связываемся с Google...
      </h1>
      <p className="text-muted-foreground">
        Пожалуйста, подождите, мы настраиваем ваш профиль.
      </p>
    </div>
  );
}


export default function GoogleCallbackPage(){

  return (
    <Suspense fallback={
      <div className="flex min-h-[60vh] items-center justify-center">
        <Loader2 className="h-10 w-10 animate-spin text-blue-600" />
      </div>
    }>
      <GoogleCallbackContent />
    </Suspense>
    )
}
