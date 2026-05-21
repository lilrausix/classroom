const themeToggleButton = document.getElementById('theme-toggle');
if (themeToggleButton) {
    const storedTheme = localStorage.getItem('theme') || 'dark';
    const lightMode = storedTheme === 'light';

    document.documentElement.classList.toggle('light', lightMode);

    const updateButton = () => {
        const isLight = document.documentElement.classList.contains('light');
        themeToggleButton.textContent = isLight ? '☀️ Gaišā' : '🌙 Tumšā';
        themeToggleButton.setAttribute('aria-label', isLight ? 'Pāriet uz tumšo režīmu' : 'Pāriet uz gaišo režīmu');
    };

    themeToggleButton.addEventListener('click', () => {
        const isLight = document.documentElement.classList.toggle('light');
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        updateButton();
    });

    updateButton();
}
