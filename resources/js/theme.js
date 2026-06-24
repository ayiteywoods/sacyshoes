const THEME_STORAGE_KEY = 'sacyshoes-theme';

export function getStoredTheme() {
  try {
    return localStorage.getItem(THEME_STORAGE_KEY) === 'dark' ? 'dark' : 'light';
  } catch {
    return 'light';
  }
}

export function applyTheme(theme) {
  const isDark = theme === 'dark';
  document.documentElement.classList.toggle('dark', isDark);
  document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';

  try {
    localStorage.setItem(THEME_STORAGE_KEY, isDark ? 'dark' : 'light');
  } catch {
    // Ignore storage failures.
  }
}

export function initTheme() {
  applyTheme(getStoredTheme());
}

export function createThemeStore() {
  return {
    mode: getStoredTheme(),

    get isDark() {
      return this.mode === 'dark';
    },

    toggle() {
      this.mode = this.isDark ? 'light' : 'dark';
      applyTheme(this.mode);
    },

    set(mode) {
      this.mode = mode === 'dark' ? 'dark' : 'light';
      applyTheme(this.mode);
    },
  };
}

// Dark mode disabled for now
// initTheme();
document.documentElement.classList.remove('dark');
document.documentElement.style.colorScheme = 'light';
