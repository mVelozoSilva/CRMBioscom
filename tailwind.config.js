    // tailwind.config.js
    /** @type {import('tailwindcss').Config} */
    module.exports = {
      // CRÍTICO: Esta propiedad 'content' le dice a Tailwind qué archivos escanear.
      content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
      ],
      theme: {
        extend: {
          // Colores de Bioscom para usar con clases de Tailwind (ej. bg-bioscom-primary)
          colors: {
            'bioscom': {
              primary: '#6284b8',
              secondary: '#5f87b8',
              accent: '#00334e',
              background: '#f3f6fa',
              'text-primary': '#00334e',
              success: '#28a745',
              warning: '#ffc107',
              error: '#dc3545',
              info: '#17a2b8',
              neutral: '#6c757d',
            },
            // Sobrescribir las escalas de colores estándar de Tailwind (para usar clases como 'text-red-600')
            'red': { 
                50: '#FEE2E2', 100: '#FECACA', 200: '#FCA5A5', 300: '#F87171', 400: '#EF4444', 500: '#DC2626',
                600: '#DC3545', 700: '#B91C1C', 800: '#991B1B', 900: '#7F1D1D',
            },
            'yellow': {
                50: '#FFFBEB', 100: '#FEF3C7', 200: '#FDE68A', 300: '#FCD34D', 400: '#FBBF24', 500: '#F59E0B',
                600: '#D97706', 700: '#B45309', 800: '#92400E', 900: '#78350F',
            },
            'green': {
                50: '#ECFDF5', 100: '#D1FAE5', 200: '#A7F3D0', 300: '#6EE7B7', 400: '#34D399', 500: '#10B981',
                600: '#059669', 700: '#047857', 800: '#065F46', 900: '#064E40',
            },
            'blue': {
                50: '#EFF6FF', 100: '#DBEAFE', 200: '#BFDBFE', 300: '#93C5FD', 400: '#60A5FA', 500: '#3B82F6',
                600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF', 900: '#1E3A8A',
            },
            'gray': {
                50: '#f9fafb', 100: '#f3f4f6', 200: '#e5e7eb', 300: '#d1d5db', 400: '#9ca3af', 500: '#6b7280',
                600: '#4b5563', 700: '#374151', 800: '#1f2937', 900: '#111827',
            }
          },
          // Configuración de fuentes
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            serif: ['Georgia', 'serif'],
          },
          // Border Radius
          borderRadius: {
            'bioscom': '0.5rem',
          },
          // Espaciado
          spacing: {
            'xs': '0.25rem', 'sm': '0.5rem', 'md': '1rem', 'lg': '1.5rem', 'xl': '2rem', '2xl': '3rem',
          }
        },
      },
      plugins: [],
    };
    