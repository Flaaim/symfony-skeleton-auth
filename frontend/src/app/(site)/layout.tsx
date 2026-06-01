import React from "react";
import { Metadata } from "next";
import { Toaster } from "sonner";

export const metadata: Metadata = {
  title: "Тесты ростехнадзора",
  description: "Описание страницы",
};

export default function SiteLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <div className="grid min-h-screen grid-cols-[1fr_270px_700px_1fr] grid-rows-[auto_1fr_auto] gap-x-10 gap-y-12 max-[765px]:grid-cols-1 max-[765px]:grid-rows-[auto_auto_auto] max-[765px]:p-2.5">
      <header className="col-start-2 col-end-4 max-[765px]:hidden">Header</header>
      <main className="col-start-2 col-end-4">
        {children}
        <Toaster position="top-center" richColors />
      </main>
      <footer className="col-start-2 col-end-4">Footer</footer>
    </div>
  );
}
