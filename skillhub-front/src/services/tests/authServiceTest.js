import { beforeEach, describe, expect, it, vi } from "vitest";
import api from "../axios";
import {
  clearSession,
  getToken,
  getUser,
  login,
  logout,
  register,
} from "../authService";

vi.mock("../axios", () => ({
  default: {
    post: vi.fn(),
  },
}));

function createLocalStorageMock() {
  const storage = {};

  return {
    getItem: (key) => (key in storage ? storage[key] : null),
    setItem: (key, value) => {
      storage[key] = String(value);
    },
    removeItem: (key) => {
      delete storage[key];
    },
    clear: () => {
      for (const key of Object.keys(storage)) {
        delete storage[key];
      }
    },
  };
}

describe("authService", () => {
  beforeEach(() => {
    Object.defineProperty(globalThis, "localStorage", {
      value: createLocalStorageMock(),
      writable: true,
      configurable: true,
    });
    vi.clearAllMocks();
    localStorage.clear();
  });

  it("stores token from response payload and user on login", async () => {
    api.post.mockResolvedValueOnce({
      data: {
        access_token: "token-from-body",
        user: { id: 1, nom: "Doe" },
      },
      headers: {},
    });

    const user = await login("john@example.com", "secret");

    expect(api.post).toHaveBeenCalledWith("/login", {
      email: "john@example.com",
      nonce: expect.any(String),
      timestamp: expect.any(Number),
      hmac: expect.any(String),
    });
    expect(user).toEqual({ id: 1, nom: "Doe" });
    expect(getToken()).toBe("token-from-body");
    expect(getUser()).toEqual({ id: 1, nom: "Doe" });
  });

  it("stores token extracted from Bearer authorization header", async () => {
    api.post.mockResolvedValueOnce({
      data: {
        user: { id: 2, nom: "Header" },
      },
      headers: {
        authorization: "Bearer   header-token-value",
      },
    });

    await login("header@example.com", "secret");

    expect(getToken()).toBe("header-token-value");
    expect(getUser()).toEqual({ id: 2, nom: "Header" });
  });

  it("rejects login when token is missing", async () => {
    api.post.mockResolvedValueOnce({
      data: {
        user: { id: 3, nom: "NoToken" },
      },
      headers: {},
    });

    await expect(login("no-token@example.com", "secret")).rejects.toThrow(
      "Connexion reussie mais token manquant dans la reponse serveur.",
    );
    expect(getToken()).toBeNull();
    expect(getUser()).toBeNull();
  });

  it("supports token aliases and ignores literal undefined/null", async () => {
    localStorage.setItem("token", "undefined");
    expect(getToken()).toBeNull();

    api.post.mockResolvedValueOnce({
      data: {
        auth_token: "alias-token",
        data: {
          user: { id: 4, nom: "Alias" },
        },
      },
      headers: {},
    });

    await login("alias@example.com", "secret");
    expect(getToken()).toBe("alias-token");
  });

  it("register returns user and logout clears session", async () => {
    api.post
      .mockResolvedValueOnce({
        data: {
          user: { id: 5, nom: "New" },
        },
      })
      .mockResolvedValueOnce({});

    const user = await register({
      nom: "New",
      prenom: "User",
      email: "new@example.com",
      password: "secret",
      role: "apprenant",
    });

    localStorage.setItem("token", "persisted-token");
    localStorage.setItem("user", JSON.stringify({ id: 5 }));
    await logout();

    expect(user).toEqual({ id: 5, nom: "New" });
    expect(api.post).toHaveBeenCalledWith("/logout");
    expect(getToken()).toBeNull();
    expect(getUser()).toBeNull();
  });

  it("clearSession removes persisted data", () => {
    localStorage.setItem("token", "token");
    localStorage.setItem("user", JSON.stringify({ id: 6 }));

    clearSession();

    expect(getToken()).toBeNull();
    expect(getUser()).toBeNull();
  });
});
