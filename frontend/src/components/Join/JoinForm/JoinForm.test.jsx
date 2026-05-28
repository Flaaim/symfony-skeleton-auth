import {render, screen } from "@testing-library/react";
import JoinForm from "./JoinForm";
import userEvent from "@testing-library/user-event";

describe('join form', () => {
  it('renders all fields and the submit button', () => {

    render(<JoinForm />)
    expect(screen.getByText("Регистрация в системе")).toBeInTheDocument();
    expect(screen.getByLabelText(/Электронная почта/i)).toBeInTheDocument();
    expect(screen.getByLabelText("Пароль")).toBeInTheDocument();
    expect(screen.getByLabelText("Подтвердите пароль")).toBeInTheDocument();

    expect(screen.getByRole("button", {name: "Присоединиться"})).toBeInTheDocument();
  })

  it("shows validation errors", async () => {
    const user = userEvent.setup()

    render(<JoinForm />)

    const emailInput = screen.getByLabelText(/Электронная почта/i);
    const passwordInput = screen.getByLabelText("Пароль")
    const confirmPasswordInput = screen.getByLabelText("Подтвердите пароль")

    await user.type(emailInput, "invalid-email");
    await user.click(passwordInput);

    expect(await screen.findByText(/Пожалуйста, введите корректный email адрес/i)).toBeInTheDocument();

    await user.type(passwordInput, "123");
    await user.click(emailInput)
    expect(await screen.findByText(/Пароль должен содержать минимум 8 символов/i)).toBeInTheDocument();

    await user.type(passwordInput, "12345678");
    await user.type(confirmPasswordInput, "123456789");
    await user.click(emailInput)

    expect(await screen.findByText(/Пароли не совпадают/i));

  })

});
