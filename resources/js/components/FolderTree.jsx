import React, { useState } from 'react';
import Icon from '../icons/Icon.jsx';

const FolderTreeItem = ({ folder, currentFolderId, onFolderSelect, level = 0 }) => {
    const [isExpanded, setIsExpanded] = useState(folder.expanded || false);
    const hasChildren = folder.children && folder.children.length > 0;
    const isSelected = folder.id === currentFolderId;

    const handleToggleExpanded = (e) => {
        e.stopPropagation();
        setIsExpanded(!isExpanded);
    };

    const handleFolderClick = () => {
        onFolderSelect(folder.id);
    };

    return (
        <div>
            <div
                className={`shelf:flex shelf:items-center shelf:px-2 shelf:py-1 shelf:cursor-pointer shelf:hover:bg-gray-100 shelf:transition-colors ${
                    isSelected ? 'shelf:bg-blue-100 shelf:text-blue-800' : 'shelf:text-gray-700'
                }`}
                style={{ paddingLeft: `${level * 16 + 8}px` }}
                onClick={handleFolderClick}
            >
                {hasChildren ? (
                    <button
                        onClick={handleToggleExpanded}
                        className="shelf:p-0.5 shelf:mr-1 shelf:hover:bg-gray-200 shelf:rounded shelf:transition-colors"
                    >
                        <Icon
                            name={isExpanded ? "ChevronDownIcon" : "ChevronRightIcon"}
                            className="shelf:w-3 shelf:h-3"
                        />
                    </button>
                ) : (
                    <div className="shelf:w-4 shelf:mr-1" />
                )}

                <Icon
                    name={isExpanded ? "FolderOpenIcon" : "FolderIcon"}
                    className={`shelf:w-4 shelf:h-4 shelf:mr-2 ${
                        isSelected ? 'shelf:text-blue-600' : 'shelf:text-gray-500'
                    }`}
                />

                <span className="shelf:text-sm shelf:truncate shelf:flex-1">
                    {folder.name}
                </span>
            </div>

            {isExpanded && hasChildren && (
                <div>
                    {folder.children.map(child => (
                        <FolderTreeItem
                            key={child.id}
                            folder={child}
                            currentFolderId={currentFolderId}
                            onFolderSelect={onFolderSelect}
                            level={level + 1}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

const FolderTree = ({ folders, currentFolderId, onFolderSelect }) => {
    return (
        <div className="shelf:h-full shelf:overflow-y-auto">
            <div className="shelf:px-3 shelf:py-2 shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider shelf:border-b shelf:border-gray-200">
                Folders
            </div>

            <div className="shelf:py-2">
                {/* Root folder */}
                <div
                    className={`shelf:flex shelf:items-center shelf:px-2 shelf:py-1 shelf:cursor-pointer shelf:hover:bg-gray-100 shelf:transition-colors ${
                        currentFolderId === null ? 'shelf:bg-blue-100 shelf:text-blue-800' : 'shelf:text-gray-700'
                    }`}
                    style={{ paddingLeft: '8px' }}
                    onClick={() => onFolderSelect(null)}
                >
                    <Icon
                        name="HomeIcon"
                        className={`shelf:w-4 shelf:h-4 shelf:mr-2 ${
                            currentFolderId === null ? 'shelf:text-blue-600' : 'shelf:text-gray-500'
                        }`}
                    />
                    <span className="shelf:text-sm shelf:truncate shelf:flex-1">
                        Root
                    </span>
                </div>

                {/* Folder tree */}
                {folders && folders.map(folder => (
                    <FolderTreeItem
                        key={folder.id}
                        folder={folder}
                        currentFolderId={currentFolderId}
                        onFolderSelect={onFolderSelect}
                        level={0}
                    />
                ))}

                {(!folders || folders.length === 0) && (
                    <div className="shelf:px-4 shelf:py-8 shelf:text-center shelf:text-gray-500 shelf:text-sm">
                        No folders found
                    </div>
                )}
            </div>
        </div>
    );
};

export default FolderTree;