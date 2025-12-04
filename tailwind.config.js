/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/Livewire/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                'mika': {
                    'bg': '#121212',
                    'surface': '#1E1E1E',
                    'surface-light': '#2D2D2D',
                    'primary': '#5D3FD3',
                    'primary-light': '#7B5FE5',
                    'primary-dark': '#4A2FB8',
                    'success': '#4CAF50',
                    'danger': '#F44336',
                    'warning': '#FF9800',
                    'text': '#FFFFFF',
                    'text-secondary': '#B3B3B3',
                },
            },
            fontFamily: {
                'sans': ['Inter', 'system-ui', 'sans-serif'],
            },
            animation: {
                'bounce-slow': 'bounce 2s infinite',
                'pulse-slow': 'pulse 3s infinite',
                'typing': 'typing 1.5s steps(3) infinite',
            },
            keyframes: {
                typing: {
                    '0%, 100%': { opacity: 0 },
                    '50%': { opacity: 1 },
                },
            },
        },
    },
    plugins: [],
}
