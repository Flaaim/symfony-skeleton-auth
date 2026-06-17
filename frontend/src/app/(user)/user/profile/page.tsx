import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/components/ui/card";
import {CheckCircle2, KeyRound, Link2, Mail, Shield, UserIcon} from "lucide-react";
import {Button} from "@base-ui/react";
import {fetchUser} from "@/actions/auth";
import {redirect} from "next/navigation";
import Link from "next/link";

export default async function ProfilePage(){

  let profile;

  try {
    profile = await fetchUser();
  } catch (error) {
    console.error("Ошибка авторизации в лейауте, перенаправление...", error);
    redirect('/join/login');
  }

  const getYandexAuthUrl = (isAttach = false) => {
    const rootUrl = "https://oauth.yandex.ru/authorize";

    const redirectUri = isAttach ? process.env.NEXT_PUBLIC_YANDEX_ATTACH_REDIRECT_URI : process.env.NEXT_PUBLIC_YANDEX_REDIRECT_URI

    const options = {
      response_type: "code",
      client_id: process.env.NEXT_PUBLIC_YANDEX_CLIENT_ID as string,
      redirect_uri: redirectUri as string,
    };
    const qs = new URLSearchParams(options);
    return `${rootUrl}?${qs.toString()}`;
  }
  const getGoogleAuthUrl = (isAttach = false) => {
    const rootUrl = "https://accounts.google.com/o/oauth2/v2/auth"

    const redirectUri = isAttach ?  process.env.NEXT_PUBLIC_GOOGLE_ATTACH_REDIRECT_URI : process.env.NEXT_PUBLIC_GOOGLE_REDIRECT_URI

    const options = {
      response_type: "code",
      scope: "email",
      client_id: process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID,
      redirect_uri: redirectUri as string
    }
    const qs = new URLSearchParams(options);
    return `${rootUrl}?${qs.toString()}`;
  }
  const attachedNetworks: string[] = profile.networks || [];
  const isYandexAttached = attachedNetworks.some(net => net.network === "yandex");
  const isGoogleAttached = attachedNetworks.some(net => net.network === "google");

  return (
    <div className="mx-auto max-w-4xl space-y-6 p-4 md:p-8">
      <div>
        <h1 className="text-3xl font-bold tracking-tight">Настройки профиля</h1>
        <p className="text-muted-foreground text-sm mt-2">
          Управляйте своими личными данными и настройками безопасности.
        </p>
      </div>
      <div className="grid gap-6 md:grid-cols-2">
        <Card className="shadow-sm">
          <CardHeader>
            <div className="flex items-center gap-2 mb-1">
              <UserIcon className="h-5 w-5 text-blue-600" />
              <CardTitle className="text-xl">Личные данные</CardTitle>
            </div>
            <CardDescription>Основная информация о вашем аккаунте.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-1">
              <p className="text-sm font-medium leading-none text-muted-foreground">ID пользователя</p>
              <p className="text-sm font-mono text-gray-600 bg-gray-50 p-2 rounded-md w-fit">
                {profile.id}
              </p>
            </div>

            <div className="space-y-1">
              <p className="text-sm font-medium leading-none text-muted-foreground">Имя и Фамилия</p>
              <p className="text-base font-medium">
                {profile.name || "Не указано"}
              </p>
            </div>
            <div className="pt-2">
              <Button variant="outline" size="sm" disabled>
                Редактировать профиль
              </Button>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <div className="flex items-center gap-2 mb-1">
              <Shield className="h-5 w-5 text-green-600" />
              <CardTitle className="text-xl">Безопасность</CardTitle>
            </div>
            <CardDescription>Управление email-адресом и паролем.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <Mail className="h-4 w-4 text-muted-foreground" />
                  <p className="text-sm font-medium leading-none text-muted-foreground">Email адрес</p>
                </div>
                <p className="text-base font-medium pl-6">{profile.email}</p>
              </div>
              <Button variant="secondary" size="sm">
                <Link href="/user/profile/change-email">
                  Изменить email
                </Link>
              </Button>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <KeyRound className="h-4 w-4 text-muted-foreground" />
                  <p className="text-sm font-medium leading-none text-muted-foreground">Пароль</p>
                </div>
                <p className="text-base font-medium pl-6">••••••••••••</p>
              </div>
              <Button variant="secondary" size="sm">
                <Link href="/user/profile/change-password">
                  Изменить пароль
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
        <Card className="md:col-span-2 shadow-sm">
          <CardHeader>
            <div className="flex items-center gap-2 mb-1">
              <Link2 className="h-5 w-5 text-indigo-600" />
              <CardTitle className="text-xl">Связанные аккаунты</CardTitle>
            </div>
            <CardDescription>
              Привяжите аккаунты социальных сетей для быстрого и безопасного входа в систему.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4">
              <div className="flex items-center gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FFCC00] text-black font-bold shadow-sm">
                  Я
                </div>
                <div>
                  <p className="text-sm font-medium">Яндекс ID</p>
                  {isYandexAttached ? (
                    <div className="flex items-center gap-1 mt-0.5">
                      <CheckCircle2 className="h-3.5 w-3.5 text-green-600" />
                      <p className="text-xs text-green-600 font-medium">Привязан</p>
                    </div>
                  ) : (
                    <p className="text-xs text-muted-foreground mt-0.5">Не привязан</p>
                  )}
                </div>
              </div>
              {isYandexAttached ? (
                ''
              ) : (
                <Button variant="secondary" size="sm">
                  <Link href={getYandexAuthUrl(true)}>
                    Привязать
                  </Link>
                </Button>
              )}
            </div>



            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
              <div className="flex items-center gap-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white border shadow-sm">
                  <svg className="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                  </svg>
                </div>
                <div>
                  <p className="text-sm font-medium">Google</p>
                  {isGoogleAttached ? (
                    <div className="flex items-center gap-1 mt-0.5">
                      <CheckCircle2 className="h-3.5 w-3.5 text-green-600" />
                      <p className="text-xs text-green-600 font-medium">Привязан</p>
                    </div>
                  ) : (
                    <p className="text-xs text-muted-foreground mt-0.5">Не привязан</p>
                  )}
                </div>
              </div>
              {isGoogleAttached ? (
                ''
              ) : (
                <Button variant="secondary" size="sm">
                  <Link href={getGoogleAuthUrl(true)}>
                    Привязать
                  </Link>
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

    </div>
  );
}
