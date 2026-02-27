import React, { useState, useEffect } from "react";
import LibroReclamaciones from "../../assets/iconos/LibroReclamaciones.svg";
import VISAIcon from "../../assets/iconos/VISAIcono.svg";
import MCIcon from "../../assets/iconos/MCIcon.svg";
import AMEXIcon from "../../assets/iconos/AMEXIcon.svg";
import PyPIcon from "../../assets/iconos/PyPIcon.svg";
import WUIcon from "../../assets/iconos/WUIcon.svg";
import Facebook from "../../assets/iconos/Facebook.svg";
import Instagram from "../../assets/iconos/Instagram.svg";
import YouTube from "../../assets/iconos/YouTube.svg";
import LinkedIn from "../../assets/iconos/LinkedIn.svg";
import TikTok from "../../assets/iconos/TikTok.svg";

const siteLinks = [
  { label: "Inicio", href: "/" },
  { label: "Ofertas", href: "/ofertas" },
  { label: "Catalogo", href: "/catalogo/tecnologia" },
  { label: "Carrito", href: "/carrito" },
];

const stackLinks = [
  { label: "Backend API", href: "#" },
  { label: "Frontend React", href: "#" },
  { label: "Diseno responsive", href: "#" },
  { label: "Arquitectura modular", href: "#" },
];

const socialLinks = [
  {
    label: "LinkedIn",
    href: "https://www.linkedin.com/in/isaac-pasapera-navarro/",
    icon: LinkedIn,
  },
  {
    label: "Instagram",
    href: "https://www.instagram.com/isaacpasapera/",
    icon: Instagram,
  },
  {
    label: "GitHub",
    href: "https://github.com/isaacpassnav",
    icon: null,
  },
  { label: "Facebook", href: "https://www.facebook.com/", icon: Facebook },
  { label: "YouTube", href: "https://www.youtube.com/", icon: YouTube },
  { label: "TikTok", href: "https://www.tiktok.com/", icon: TikTok },
  { label: "X", href: "https://x.com/", icon: null },
];

export default function FooterGrande() {
  const [theme, setTheme] = useState(() => {
    return document.documentElement.getAttribute("data-theme") || "light";
  });

  useEffect(() => {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === "data-theme") {
          setTheme(document.documentElement.getAttribute("data-theme") || "light");
        }
      });
    });

    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ["data-theme"],
    });

    return () => observer.disconnect();
  }, []);

  const isDark = theme === "dark";
  const colors = {
    bg: isDark ? "#1F1A57" : "#163B85",
    title: isDark ? "#F4F5FF" : "#FFFFFF",
    text: isDark ? "#C9CAE5" : "#DCE7FF",
    muted: isDark ? "#AEB1D5" : "#BFD0F3",
    border: isDark ? "rgba(255,255,255,0.22)" : "rgba(255,255,255,0.14)",
    chipBg: isDark ? "rgba(255,255,255,0.12)" : "rgba(255,255,255,0.16)",
    cardBg: isDark ? "rgba(255,255,255,0.12)" : "rgba(255,255,255,0.2)",
    paymentBg: isDark ? "rgba(255,255,255,0.16)" : "#FFFFFF",
  };

  return (
    <div
      className="w-full max-w-[1600px] mx-auto p-6 sm:p-8 lg:px-16 lg:pt-[132px] lg:pb-10 rounded-[32px]"
      style={{ backgroundColor: colors.bg, transition: "background-color 0.3s ease" }}
    >
      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
        <section className="flex flex-col gap-4">
          <h3
            className="text-[22px] leading-7 sm:text-[26px] sm:leading-8 font-semibold tracking-wide"
            style={{ color: colors.title }}
          >
            ISAACPASAPERA.DEV
          </h3>
          <p className="text-sm leading-6" style={{ color: colors.text }}>
            Full Stack Developer — built with love ♥
          </p>
          <p className="text-sm leading-6 max-w-[360px]" style={{ color: colors.muted }}>
            Proyecto ecommerce en evolucion. Frontend modular, backend desacoplado y base lista para escalar por dominios.
          </p>
          <a href="#" className="w-fit mt-1">
            <img src={LibroReclamaciones} alt="Libro de reclamaciones" className="h-8" />
          </a>
        </section>

        <section className="flex flex-col gap-3">
          <h4 className="text-lg font-semibold" style={{ color: colors.title }}>
            Navegacion
          </h4>
          {siteLinks.map((item) => (
            <a
              key={item.label}
              href={item.href}
              className="text-sm hover:underline transition-colors"
              style={{ color: colors.text }}
            >
              {item.label}
            </a>
          ))}
        </section>

        <section className="flex flex-col gap-3">
          <h4 className="text-lg font-semibold" style={{ color: colors.title }}>
            Stack y enfoque
          </h4>
          {stackLinks.map((item) => (
            <a
              key={item.label}
              href={item.href}
              className="text-sm hover:underline transition-colors"
              style={{ color: colors.text }}
            >
              {item.label}
            </a>
          ))}
          <div
            className="mt-3 inline-flex items-center gap-3 rounded-full px-4 py-2 w-fit"
            style={{ backgroundColor: colors.paymentBg }}
          >
            <img src={VISAIcon} alt="Visa" className="h-6" />
            <img src={MCIcon} alt="Mastercard" className="h-6" />
            <img src={AMEXIcon} alt="Amex" className="h-6" />
            <img src={PyPIcon} alt="PyP" className="h-6" />
            <img src={WUIcon} alt="WU" className="h-6" />
          </div>
        </section>

        <section className="flex flex-col gap-3">
          <h4 className="text-lg font-semibold" style={{ color: colors.title }}>
            Redes
          </h4>
          <div className="flex flex-wrap gap-2">
            {socialLinks.map((item) =>
              item.icon ? (
                <a
                  key={item.label}
                  href={item.href}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={item.label}
                  className="h-10 w-10 rounded-full flex items-center justify-center transition-transform hover:scale-105"
                  style={{ backgroundColor: colors.cardBg }}
                >
                  <img src={item.icon} alt={item.label} className="h-5 w-5" />
                </a>
              ) : (
                <a
                  key={item.label}
                  href={item.href}
                  target="_blank"
                  rel="noreferrer"
                  className="text-sm px-3 py-2 rounded-full hover:underline"
                  style={{ color: colors.text, backgroundColor: colors.chipBg }}
                >
                  {item.label}
                </a>
              )
            )}
          </div>
          <a
            href="mailto:contacto@isaacpasapera.dev"
            className="text-sm mt-2 hover:underline"
            style={{ color: colors.muted }}
          >
            contacto@isaacpasapera.dev
          </a>
        </section>
      </div>

      <div
        className="mt-8 pt-4 text-center text-sm"
        style={{ color: colors.text, borderTop: `1px solid ${colors.border}` }}
      >
        Copyright © 2026 ISAACPASAPERA.DEV. Todos los derechos reservados.
      </div>
    </div>
  );
}
