import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    safelist: [
        'bot-message',
        'user-message',
        'bot-bubble',
        'user-bubble',
    ],

    // safelist: [
    //     'flex', 'min-h-screen','w-1/2','p-6', 'solid', 'red','list-disc', 'pl-5', 'space-y-2',
    //     'flex-col', 'flex-row','border-2', 'border-red-500','overflow-y-auto', 'h-64', 'mb-4',
    //     'gap-3', 'bg-blue-600', 'text-white', 'px-4', 'rounded-r', 
    //     'gap-4',
    //     'relative',
    //     'w-full',
    //     'p-2',
    //     'border',
    //     'border-lime-300',
    //     'border-l-purple-300',
    //     'border-gray-300',
    //     'bg-lime-100',
    //     'bg-purple-200',
    //     'bg-blue-500',
    //     'rounded-xl',
    //     'rounded',
    //     'rounded-bl-none',
    //     'rounded-tr-none',
    //     'px-4',
    //     'py-2',
    //     'focus:outline-none',
    //     'focus:ring-2',
    //     'focus:ring-blue-500',
    //     'text-right',
    //     'text-white',
    //     'hover:bg-blue-600',
    //     'transition-colors',
    //     'items-center',
    //   ],

    plugins: [forms],
};
