import React, { useMemo } from 'react';
import Icon from '../icons/Icon.jsx';

const FileIcon = ({ file }) => {
    const getFileIcon = () => {
        if (!file.mime_type) return "DocumentIcon";

        if (file.mime_type.startsWith('image/')) return "PhotoIcon";
        if (file.mime_type.startsWith('video/')) return "VideoCameraIcon";
        if (file.mime_type.startsWith('audio/')) return "SpeakerWaveIcon";
        if (file.mime_type === 'application/pdf') return "DocumentTextIcon";
        if (file.mime_type.includes('zip') || file.mime_type.includes('archive')) return "ArchiveBoxIcon";
        if (file.mime_type.includes('text')) return "DocumentTextIcon";

        return "DocumentIcon";
    };

    const getIconColor = () => {
        if (!file.mime_type) return "shelf:text-gray-500";

        if (file.mime_type.startsWith('image/')) return "shelf:text-green-500";
        if (file.mime_type.startsWith('video/')) return "shelf:text-purple-500";
        if (file.mime_type.startsWith('audio/')) return "shelf:text-blue-500";
        if (file.mime_type === 'application/pdf') return "shelf:text-red-500";
        if (file.mime_type.includes('zip') || file.mime_type.includes('archive')) return "shelf:text-orange-500";
        if (file.mime_type.includes('text')) return "shelf:text-gray-600";

        return "shelf:text-gray-500";
    };

    return (
        <Icon
            name={getFileIcon()}
            className={`shelf:w-5 shelf:h-5 ${getIconColor()}`}
        />
    );
};

const GridItem = ({ item, isSelected, onSelect, onDoubleClick }) => {
    const handleClick = (e) => {
        onSelect(item, e.ctrlKey || e.metaKey);
    };

    const handleDoubleClick = () => {
        onDoubleClick(item);
    };

    const formatSize = (bytes) => {
        if (!bytes) return '';
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(1)} ${units[unitIndex]}`;
    };

    return (
        <div
            className={`shelf:p-3 shelf:rounded shelf:cursor-pointer shelf:transition-colors shelf:border-2 ${
                isSelected
                    ? 'shelf:bg-blue-100 shelf:border-blue-300'
                    : 'shelf:border-transparent shelf:hover:bg-gray-50 shelf:hover:border-gray-200'
            }`}
            onClick={handleClick}
            onDoubleClick={handleDoubleClick}
        >
            <div className="shelf:flex shelf:flex-col shelf:items-center shelf:text-center">
                <div className="shelf:mb-2">
                    {item.type === 'folder' ? (
                        <Icon
                            name="FolderIcon"
                            className="shelf:w-12 shelf:h-12 shelf:text-blue-500"
                        />
                    ) : (
                        <div className="shelf:w-12 shelf:h-12 shelf:flex shelf:items-center shelf:justify-center">
                            <FileIcon file={item} />
                        </div>
                    )}
                </div>

                <div className="shelf:text-sm shelf:font-medium shelf:text-gray-900 shelf:truncate shelf:w-full shelf:mb-1">
                    {item.type === 'folder' ? item.name : item.filename}
                </div>

                {item.type === 'file' && item.size && (
                    <div className="shelf:text-xs shelf:text-gray-500">
                        {formatSize(item.size)}
                    </div>
                )}
            </div>
        </div>
    );
};

const ListItem = ({ item, isSelected, onSelect, onDoubleClick }) => {
    const handleClick = (e) => {
        onSelect(item, e.ctrlKey || e.metaKey);
    };

    const handleDoubleClick = () => {
        onDoubleClick(item);
    };

    const formatSize = (bytes) => {
        if (!bytes) return '-';
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(1)} ${units[unitIndex]}`;
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString();
    };

    return (
        <div
            className={`shelf:flex shelf:items-center shelf:px-3 shelf:py-2 shelf:cursor-pointer shelf:transition-colors shelf:border-l-2 ${
                isSelected
                    ? 'shelf:bg-blue-50 shelf:border-blue-500'
                    : 'shelf:border-transparent shelf:hover:bg-gray-50'
            }`}
            onClick={handleClick}
            onDoubleClick={handleDoubleClick}
        >
            <div className="shelf:flex-shrink-0 shelf:mr-3">
                {item.type === 'folder' ? (
                    <Icon
                        name="FolderIcon"
                        className="shelf:w-5 shelf:h-5 shelf:text-blue-500"
                    />
                ) : (
                    <FileIcon file={item} />
                )}
            </div>

            <div className="shelf:flex-1 shelf:min-w-0">
                <div className="shelf:text-sm shelf:font-medium shelf:text-gray-900 shelf:truncate">
                    {item.type === 'folder' ? item.name : item.filename}
                </div>
            </div>

            <div className="shelf:w-20 shelf:text-xs shelf:text-gray-500 shelf:text-right">
                {item.type === 'file' ? formatSize(item.size) : '-'}
            </div>

            <div className="shelf:w-32 shelf:text-xs shelf:text-gray-500 shelf:text-right shelf:ml-4">
                {formatDate(item.updated_at)}
            </div>
        </div>
    );
};

