import ConfirmEmail from "./ConfirmEmail";
import { render, screen } from "@testing-library/react";
import ResetPasswordForm from "../../Password/Confirm/ConfirmForm";
import { joinConfirm } from "../../../../actions/auth";

const mockGet = jest.fn();
jest.mock("next/navigation", () => ({
  useSearchParams: () => ({
    get: mockGet,
  }),
}));
jest.mock("@/actions/auth", () => ({
  joinConfirm: () => ({ ok: true }),
}));
describe("Confirm email page", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("wrong token format or not exists", async () => {
    mockGet.mockReturnValue("invalid-token-123");

    render(<ConfirmEmail />);
    expect(
      await screen.findByRole("heading", { name: "Что-то пошло не так." })
    ).toBeInTheDocument();
  });

  it("render success", async () => {
    mockGet.mockReturnValue("123e4567-e89b-12d3-a456-426614174000");

    render(<ConfirmEmail />);

    expect(await screen.findByRole("heading", { name: "Успех!" })).toBeInTheDocument();
  });
});
