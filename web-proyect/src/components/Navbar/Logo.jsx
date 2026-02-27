
    import brandLogo from '../../assets/iconos/okea_logo.svg';
    import brandLogoDark from '../../assets/iconos/okea_logo_categorias.svg';
    import { useNavigate } from 'react-router-dom';
    import { useState, useEffect } from 'react';

    // =======================
    // 1. ESTADO Y NAVEGACIÓN
    // =======================

    export default function Logo() {
        const navigate = useNavigate();
        const [theme, setTheme] = useState(() => {
            return document.documentElement.getAttribute('data-theme') || 'light';
        });

        // =======================
        // 2. EFECTO: OBSERVAR CAMBIO DE TEMA
        // =======================

        useEffect(() => {
            setTheme(document.documentElement.getAttribute('data-theme') || 'light');
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'data-theme') {
                        setTheme(document.documentElement.getAttribute('data-theme') || 'light');
                    }
                });
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme'],
            });
            return () => observer.disconnect();
        }, []);

        // =======================
        // 3. RENDER
        // =======================

        return (
            <button
                className="flex items-center justify-center h-10" 
                onClick={() => navigate('/')}
                style={{ background: 'none', border: 'none', padding: 0, cursor: 'pointer' }}
                aria-label="Ir al inicio"
            >
                <img 
                    src={theme === 'dark' ? brandLogoDark : brandLogo} 
                    alt="Logo de la plataforma" 
                    className="h-10 w-auto object-contain max-w-full max-h-full" 
                />
            </button>
        );
    }
