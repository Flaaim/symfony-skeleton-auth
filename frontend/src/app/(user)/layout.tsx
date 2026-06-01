import React from "react";
import { Metadata } from "next";
import { toast, Toaster } from "sonner";
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
        <div className="flex flex-col min-h-screen">
          <header className="flex h-16 shrink-0 items-center gap-2 border-b px-4 bg-background">
            <SidebarTrigger className="-ml-1" />
            <div className="h-4 w-px bg-border my-auto mx-2" />
            <span className="font-medium">Панель пользователя</span>
          </header>
          <main className="flex-1 p-6 max-[765px]:p-2.5">{children}</main>
          <footer className="p-4 border-t text-sm text-muted-foreground">Footer</footer>
        </div>
      </div>

      <Toaster position="top-center" richColors />
    </SidebarProvider>
  );
}
