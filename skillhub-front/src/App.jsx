import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import DashboardFormateur from "./pages/DashboardFormateur";
import DashboardApprenant from "./pages/DashboardApprenant";
import Home from "./pages/Home";
import Ateliers from "./pages/Ateliers";
import FormationDetail from "./pages/FormationDetail";
import FormationSuivi from "./pages/FormationSuivi";
import PrivateRoute from "./components/PrivateRoute";
import PublicLayout from "./components/PublicLayout";
import "./App.css";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route element={<PublicLayout />}>
          <Route path="/" element={<Home />} />
          <Route path="/ateliers" element={<Ateliers />} />
          <Route path="/ateliers/:id" element={<FormationDetail />} />
        </Route>

        <Route
          path="/formateur"
          element={
            <PrivateRoute role="FORMATEUR">
              <DashboardFormateur />
            </PrivateRoute>
          }
        />

        <Route
          path="/apprenant"
          element={
            <PrivateRoute role="APPRENANT">
              <DashboardApprenant />
            </PrivateRoute>
          }
        />
        <Route
          path="/apprenant/suivi/:id"
          element={
            <PrivateRoute role="APPRENANT">
              <FormationSuivi />
            </PrivateRoute>
          }
        />

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
