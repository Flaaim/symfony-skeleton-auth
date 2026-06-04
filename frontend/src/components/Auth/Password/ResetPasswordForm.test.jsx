import ResetPasswordForm from "./ResetPasswordForm";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event/dist/cjs/index.js";
import { passwordResetRequest } from "../../../actions/auth";

jest.mock("../../../actions/auth", () => {
  const mockFn = jest.fn();
  return {
    __esModule: true,
    default: mockFn,
    passwordResetRequest: mockFn,
  };
});

const mockRequestResetPassword = jest.mocked(passwordResetRequest);
describe("Reset password form", () => {
  it("renders email field and the submit button", () => {
    render(<ResetPasswordForm />);

    expect(screen.getByText("Восстановление пароля")).toBeInTheDocument();
    expect(screen.getByLabelText("Электронная почта")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Отправить запрос" })).toBeInTheDocument();
  });

  it("shows validations errors", async () => {
    const user = userEvent.setup();

    render(<ResetPasswordForm />);
    const emailInput = screen.getByLabelText("Электронная почта");
    const submitButton = screen.getByRole("button", { name: "Отправить запрос" });

    await user.type(emailInput, "invalidEmail");
    await user.click(submitButton);

    expect(
      await screen.findByText("Пожалуйста, введите корректный email адрес.")
    ).toBeInTheDocument();
  });

  it("show success card", async () => {
    const user = userEvent.setup();
    mockRequestResetPassword.mockResolvedValue({ ok: true });

    render(<ResetPasswordForm />);
    const emailInput = screen.getByLabelText("Электронная почта");
    const submitButton = screen.getByRole("button", { name: "Отправить запрос" });

    await user.type(emailInput, "valid@email.ru");
    await user.click(submitButton);

    const successTitle = await screen.findByText("Проверьте вашу почту");
    expect(successTitle).toBeInTheDocument();
  });
});
