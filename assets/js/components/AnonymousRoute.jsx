import React, { useContext } from "react";
import { Navigate, Outlet } from "react-router-dom";
import AuthContext from "../contexts/AuthContext";

// Garde "anonyme uniquement" : un utilisateur déjà connecté est redirigé
// hors des pages de connexion / inscription.
const AnonymousRoute = () => {
  const { isAuthenticated } = useContext(AuthContext);

  return isAuthenticated ? <Navigate to="/customers" replace /> : <Outlet />;
};

export default AnonymousRoute;
