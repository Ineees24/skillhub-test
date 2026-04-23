import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
} from "react";
import PropTypes from "prop-types";
import AuthModals from "../components/AuthModals";
import { getUser } from "../services/authService";

const AuthModalContext = createContext(null);

export function AuthModalProvider({ children }) {
  const [modal, setModal] = useState(null);
  const [modalData, setModalData] = useState(null);
  const [sessionTick, setSessionTick] = useState(0);

  const refreshPublicSession = useCallback(() => {
    setSessionTick((t) => t + 1);
  }, []);

  const publicUser = useMemo(() => getUser(), [sessionTick]);

  const openModal = useCallback((type, data = null) => {
    setModal(type);
    setModalData(data);
    document.body.style.overflow = "hidden";
  }, []);

  const closeModal = useCallback(() => {
    setModal(null);
    setModalData(null);
    document.body.style.overflow = "";
  }, []);

  const value = useMemo(
    () => ({
      modal,
      modalData,
      openModal,
      closeModal,
      publicUser,
      refreshPublicSession,
    }),
    [modal, openModal, closeModal, publicUser, refreshPublicSession],
  );

  return (
    <AuthModalContext.Provider value={value}>
      {children}
      <AuthModals
        modal={modal}
        modalData={modalData}
        onClose={closeModal}
        openModal={openModal}
        refreshPublicSession={refreshPublicSession}
      />
    </AuthModalContext.Provider>
  );
}

AuthModalProvider.propTypes = {
  children: PropTypes.node.isRequired,
};

export function useAuthModal() {
  const ctx = useContext(AuthModalContext);
  if (!ctx) {
    throw new Error("useAuthModal must be used within AuthModalProvider");
  }
  return ctx;
}