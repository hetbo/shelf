{{-- resources/views/file-manager/partials/header.blade.php --}}
<div class="shelf:bg-white shelf:border-b shelf:border-gray-200 shelf:px-6 shelf:py-4">
    <div class="shelf:flex shelf:items-center shelf:justify-between">
        {{-- Navigation & Breadcrumbs --}}
        <div class="shelf:flex shelf:items-center shelf:space-x-4">
            <div class="shelf:flex shelf:items-center shelf:space-x-2">
                <button class="shelf:p-2 shelf:text-gray-500 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded"
                        hx-get="/file-manager/back" hx-target="#file-list">
                    <x-my-arrow-left class="shelf:w-4 shelf:h-4"/>
                </button>
                <button class="shelf:p-2 shelf:text-gray-500 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded"
                        hx-get="/file-manager/forward" hx-target="#file-list">
                    <x-my-arrow-right class="shelf:w-4 shelf:h-4"/>
                </button>
                <button class="shelf:p-2 shelf:text-gray-500 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded"
                        hx-get="/file-manager/refresh" hx-target="#file-list">
                    <x-my-refresh class="shelf:w-4 shelf:h-4"/>
                </button>
            </div>

            {{-- Breadcrumb --}}
            <div class="shelf:flex shelf:items-center shelf:text-sm shelf:text-gray-600">
                <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2"/>
                <span class="shelf:font-medium">Home</span>
                <x-my-chevron-right class="shelf:w-3 shelf:h-3 shelf:mx-1"/>
                <span>Documents</span>
                <x-my-chevron-right class="shelf:w-3 shelf:h-3 shelf:mx-1"/>
                <span class="shelf:text-blue-600">Projects</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="shelf:flex shelf:items-center shelf:space-x-2">
            <button class="shelf:px-3 shelf:py-2 shelf:text-sm shelf:text-gray-700 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded shelf:flex shelf:items-center shelf:space-x-2"
                    hx-get="/file-manager/upload" hx-target="#modal-container">
                <x-my-upload class="shelf:w-4 shelf:h-4"/>
                <span>Upload</span>
            </button>
            <button class="shelf:px-3 shelf:py-2 shelf:text-sm shelf:text-gray-700 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded shelf:flex shelf:items-center shelf:space-x-2"
                    hx-get="/file-manager/create-folder" hx-target="#modal-container">
                <x-my-folder-plus class="shelf:w-4 shelf:h-4"/>
                <span>New Folder</span>
            </button>
            <div class="shelf:border-l shelf:border-gray-200 shelf:h-6 shelf:mx-2"></div>
            <div class="shelf:relative">
                <input type="text"
                       placeholder="Search files..."
                       class="shelf:pl-8 shelf:pr-3 shelf:py-2 shelf:text-sm shelf:border shelf:border-gray-300 shelf:rounded shelf:w-64 focus:shelf:outline-none focus:shelf:border-blue-500"
                       hx-get="/file-manager/search"
                       hx-trigger="keyup changed delay:300ms"
                       hx-target="#file-list">
                <x-my-search class="shelf:w-4 shelf:h-4 shelf:absolute shelf:left-2 shelf:top-3 shelf:text-gray-400"/>
            </div>
        </div>
    </div>
</div>