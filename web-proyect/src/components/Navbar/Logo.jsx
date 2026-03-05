import { useNavigate } from "react-router-dom";
import brandLogo from "../../assets/iconos/tu_logo.svg";

export default function Logo() {
  const navigate = useNavigate();

  return (
    <button
      className="flex items-center justify-center h-10"
      onClick={() => navigate("/")}
      style={{ background: "none", border: "none", padding: 0, cursor: "pointer" }}
      aria-label="Ir al inicio"
    >
      <img
        src={brandLogo}
        alt="Logo de la plataforma"
        className="h-10 w-auto object-contain max-w-full max-h-full"
      />
    </button>
  );
}
