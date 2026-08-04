export default {
  plugins: {
    'postcss-mixins': {
      mixins: {
        /**
         * @example @mixin transition color background-color;
         * @example @mixin transition transform color, 200ms, ease;
         */
        transition(
          mixin,
          properties,
          duration = 'var(--duration-tortoise)',
          timing = 'var(--ease-in-out)',
        ) {
          const props = String(properties).trim().split(/\s+/).join(', ')

          mixin.replaceWith(
            `transition-property: ${props};\n` +
              `transition-duration: ${duration};\n` +
              `transition-timing-function: ${timing};`,
          )
        },
      },
    },
  },
}
