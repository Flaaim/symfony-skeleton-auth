import {NextRequest, NextResponse} from "next/server";


export function middleware(request: NextRequest) {
  const token = request.cookies.get("access_token")?.value;

  const { pathname } = request.nextUrl;

  if(pathname.startsWith('/user') && !token){
    if(!token){
      const loginUrl = new URL("/join/login", request.url);
      return NextResponse.redirect(loginUrl);
    }
  }

  if(pathname === "/join/login" && token){
    return NextResponse.redirect(new URL("/dashboard", request.url));
  }
  return NextResponse.next();
}

export const config = {
  matcher: ["/dashboard/:path*", "/join/login"],
};
