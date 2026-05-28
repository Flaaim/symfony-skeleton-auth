import {render, screen } from "@testing-library/react";
import JoinByEmail from "./JoinByEmail";
import userEvent from "@testing-library/user-event";
import JoinAction from "../../../actions/auth";

jest.mock("../../../actions/auth", () => {
  const mockFn = jest.fn();
  return {
    __esModule: true,
    default: mockFn,
    JoinAction: mockFn,  
  };
});

jest.mock('next/navigation', () => ({
  useRouter: () => ({ push: jest.fn() }),
}));

const mockJoinAction = jest.mocked(JoinAction);
describe('join form', () => {
  it('renders all fields and the submit button', () => {

    render(<JoinByEmail />)
    expect(screen.getByText("Регистрация в системе")).toBeInTheDocument();
    expect(screen.getByLabelText(/Электронная почта/i)).toBeInTheDocument();
    expect(screen.getByLabelText("Пароль")).toBeInTheDocument();
    expect(screen.getByLabelText("Подтвердите пароль")).toBeInTheDocument();

    expect(screen.getByRole("button", {name: "Присоединиться"})).toBeInTheDocument();
  })

  it("shows validation errors", async () => {
    const user = userEvent.setup()

    render(<JoinByEmail />)

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

  it("show success card", async () => {
    const user = userEvent.setup()
    mockJoinAction.mockResolvedValue({ success: true })


    render(<JoinByEmail/>);

    const emailInput = screen.getByLabelText(/Электронная почта/i);
    const passwordInput = screen.getByLabelText("Пароль");
    const confirmPasswordInput = screen.getByLabelText("Подтвердите пароль");
    const submitButton = screen.getByRole("button");

    await user.type(emailInput, "test@mail.ru");
    await user.type(passwordInput, "12345678");
    await user.type(confirmPasswordInput, "12345678");

    await user.click(submitButton);

    const successTitle = await screen.findByText(/Проверьте вашу почту/i);
    expect(successTitle).toBeInTheDocument();

  })

});
