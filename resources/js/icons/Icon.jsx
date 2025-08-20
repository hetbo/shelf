import React, { Suspense } from 'react';
import * as Icons from './index'; // Import all icons from our barrel file

const Icon = ({ name, className = '' }) => {
    // Find the specific icon component from the imported list based on the 'name' prop
    const IconComponent = Icons[name];

    if (!IconComponent) {
        // Handle cases where the icon name is invalid
        console.warn(`Icon "${name}" not found.`);
        return null;
    }

    // Set default styles that can be overridden by the passed `className`
    const defaultClasses = 'shelf:h-5 shelf:w-5';

    return (
        // Suspense is a fallback for if the icon component is code-splitted (advanced)
        <Suspense fallback={null}>
            <IconComponent className={`${defaultClasses} ${className}`} />
        </Suspense>
    );
};

export default Icon;