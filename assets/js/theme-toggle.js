/*===== THEME TOGGLE FUNCTIONALITY =====*/

// Check for saved theme preference or default to light mode
const currentTheme = localStorage.getItem('theme') || 'light';

// Apply theme on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial theme
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        updateThemeIcon('dark');
    } else {
        document.body.classList.remove('dark-mode');
        updateThemeIcon('light');
    }
    
    // Add click event to theme toggle button
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});

// Toggle theme function
function toggleTheme() {
    const body = document.body;
    const themeToggle = document.getElementById('theme-toggle');
    
    // Add rotation animation
    themeToggle.classList.add('rotating');
    
    // Toggle dark mode class
    body.classList.toggle('dark-mode');
    
    // Determine current theme
    const theme = body.classList.contains('dark-mode') ? 'dark' : 'light';
    
    // Save to localStorage
    localStorage.setItem('theme', theme);
    
    // Update icon
    updateThemeIcon(theme);
    
    // Remove rotation animation after it completes
    setTimeout(() => {
        themeToggle.classList.remove('rotating');
    }, 500);
    
    // Optional: Show notification
    showThemeNotification(theme);
}

// Update theme icon
function updateThemeIcon(theme) {
    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
        if (theme === 'dark') {
            icon.className = 'fas fa-sun'; // Sun icon for dark mode (to switch to light)
        } else {
            icon.className = 'fas fa-moon'; // Moon icon for light mode (to switch to dark)
        }
    }
}

// Show theme change notification (optional)
function showThemeNotification(theme) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 12px 20px;
        background: ${theme === 'dark' ? '#1e293b' : '#ffffff'};
        color: ${theme === 'dark' ? '#f1f5f9' : '#0f172a'};
        border: 1px solid ${theme === 'dark' ? 'rgba(148,163,184,.15)' : 'rgba(2,6,23,.08)'};
        border-radius: 8px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    `;
    
    const icon = theme === 'dark' ? '🌙' : '☀️';
    const text = theme === 'dark' ? 'Dark mode enabled' : 'Light mode enabled';
    notification.innerHTML = `<span>${icon}</span><span>${text}</span>`;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Remove after 2 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}

// Add CSS animations for notification
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Keyboard shortcut: Ctrl/Cmd + Shift + D to toggle theme
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        toggleTheme();
    }
});

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { toggleTheme, updateThemeIcon };
}
