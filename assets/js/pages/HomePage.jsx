import React from "react";
import { Link } from "react-router-dom";

const HomePage = () => {
  return (
    <div className="jumbotron">
      <h1 className="display-4">SymReact 🧾</h1>
      <p className="lead">
        Application de gestion de clients et de factures — API REST{" "}
        <strong>Symfony 7</strong> / <strong>API Platform 4</strong> et front{" "}
        <strong>React 18</strong>.
      </p>
      <hr className="my-4" />

      <div className="alert alert-info">
        <h4 className="alert-heading">Compte de démonstration</h4>
        <p className="mb-1">
          Pour tester l'application sans inscription, connectez-vous avec :
        </p>
        <ul className="mb-0">
          <li>
            Email : <code>demo@symreact.local</code>
          </li>
          <li>
            Mot de passe : <code>password</code>
          </li>
        </ul>
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
    </div>
  );
};

export default HomePage;
