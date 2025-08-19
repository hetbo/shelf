<div class="shelf:flex-1 shelf:bg-white shelf:overflow-hidden shelf:flex shelf:flex-col" id="file-list">
    {{-- View Options --}}
    <div class="shelf:border-b shelf:border-gray-200 shelf:px-6 shelf:py-3 shelf:bg-gray-50">
        <div class="shelf:flex shelf:items-center shelf:justify-between">
            <div class="shelf:flex shelf:items-center shelf:space-x-4">
                <span class="shelf:text-sm shelf:text-gray-700">52 items</span>
                <div class="shelf:flex shelf:items-center shelf:space-x-1">
                    <button class="shelf:p-1 shelf:text-blue-600 shelf:bg-blue-50 shelf:rounded">
                        <x-my-grid class="shelf:w-4 shelf:h-4"/>
                    </button>
                    <button class="shelf:p-1 shelf:text-gray-500 hover:shelf:text-blue-600 hover:shelf:bg-blue-50 shelf:rounded">
                        <x-my-list class="shelf:w-4 shelf:h-4"/>
                    </button>
                </div>
            </div>
            <div class="shelf:flex shelf:items-center shelf:space-x-2 shelf:text-sm">
                <span class="shelf:text-gray-600">Sort by:</span>
                <select class="shelf:border shelf:border-gray-300 shelf:rounded shelf:px-2 shelf:py-1 shelf:text-sm focus:shelf:outline-none focus:shelf:border-blue-500"
                        hx-get="/file-manager/sort"
                        hx-target="#file-list">
                    <option>Name</option>
                    <option>Date modified</option>
                    <option>Size</option>
                    <option>Type</option>
                </select>
            </div>
        </div>
    </div>

    {{-- File Grid --}}
    <div class="shelf:flex-1 shelf:overflow-y-auto shelf:p-6">
        <div class="shelf:grid shelf:grid-cols-8 shelf:gap-4">
            {{-- Folder Items --}}
            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/folder/projects/webapp"
                 hx-target="#file-list"
                 hx-get="/file-manager/select/folder/webapp"
                 hx-target="#file-details">
                <x-my-folder class="shelf:w-12 shelf:h-12 shelf:text-yellow-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">Web App</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/folder/projects/mobile"
                 hx-target="#file-list"
                 hx-get="/file-manager/select/folder/mobile"
                 hx-target="#file-details">
                <x-my-folder class="shelf:w-12 shelf:h-12 shelf:text-yellow-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">Mobile App</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/folder/projects/designs"
                 hx-target="#file-list"
                 hx-get="/file-manager/select/folder/designs"
                 hx-target="#file-details">
                <x-my-folder class="shelf:w-12 shelf:h-12 shelf:text-yellow-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">Designs</span>
            </div>

            {{-- File Items --}}
            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/proposal.pdf"
                 hx-target="#file-details">
                <x-my-file-text class="shelf:w-12 shelf:h-12 shelf:text-red-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">proposal.pdf</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group shelf:bg-blue-50 shelf:border shelf:border-blue-200"
                 hx-get="/file-manager/select/file/budget.xlsx"
                 hx-target="#file-details">
                <x-my-file-spreadsheet class="shelf:w-12 shelf:h-12 shelf:text-green-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-blue-700">budget.xlsx</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/presentation.pptx"
                 hx-target="#file-details">
                <x-my-presentation class="shelf:w-12 shelf:h-12 shelf:text-orange-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">presentation.pptx</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/screenshot.png"
                 hx-target="#file-details">
                <x-my-image class="shelf:w-12 shelf:h-12 shelf:text-purple-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">screenshot.png</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/demo.mp4"
                 hx-target="#file-details">
                <x-my-video class="shelf:w-12 shelf:h-12 shelf:text-blue-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">demo.mp4</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/notes.txt"
                 hx-target="#file-details">
                <x-my-file-text class="shelf:w-12 shelf:h-12 shelf:text-gray-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">notes.txt</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/archive.zip"
                 hx-target="#file-details">
                <x-my-archive class="shelf:w-12 shelf:h-12 shelf:text-indigo-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">archive.zip</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/config.json"
                 hx-target="#file-details">
                <x-my-file-code class="shelf:w-12 shelf:h-12 shelf:text-yellow-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">config.json</span>
            </div>

            <div class="shelf:flex shelf:flex-col shelf:items-center shelf:p-3 shelf:rounded shelf:cursor-pointer hover:shelf:bg-blue-50 shelf:group"
                 hx-get="/file-manager/select/file/readme.md"
                 hx-target="#file-details">
                <x-my-file-text class="shelf:w-12 shelf:h-12 shelf:text-blue-600 shelf:mb-2"/>
                <span class="shelf:text-sm shelf:text-center shelf:text-gray-700 group-hover:shelf:text-blue-700">readme.md</span>
            </div>
        </div>
    </div>
</div>