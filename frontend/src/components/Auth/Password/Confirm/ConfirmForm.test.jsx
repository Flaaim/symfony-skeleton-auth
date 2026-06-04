import {render, screen } from "@testing-library/react";
import ResetPasswordForm from "./ConfirmForm";
import userEvent from "@testing-library/user-event/dist/cjs/index.js";


const mockGet = jest.fn();
jest.mock('next/navigation', () => ({
  useSearchParams: () => ({
    get: mockGet,
  }),
}));
describe("Confirm new password form", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("wrong token format or not exists", async () => {
    mockGet.mockReturnValue("invalid-token-123");

    render(<ResetPasswordForm />)
    expect(await screen.findByRole("heading", {name: "Ошибка доступа"})).toBeInTheDocument()
  });

  it("renders all fields and submit button", async () => {
    mockGet.mockReturnValue("123e4567-e89b-12d3-a456-426614174000");
    render(<ResetPasswordForm/>);

    expect(await screen.findByRole("heading", { name: "Восстановление доступа"})).toBeInTheDocument();
    expect(await screen.findByLabelText("Пароль")).toBeInTheDocument();
    expect(await screen.findByLabelText("Подтвердите пароль")).toBeInTheDocument();

    const submitButton = await screen.findByRole("button", { name: "Обновить пароль" });
    expect(submitButton).toBeInTheDocument();
  })

  it("shows validations errors", async () => {
    mockGet.mockReturnValue("123e4567-e89b-12d3-a456-426614174000");
    render(<ResetPasswordForm/>);
    const user = userEvent.setup();

    const passwordInput = screen.getByLabelText("Пароль");
    const confirmPasswordInput = screen.getByLabelText("Подтвердите пароль");
    const submitButton = screen.getByRole("button", {name: "Обновить пароль"})

    await user.type(passwordInput, "1234567");
    await user.type(confirmPasswordInput, "1234567");
    await user.click(submitButton);

    const errorMessages = screen.getAllByText("Пароль должен содержать минимум 8 символов.");

    expect(errorMessages).toHaveLength(2);

    errorMessages.forEach((msg) => {
      expect(msg).toBeInTheDocument();
    });

    await user.clear(passwordInput);
    await user.clear(confirmPasswordInput);

    await user.type(passwordInput, "12345678");
    await user.type(confirmPasswordInput, "123456789");

    expect(await screen.getByText("Пароли не совпадают")).toBeInTheDocument()

  })


})
