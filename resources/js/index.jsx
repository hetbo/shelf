import React from 'react';
import { createRoot } from 'react-dom/client';
import Shelf from './components/Shelf';

// Auto-mount when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('shelf-root');
    if (container) {
        const root = createRoot(container);
        root.render(<Shelf />);
    }
});