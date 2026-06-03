import React from "react";
import { Metadata } from "next";
import { Toaster } from "sonner";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { DashboardSidebar } from "@/components/User/Dashboard/DashboardSidebar";
import { fetchEmail } from "@/actions/user";

export const metadata: Metadata = {
  title: "Панель пользователя",
  description: "Описание страницы",
};

export default async function UserDashboardLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const data = await fetchEmail();
  return (
    <SidebarProvider>
      <div className="grid min-h-screen w-full grid-cols-[auto_1fr] max-[765px]:grid-cols-1">
        <DashboardSidebar email={data.email} />
        <div className="flex min-h-screen flex-col">
          <header className="bg-background flex h-16 shrink-0 items-center gap-2 border-b px-4">
            <SidebarTrigger className="-ml-1" />
            <div className="bg-border mx-2 my-auto h-4 w-px" />
            <span className="font-medium">Панель пользователя</span>
          </header>
          <main className="flex-1 p-6 max-[765px]:p-2.5">{children}</main>
          <footer className="text-muted-foreground border-t p-4 text-sm">Footer</footer>
        </div>
      </div>

      <Toaster position="top-center" richColors />
    </SidebarProvider>
  );
}
