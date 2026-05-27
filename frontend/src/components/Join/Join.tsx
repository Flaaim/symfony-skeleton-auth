import Link from "next/link";
import JoinForm from "@/components/Join/JoinForm/JoinForm";


export default function Join() {
  return (
    <>
      <h1>Присоединиться</h1>
      <div className="flex h-screen items-center justify-center">
        <JoinForm />
      </div>
      <Link href='/'>Назад на главную</Link>
    </>

  );
}
