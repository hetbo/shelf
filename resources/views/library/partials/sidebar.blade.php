<div class="shelf:w-64 shelf:bg-gray-50 shelf:border-r shelf:border-gray-200 shelf:overflow-y-auto">
    <div class="shelf:p-4">
        <h3 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-3">Folders</h3>

        <div class="shelf:space-y-1">
            {{-- Root Folder --}}
            <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-blue-600 shelf:bg-blue-50 shelf:rounded">
                <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2"/>
                <span class="shelf:font-medium">Root</span>
            </div>

            {{-- Documents Folder --}}
            <div class="shelf:ml-4">
                <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                     hx-get="/file-manager/folder/documents"
                     hx-target="#file-list">
                    <x-my-chevron-right class="shelf:w-3 shelf:h-3 shelf:mr-1"/>
                    <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                    <span>Documents</span>
                </div>

                {{-- Documents Subfolders --}}
                <div class="shelf:ml-4 shelf:space-y-1">
                    <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                         hx-get="/file-manager/folder/documents/projects"
                         hx-target="#file-list">
                        <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                        <span>Projects</span>
                    </div>
                    <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                         hx-get="/file-manager/folder/documents/reports"
                         hx-target="#file-list">
                        <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                        <span>Reports</span>
                    </div>
                </div>
            </div>

            {{-- Images Folder --}}
            <div class="shelf:ml-4">
                <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                     hx-get="/file-manager/folder/images"
                     hx-target="#file-list">
                    <x-my-chevron-right class="shelf:w-3 shelf:h-3 shelf:mr-1"/>
                    <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                    <span>Images</span>
                </div>

                {{-- Images Subfolders --}}
                <div class="shelf:ml-4 shelf:space-y-1">
                    <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                         hx-get="/file-manager/folder/images/photos"
                         hx-target="#file-list">
                        <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                        <span>Photos</span>
                    </div>
                    <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                         hx-get="/file-manager/folder/images/graphics"
                         hx-target="#file-list">
                        <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                        <span>Graphics</span>
                    </div>
                </div>
            </div>

            {{-- Videos Folder --}}
            <div class="shelf:ml-4">
                <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                     hx-get="/file-manager/folder/videos"
                     hx-target="#file-list">
                    <x-my-chevron-down class="shelf:w-3 shelf:h-3 shelf:mr-1"/>
                    <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                    <span>Videos</span>
                </div>
            </div>

            {{-- Downloads Folder --}}
            <div class="shelf:ml-4">
                <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-700 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer"
                     hx-get="/file-manager/folder/downloads"
                     hx-target="#file-list">
                    <x-my-chevron-right class="shelf:w-3 shelf:h-3 shelf:mr-1"/>
                    <x-my-folder class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-600"/>
                    <span>Downloads</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access --}}
    <div class="shelf:border-t shelf:border-gray-200 shelf:p-4">
        <h4 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-3">Quick Access</h4>
        <div class="shelf:space-y-2">
            <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-600 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer">
                <x-my-star class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-yellow-500"/>
                <span>Favorites</span>
            </div>
            <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-600 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer">
                <x-my-clock class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-gray-500"/>
                <span>Recent</span>
            </div>
            <div class="shelf:flex shelf:items-center shelf:p-2 shelf:text-sm shelf:text-gray-600 hover:shelf:bg-gray-100 shelf:rounded shelf:cursor-pointer">
                <x-my-trash class="shelf:w-4 shelf:h-4 shelf:mr-2 shelf:text-red-500"/>
                <span>Trash</span>
            </div>
        </div>
    </div>
</div>