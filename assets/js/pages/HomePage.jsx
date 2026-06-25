import React, { useContext } from "react";
import { Link } from "react-router-dom";
import AuthContext from "../contexts/AuthContext";

const HomePage = () => {
  const { isAuthenticated } = useContext(AuthContext);

  return (
    <div className="jumbotron">
      <h1 className="display-4">SymReact 🧾</h1>
      <p className="lead">
        Application de gestion de clients et de factures — API REST{" "}
        <strong>Symfony 7</strong> / <strong>API Platform 4</strong> et front{" "}
        <strong>React 18</strong>.
      </p>
      <hr className="my-4" />

      {isAuthenticated ? (
        <>
          <p className="lead">Vous êtes connecté. Que souhaitez-vous gérer ?</p>
          <p className="lead">
            <Link className="btn btn-primary btn-lg mr-2" to="/customers" role="button">
              Mes clients
            </Link>
            <Link className="btn btn-outline-secondary btn-lg" to="/invoices" role="button">
              Mes factures
            </Link>
          </p>
        </>
      ) : (
        <>
          <div className="demo-card my-4">
            <span className="demo-card__badge">Compte de démonstration</span>
            <p className="demo-card__hint">
              Testez l'application sans inscription avec ces identifiants :
            </p>
            <div className="demo-card__creds">
              <div className="demo-card__field">
                <span className="demo-card__label">Email</span>
                <span className="demo-card__value">demo@symreact.local</span>
              </div>
              <div className="demo-card__field">
                <span className="demo-card__label">Mot de passe</span>
                <span className="demo-card__value">password</span>
              </div>
            </div>
          </div>

          <p className="lead">
            <Link className="btn btn-primary btn-lg mr-2" to="/login" role="button">
              Connexion
            </Link>
            <Link
              className="btn btn-outline-secondary btn-lg"
              to="/register"
              role="button"
            >
              Inscription
            </Link>
          </p>
        </>
      )}
    </div>
  );
};

export default HomePage;
