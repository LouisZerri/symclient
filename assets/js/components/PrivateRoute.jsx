import React, { useContext } from "react";
import { Navigate, Outlet } from "react-router-dom";
import AuthContext from "../contexts/AuthContext";

// Garde de route pour react-router v6 : rend les routes enfants (Outlet)
// si l'utilisateur est authentifié, sinon redirige vers /login.
const PrivateRoute = () => {
  const { isAuthenticated } = useContext(AuthContext);

  return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace />;
};

export default PrivateRoute;
