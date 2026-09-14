import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                main: '#000000',
                panel: '#141311', // Dark beige/brownish for panels
                border: '#2b2824',
                txt: {
                    main: '#f5ece3', // Light beige for main text
                    muted: '#c2b5a3',
                },
                accent: {
                    beige: '#d4a373',
                    pink: {
                        DEFAULT: '#e5989b', // 20% visual weight accent
                        hover: '#ffb4a2'
                    }
                }
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            }
        },
    },

    // theme: {
    //     extend: {
    //         fontFamily: {
    //             sans: ['Figtree', ...defaultTheme.fontFamily.sans],
    //         },
    //     },
    // },

    plugins: [forms],
};
