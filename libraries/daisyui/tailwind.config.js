/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    '../../themes/contrib/ui_suite_daisyui/dist/css/custom/*.css',
    '../../themes/contrib/ui_suite_daisyui/templates/*.{twig,js}',
    '../../themes/contrib/ui_suite_daisyui/templates/**/*.{twig,js}',
    '../../themes/contrib/ui_suite_daisyui/components/**/*.{twig,js,yml}',
    '../../themes/contrib/ui_suite_daisyui/*.ui_styles.yml',
    '../../themes/contrib/ui_suite_daisyui/ui_examples/*.yml',
  ],
  safelist: [
    'alert-info', 'alert-success', 'alert-warning', 'alert-error',
    'artboard-horizontal',
    'phone-1', 'phone-2', 'phone-3', 'phone-4', 'phone-5', 'phone-6',
    'badge-neutral', 'badge-primary', 'badge-secondary', 'badge-accent', 'badge-ghost', 'badge-info', 'badge-success', 'badge-warning', 'badge-error',
    'badge-xs', 'badge-sm', 'badge-md', 'badge-lg',
    'btn-neutral', 'btn-primary', 'btn-secondary', 'btn-accent', 'btn-ghost', 'btn-link', 'btn-info', 'btn-success', 'btn-warning', 'btn-error',
    'btn-xs', 'btn-sm', 'btn-md', 'btn-lg',
    'card-compact', 'card-side',
    'carousel-center', 'carousel-end',
    'chat-end',
    'chat-bubble-primary', 'chat-bubble-secondary', 'chat-bubble-accent', 'chat-bubble-info', 'chat-bubble-success', 'chat-bubble-warning', 'chat-bubble-error',
    'divider-horizontal',
    'divider-start', 'divider-end',
    'divider-neutral', 'divider-primary', 'divider-secondary', 'divider-accent', 'divider-success', 'divider-warning', 'divider-info', 'divider-error',
    'link-primary', 'link-secondary', 'link-accent', 'link-neutral', 'link-success', 'link-info', 'link-warning', 'link-error',
    'loading-spinner', 'loading-dots', 'loading-ring', 'loading-ball', 'loading-bars', 'loading-infinity',
    'loading-xs', 'loading-sm', 'loading-md', 'loading-lg',
    'menu-vertical', 'menu-horizontal',
    'menu-xs', 'menu-sm', 'menu-md', 'menu-lg',
    'progress-primary', 'progress-secondary', 'progress-accent', 'progress-info', 'progress-success', 'progress-warning', 'progress-error',
    'stats-vertical',
    'step-neutral', 'step-primary', 'step-secondary', 'step-accent', 'step-info', 'step-success', 'step-warning',  'step-error',
    'steps-vertical',
    'table-xs', 'table-sm', 'table-md', 'table-lg',
    'tabs-bordered', 'tabs-lifted', 'tabs-boxed',
    'tabs-xs', 'tabs-sm', 'tabs-lg',
    'timeline-vertical',
    'toast-start', 'toast-center', 'toast-end', 'toast-top', 'toast-middle', 'toast-bottom',
  ],
  theme: {
    extend: {}
  },
  plugins: [
    require("daisyui"),
    require('@tailwindcss/typography'),
  ],
  daisyui: {
    themes: [
      "light",
      "dark",
      "cupcake",
      "bumblebee",
      "emerald",
      "corporate",
      "synthwave",
      "retro",
      "cyberpunk",
      "valentine",
      "halloween",
      "garden",
      "forest",
      "aqua",
      "lofi",
      "pastel",
      "fantasy",
      "wireframe",
      "black",
      "luxury",
      "dracula",
      "cmyk",
      "autumn",
      "business",
      "acid",
      "lemonade",
      "night",
      "coffee",
      "winter",
      "dim",
      "nord",
      "sunset",
    ],
    darkTheme: "dark", // name of one of the included themes for dark mode
    base: true, // applies background color and foreground color for root element by default
    styled: true, // include daisyUI colors and design decisions for all components
    utils: true, // adds responsive and modifier utility classes
    prefix: "", // prefix for daisyUI classnames (components, modifiers and responsive class names. Not colors)
    logs: true, // Shows info about daisyUI version and used config in the console when building your CSS
    themeRoot: ":root", // The element that receives theme color CSS variables
  }
};
