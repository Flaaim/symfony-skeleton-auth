import {render, screen } from "@testing-library/react";
import Join from "./Join";


describe("Join page", () => {
  it('renders a join', () => {

    render(<Join />)
    const heading = screen.getByRole("heading", { level: 1 });
    expect(heading).toBeInTheDocument();

    const testHeading = screen.getByText(/Присоединиться/i)
    expect(testHeading).toBeInTheDocument()

    const homeLink = screen.getByRole("link", { name: /Назад на главную/i });
    expect(homeLink).toBeInTheDocument();
    expect(homeLink).toHaveAttribute("href", "/");
  })

})
