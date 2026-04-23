import api from "./axios";

const TOKEN_KEYS = ["access_token", "token", "auth_token"];

function toHex(bytes) {
  return Array.from(bytes, (b) => b.toString(16).padStart(2, "0")).join("");
}

async function hmacSha256Hex(key, message) {
  const keyString = pickString(key);
  const messageString = pickString(message);
  if (!keyString || messageString === null) {
    throw new Error("Impossible de calculer la preuve HMAC (valeurs manquantes).");
  }

  // Browser (WebCrypto)
  const subtle = globalThis.crypto?.subtle;
  if (subtle && typeof TextEncoder !== "undefined") {
    const enc = new TextEncoder();
    const cryptoKey = await subtle.importKey(
      "raw",
      enc.encode(keyString),
      { name: "HMAC", hash: "SHA-256" },
      false,
      ["sign"],
    );
    const signature = await subtle.sign("HMAC", cryptoKey, enc.encode(messageString));
    return toHex(new Uint8Array(signature));
  }

  // Node/Vitest fallback
  try {
    const { createHmac } = await import("crypto");
    return createHmac("sha256", keyString).update(messageString, "utf8").digest("hex");
  } catch {
    throw new Error("WebCrypto indisponible pour calculer le HMAC.");
  }
}

function generateNonce() {
  if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID().replaceAll("-", "");
  // fallback: not cryptographically strong, but avoids blocking older runtimes
  return `${Date.now().toString(16)}${Math.random().toString(16).slice(2)}${Math.random()
    .toString(16)
    .slice(2)}`.slice(0, 64);
}

function isRecord(value) {
  return value !== null && typeof value === "object";
}

function pickString(value) {
  if (typeof value !== "string") return null;
  const v = value.trim();
  if (!v || v === "undefined" || v === "null") return null;
  return v;
}

function firstString(values) {
  for (const value of values) {
    const stringValue = pickString(value);
    if (stringValue) return stringValue;
  }

  return null;
}

function extractBearerToken(authHeader) {
  const header = pickString(authHeader);
  if (!header) return null;
  const lowerHeader = header.toLowerCase();
  const bearerPrefix = "bearer ";

  if (!lowerHeader.startsWith(bearerPrefix)) {
    return header;
  }

  return pickString(header.slice(bearerPrefix.length).trimStart());
}

function extractTokenFromNode(node, keys) {
  if (!isRecord(node)) return null;
  return firstString(keys.map((key) => node[key]));
}

function extractTokenFromCandidates(candidates, keys) {
  for (const node of candidates) {
    const token = extractTokenFromNode(node, keys);
    if (token) return token;
  }

  return null;
}

function extractToken(data) {
  if (!isRecord(data)) return null;

  const nestedCandidates = [
    data,
    data?.data,
    data?.result,
    data?.authorization,
    data?.authorisation,
    data?.meta,
  ];
  const alternativeCandidates = [data, data?.data];
  const alternativeKeys = ["accessToken", "jwt", "plainTextToken"];

  return (
    extractTokenFromCandidates(nestedCandidates, TOKEN_KEYS) ??
    extractTokenFromCandidates(alternativeCandidates, alternativeKeys)
  );
}

function extractUser(data, fallbackUser) {
  if (data?.user && typeof data.user === "object") return data.user;
  if (data?.data?.user && typeof data.data.user === "object")
    return data.data.user;
  return fallbackUser ?? null;
}

function storeSession(data, fallbackUser) {
  const token = extractToken(data);
  const user = extractUser(data, fallbackUser);

  if (token) {
    localStorage.setItem("token", token);
  } else {
    localStorage.removeItem("token");
  }

  // On ne conserve l'utilisateur que si un token valide existe.
  // Sinon l'UI peut croire a une connexion alors que l'API refuse toutes les routes protegees.
  if (token && user) {
    localStorage.setItem("user", JSON.stringify(user));
  } else {
    localStorage.removeItem("user");
  }

  return { token, user };
}

export const getToken = () => {
  const token = localStorage.getItem("token");
  if (!token || token === "undefined" || token === "null") return null;
  return token;
};

export const getUser = () => JSON.parse(localStorage.getItem("user") || "null");

export const clearSession = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("user");
};

export const login = async (email, password) => {
  const nonce = generateNonce();
  const timestamp = Math.floor(Date.now() / 1000);
  const message = `${String(email).toLowerCase()}:${nonce}:${timestamp}`;
  const hmac = await hmacSha256Hex(password, message);

  const response = await api.post("/login", { email, nonce, timestamp, hmac });
  const headerToken =
    extractBearerToken(response.headers?.authorization) ||
    extractBearerToken(response.headers?.Authorization);

  const payload = headerToken
    ? { ...(response.data || {}), token: response.data?.token ?? headerToken }
    : response.data;

  const { token, user } = storeSession(payload);
  if (!token) {
    clearSession();
    throw new Error(
      "Connexion reussie mais token manquant dans la reponse serveur.",
    );
  }
  return user;
};

export const register = async ({ nom, prenom, email, password, role }) => {
  const { data } = await api.post("/register", {
    nom,
    prenom,
    email,
    password,
    role,
  });

  return data?.user ?? null;
};

export const logout = async () => {
  await api.post("/logout");
  clearSession();
};
