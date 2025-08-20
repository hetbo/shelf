import React from 'react';

const StatusBar = ({ totalItems, selectedCount, currentPath }) => {
    return (
        <div className="shelf:bg-gray-800 shelf:text-white shelf:px-4 shelf:py-2 shelf:flex shelf:items-center shelf:justify-between shelf:border-t shelf:border-gray-700 shelf:text-sm">
            <div className="shelf:flex shelf:items-center shelf:space-x-4">
                <div className="shelf:text-gray-300">
                    {totalItems} {totalItems === 1 ? 'item' : 'items'}
                    {selectedCount > 0 && (
                        <span className="shelf:ml-2">
                            • {selectedCount} selected
                        </span>
                    )}
                </div>

                {currentPath && (
                    <div className="shelf:text-gray-400 shelf:text-xs">
                        /{currentPath}
                    </div>
                )}
            </div>

            <div className="shelf:flex shelf:items-center shelf:space-x-4">
                <div className="shelf:text-gray-400 shelf:text-xs">
                    Ready
                </div>
            </div>
        </div>
    );
};

export default StatusBar;