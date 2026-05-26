import type { Metadata } from "next";
import { Handjet } from "next/font/google";
import "./globals.css";
import React from "react";

const handJet = Handjet({
  variable: "--font-handjet",
  subsets: ["cyrillic"],
  weight: ["400"],
});

export const metadata: Metadata = {
  title: "Rtn-tests.ru",
  description: "Шаблон",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body className={`${handJet.className} flex min-h-full flex-col antialiased`}>
        {children}
      </body>
    </html>
  );
}
