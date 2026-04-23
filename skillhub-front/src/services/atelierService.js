import api from "./axios";

function normalizeAtelierId(id) {
  const normalizedId = Number.parseInt(String(id), 10);
  if (!Number.isInteger(normalizedId) || normalizedId <= 0) {
    throw new Error("Identifiant atelier invalide.");
  }
  return normalizedId;
}

export const fetchAteliers = async () => {
  const { data } = await api.get("/ateliers");
  return data;
};

export const fetchAtelierDetail = async (id) => {
  const atelierId = normalizeAtelierId(id);
  const { data } = await api.get(`/ateliers/${atelierId}`);
  return data;
};

export const inscrireAtelier = async (id) => {
  const atelierId = normalizeAtelierId(id);
  const { data } = await api.post(`/ateliers/${atelierId}/inscription`);
  return data;
};

export const desinscrireAtelier = async (id) => {
  const atelierId = normalizeAtelierId(id);
  const { data } = await api.delete(`/ateliers/${atelierId}/inscription`);
  return data;
};

export const fetchMesInscriptions = async () => {
  const { data } = await api.get("/mes-inscriptions");
  return data;
};
