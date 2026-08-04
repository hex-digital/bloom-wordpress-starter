// Eager glob: Bloom CSS must be static graph deps so Vite extracts them with the
// entry CSS/JS and the browser loads them on first paint (async import() caused FOUC).
import.meta.glob(
  ['../../Bloom/**/*.css', '!../../Bloom/**/editor-*.css'],
  { eager: true },
);
