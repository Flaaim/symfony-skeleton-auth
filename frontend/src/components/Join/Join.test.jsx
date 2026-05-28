import {render, screen } from "@testing-library/react";
import Join from "./Join";


describe("Join page", () => {
  it('renders a join', () => {

    render(<Join />)

    const inputEmail  = screen.getByLabelText(/Электронная почта/i)
    const inputPassword  = screen.getByLabelText("Пароль")
    const inputConfirmPassword = screen.getByLabelText("Подтвердите пароль")

    expect(inputEmail).toBeInTheDocument();
    expect(inputPassword).toBeInTheDocument();
    expect(inputConfirmPassword).toBeInTheDocument();

  })

})
