import RequestChangePassword from "@/components/Auth/Email/ChangeEmailForm";
import {fetchUser} from "@/actions/auth";
import {redirect} from "next/navigation";
import RequestChangeEmail from "@/components/Auth/Email/ChangeEmailForm";


export default async function changeEmailPage() {
  let profile;
  try {
    profile = await fetchUser();
  } catch (error) {
    console.error("Ошибка авторизации в лейауте, перенаправление...", error);
    redirect('/join/login')
  }
  return (
    <RequestChangeEmail profile={profile}/>
  )
}
