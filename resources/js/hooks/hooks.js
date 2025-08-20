import { useState, useEffect, useCallback, useRef } from 'react';
import { getConfig } from './config';

// Hook for managing drag and drop file upload
export const useDragAndDrop = (onFilesDropped) => {
    const [isDragging, setIsDragging] = useState(false);
    const [dragCounter, setDragCounter] = useState(0);

    const handleDragEnter = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragCounter(prev => prev + 1);
        if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
            setIsDragging(true);
        }
    }, []);

    const handleDragLeave = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragCounter(prev => {
            const newCounter = prev - 1;
            if (newCounter === 0) {
                setIsDragging(false);
            }
            return newCounter;
        });
    }, []);

    const handleDragOver = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
    }, []);

    const handleDrop = useCallback((e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
        setDragCounter(0);

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            onFilesDropped(e.dataTransfer.files);
        }
    }, [onFilesDropped]);

    return {
        isDragging,
        dragProps: {
            onDragEnter: handleDragEnter,
            onDragLeave: handleDragLeave,
            onDragOver: handleDragOver,
            onDrop: handleDrop,
        }
    };
};

// Hook for keyboard shortcuts
export const useKeyboardShortcuts = (shortcuts) => {
    useEffect(() => {
        if (!getConfig('ui.keyboardShortcuts', true)) return;

        const handleKeyDown = (event) => {
            // Don't trigger shortcuts when typing in inputs
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                return;
            }

            const key = event.key.toLowerCase();
            const ctrl = event.ctrlKey || event.metaKey;
            const shift = event.shiftKey;
            const alt = event.altKey;

            for (const shortcut of shortcuts) {
                if (
                    shortcut.key === key &&
                    shortcut.ctrl === ctrl &&
                    shortcut.shift === shift &&
                    shortcut.alt === alt
                ) {
                    event.preventDefault();
                    shortcut.handler();
                    return;
                }
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [shortcuts]);
};

// Hook for auto-refresh functionality
export const useAutoRefresh = (callback, enabled = false) => {
    const intervalRef = useRef();
    const callbackRef = useRef(callback);

    // Update callback ref when callback changes
    useEffect(() => {
        callbackRef.current = callback;
    }, [callback]);

    useEffect(() => {
        if (!enabled || !getConfig('ui.autoRefresh', false)) {
            return;
        }

        const interval = getConfig('ui.autoRefreshInterval', 30000);

        intervalRef.current = setInterval(() => {
            callbackRef.current();
        }, interval);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [enabled]);
};

// Hook for debouncing values (useful for search)
export const useDebounce = (value, delay) => {
    const [debouncedValue, setDebouncedValue] = useState(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
};

// Hook for managing local storage with JSON serialization
export const useLocalStorage = (key, initialValue) => {
    // Get from local storage then parse stored json or return initialValue
    const [storedValue, setStoredValue] = useState(() => {
        try {
            const item = window.localStorage.getItem(key);
            return item ? JSON.parse(item) : initialValue;
        } catch (error) {
            console.warn(`Error reading localStorage key "${key}":`, error);
            return initialValue;
        }
    });

    // Return a wrapped version of useState's setter function that persists the new value to localStorage
    const setValue = useCallback((value) => {
        try {
            // Allow value to be a function so we have the same API as useState
            const valueToStore = value instanceof Function ? value(storedValue) : value;
            setStoredValue(valueToStore);
            window.localStorage.setItem(key, JSON.stringify(valueToStore));
        } catch (error) {
            console.warn(`Error setting localStorage key "${key}":`, error);
        }
    }, [key, storedValue]);

    return [storedValue, setValue];
};

// Hook for managing modal/dialog state
export const useModal = (initialState = false) => {
    const [isOpen, setIsOpen] = useState(initialState);

    const open = useCallback(() => setIsOpen(true), []);
    const close = useCallback(() => setIsOpen(false), []);
    const toggle = useCallback(() => setIsOpen(prev => !prev), []);

    return { isOpen, open, close, toggle };
};

// Hook for managing loading states
export const useAsyncOperation = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const execute = useCallback(async (asyncFunction) => {
        try {
            setLoading(true);
            setError(null);
            const result = await asyncFunction();
            return result;
        } catch (err) {
            setError(err.message || 'An error occurred');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    const reset = useCallback(() => {
        setLoading(false);
        setError(null);
    }, []);

    return { loading, error, execute, reset };
};

// Hook for handling click outside element
export const useClickOutside = (ref, callback) => {
    useEffect(() => {
        const handleClick = (event) => {
            if (ref.current && !ref.current.contains(event.target)) {
                callback();
            }
        };

        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, [ref, callback]);
};

// Hook for managing previous value
export const usePrevious = (value) => {
    const ref = useRef();

    useEffect(() => {
        ref.current = value;
    });

    return ref.current;
};

// Hook for managing window size
export const useWindowSize = () => {
    const [windowSize, setWindowSize] = useState({
        width: undefined,
        height: undefined,
    });

    useEffect(() => {
        const handleResize = () => {
            setWindowSize({
                width: window.innerWidth,
                height: window.innerHeight,
            });
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Call handler right away so state gets updated with initial window size

        return () => window.removeEventListener('resize', handleResize);
    }, []);

    return windowSize;
};

// Hook for managing intersection observer (useful for lazy loading)
export const useIntersectionObserver = (elementRef, options) => {
    const [isIntersecting, setIsIntersecting] = useState(false);

    useEffect(() => {
        const element = elementRef.current;
        if (!element) return;

        const observer = new IntersectionObserver(([entry]) => {
            setIsIntersecting(entry.isIntersecting);
        }, options);

        observer.observe(element);

        return () => observer.disconnect();
    }, [elementRef, options]);

    return isIntersecting;
};

// Hook for file validation
export const useFileValidation = () => {
    const validateFiles = useCallback((files) => {
        const errors = [];
        const validFiles = [];

        Array.from(files).forEach((file, index) => {
            // Check file size
            const maxSize = getConfig('files.maxUploadSize', 0);
            if (file.size > maxSize) {
                errors.push({
                    file: file.name,
                    error: `File size exceeds maximum allowed size of ${(maxSize / 1024 / 1024).toFixed(2)}MB`
                });
                return;
            }

            // Check file type
            const allowedTypes = getConfig('files.allowedTypes', []);
            if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
                errors.push({
                    file: file.name,
                    error: 'File type is not allowed'
                });
                return;
            }

            validFiles.push(file);
        });

        return { validFiles, errors };
    }, []);

    return { validateFiles };
};