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

  const attachedNetworks: string[] = profile.networks || [];
  const isYandexAttached = attachedNetworks.some(net => net.network === "yandex");

  console.log(attachedNetworks)
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
                <Link href="/user/dashboard/profile/change-password">
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
          </CardContent>
        </Card>
      </div>

    </div>
  );
}
