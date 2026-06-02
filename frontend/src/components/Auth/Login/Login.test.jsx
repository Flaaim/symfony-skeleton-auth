import { render, screen } from "@testing-library/react";
import Login from "./Login";
import userEvent from "@testing-library/user-event";

const mockPush = jest.fn();
jest.mock("next/navigation", () => ({
  useRouter() {
    return {
      push: mockPush,
    };
  },
}));

jest.mock("../../../actions/auth", () => ({
  __esModule: true,
  LoginAction: jest.fn().mockResolvedValue({ success: true }),
}));

describe("Login form", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });
  it("renders all fields and the submit button ", () => {
    render(<Login />);

    expect(screen.getByText("Вход в систему")).toBeInTheDocument();
    expect(screen.getByLabelText("Электронная почта")).toBeInTheDocument();
    expect(screen.getByLabelText("Пароль")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Войти" })).toBeInTheDocument();

    const registerLink = screen.getByRole("link", { name: "Зарегистрироваться" });
    expect(registerLink).toHaveAttribute('href', '/join/register');

    const forgotPassword = screen.getByRole("link", {name: "Забыли свой пароль?"});
    expect(forgotPassword).toHaveAttribute('href', '/join/reset');

  });

  it("shows validations errors", async () => {
    const user = userEvent.setup();
    render(<Login />);

    const emailInput = screen.getByLabelText(/Электронная почта/i);
    const passwordInput = screen.getByLabelText("Пароль");
    const submitButton = screen.getByRole("button", { name: "Войти" });

    await user.type(emailInput, "invalidEmail");
    await user.type(passwordInput, "123");
    await user.click(submitButton);

    expect(
      await screen.findByText("Пожалуйста, введите корректный email адрес.")
    ).toBeInTheDocument();
    expect(
      await screen.findByText("Пароль должен содержать минимум 8 символов.")
    ).toBeInTheDocument();
  });

  it("should redirect on success", async () => {
    const user = userEvent.setup();
    render(<Login />);

    const emailInput = screen.getByLabelText(/Электронная почта/i);
    const passwordInput = screen.getByLabelText("Пароль");
    const submitButton = screen.getByRole("button", { name: "Войти" });

    await user.type(emailInput, "flaaim@list.ru");
    await user.type(passwordInput, "12345678");
    await user.click(submitButton);

    expect(mockPush).toHaveBeenCalledWith("/user/dashboard");
  });
});
