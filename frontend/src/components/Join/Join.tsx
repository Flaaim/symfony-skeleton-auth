import Link from "next/link";


export default function Join() {
  return (
    <>
      <h1 className="text-4xl font-extrabold text-gray-600 sm:text-5xl">Присоединиться</h1>
      <p>Вы тут.</p>
      <Link href='/'>Назад на главную</Link>
    </>

  );
}
