import { render, screen } from "@testing-library/react";
import {DashboardSidebar} from "./DashboardSidebar";
import {SidebarProvider} from "../../ui/sidebar";

beforeAll(() => {
  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: jest.fn().mockImplementation(query => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: jest.fn(),
      removeListener: jest.fn(),
      addEventListener: jest.fn(),
      removeEventListener: jest.fn(),
      dispatchEvent: jest.fn(),
    })),
  });
})


describe("show sidebar panel", () => {
  it("render email", () => {
    render(
      <SidebarProvider>
        <DashboardSidebar email="test@email.com" />
      </SidebarProvider>
    )

    expect(screen.getByRole("button", {name: "test@email.com"})).toBeInTheDocument();
  })
})
