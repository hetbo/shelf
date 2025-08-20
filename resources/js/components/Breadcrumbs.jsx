import React from 'react';
import Icon from '../icons/Icon.jsx';

const Breadcrumbs = ({ breadcrumbs, onBreadcrumbClick }) => {
    if (!breadcrumbs || breadcrumbs.length === 0) {
        return (
            <div className="shelf:px-4 shelf:py-2 shelf:bg-white shelf:border-b shelf:border-gray-200 shelf:text-sm shelf:text-gray-600">
                <div className="shelf:flex shelf:items-center">
                    <Icon name="HomeIcon" className="shelf:w-4 shelf:h-4 shelf:mr-2" />
                    <span>Root</span>
                </div>
            </div>
        );
    }

    return (
        <div className="shelf:px-4 shelf:py-2 shelf:bg-white shelf:border-b shelf:border-gray-200 shelf:text-sm">
            <div className="shelf:flex shelf:items-center shelf:space-x-1 shelf:text-gray-600">
                <button
                    onClick={() => onBreadcrumbClick(null)}
                    className="shelf:flex shelf:items-center shelf:hover:text-blue-600 shelf:transition-colors"
                >
                    <Icon name="HomeIcon" className="shelf:w-4 shelf:h-4 shelf:mr-1" />
                    <span>Root</span>
                </button>

                {breadcrumbs.map((breadcrumb, index) => (
                    <div key={breadcrumb.id} className="shelf:flex shelf:items-center shelf:space-x-1">
                        <Icon name="ChevronRightIcon" className="shelf:w-3 shelf:h-3 shelf:text-gray-400" />

                        {index === breadcrumbs.length - 1 ? (
                            <span className="shelf:text-gray-900 shelf:font-medium">
                                {breadcrumb.name}
                            </span>
                        ) : (
                            <button
                                onClick={() => onBreadcrumbClick(breadcrumb.id)}
                                className="shelf:hover:text-blue-600 shelf:transition-colors"
                            >
                                {breadcrumb.name}
                            </button>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default Breadcrumbs;