const FileList = ({
                      folders,
                      files,
                      selectedItems,
                      onItemSelect,
                      onItemDoubleClick,
                      viewMode,
                      sortBy,
                      sortOrder,
                      loading
                  }) => {
    const sortedItems = useMemo(() => {
        const allItems = [
            ...folders.map(folder => ({ ...folder, type: 'folder' })),
            ...files.map(file => ({ ...file, type: 'file' }))
        ];

        return allItems.sort((a, b) => {
            // Always show folders first
            if (a.type !== b.type) {
                return a.type === 'folder' ? -1 : 1;
            }

            let aValue, bValue;

            switch (sortBy) {
                case 'size':
                    aValue = a.size || 0;
                    bValue = b.size || 0;
                    break;
                case 'modified':
                    aValue = new Date(a.updated_at || 0);
                    bValue = new Date(b.updated_at || 0);
                    break;
                case 'type':
                    aValue = a.mime_type || '';
                    bValue = b.mime_type || '';
                    break;
                case 'name':
                default:
                    aValue = (a.name || a.filename || '').toLowerCase();
                    bValue = (b.name || b.filename || '').toLowerCase();
                    break;
            }

            let result = 0;
            if (aValue < bValue) result = -1;
            if (aValue > bValue) result = 1;

            return sortOrder === 'desc' ? -result : result;
        });
    }, [folders, files, sortBy, sortOrder]);

    const isSelected = (item) => {
        return selectedItems.some(selected =>
            selected.id === item.id && selected.type === item.type
        );
    };

    if (loading) {
        return (
            <div className="shelf:flex-1 shelf:flex shelf:items-center shelf:justify-center shelf:bg-white">
                <div className="shelf:flex shelf:items-center shelf:space-x-2 shelf:text-gray-500">
                    <Icon name="ArrowPathIcon" className="shelf:w-5 shelf:h-5 shelf:animate-spin" />
                    <span>Loading...</span>
                </div>
            </div>
        );
    }

    if (sortedItems.length === 0) {
        return (
            <div className="shelf:flex-1 shelf:flex shelf:items-center shelf:justify-center shelf:bg-white">
                <div className="shelf:text-center shelf:text-gray-500">
                    <Icon name="FolderIcon" className="shelf:w-16 shelf:h-16 shelf:mx-auto shelf:mb-4 shelf:text-gray-300" />
                    <p className="shelf:text-lg shelf:mb-2">This folder is empty</p>
                    <p className="shelf:text-sm">Upload files or create folders to get started</p>
                </div>
            </div>
        );
    }

    if (viewMode === 'grid') {
        return (
            <div className="shelf:flex-1 shelf:bg-white shelf:p-4 shelf:overflow-y-auto">
                <div className="shelf:grid shelf:grid-cols-6 shelf:gap-4">
                    {sortedItems.map(item => (
                        <GridItem
                            key={`${item.type}-${item.id}`}
                            item={item}
                            isSelected={isSelected(item)}
                            onSelect={onItemSelect}
                            onDoubleClick={onItemDoubleClick}
                        />
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="shelf:flex-1 shelf:bg-white shelf:flex shelf:flex-col">
            <div className="shelf:flex shelf:items-center shelf:px-3 shelf:py-2 shelf:bg-gray-50 shelf:border-b shelf:border-gray-200 shelf:text-xs shelf:font-medium shelf:text-gray-500">
                <div className="shelf:flex-1">Name</div>
                <div className="shelf:w-20 shelf:text-right">Size</div>
                <div className="shelf:w-32 shelf:text-right shelf:ml-4">Modified</div>
            </div>

            <div className="shelf:flex-1 shelf:overflow-y-auto">
                {sortedItems.map(item => (
                    <ListItem
                        key={`${item.type}-${item.id}`}
                        item={item}
                        isSelected={isSelected(item)}
                        onSelect={onItemSelect}
                        onDoubleClick={onItemDoubleClick}
                    />
                ))}
            </div>
        </div>
    );
};

export default FileList;