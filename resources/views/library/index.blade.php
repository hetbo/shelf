<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shelf</title>

    <link href="{{ route('shelf.assets.css') }}" rel="stylesheet">
    <script src="{{ route('shelf.assets.js') }}" defer></script>
</head>


<body class="shelf:bg-gray-50 shelf:font-sans shelf:text-gray-800">
<div class="shelf:flex shelf:flex-col shelf:h-screen">
    {{-- Header --}}
    @include('shelf::library.partials.header')

    {{-- Main Content --}}
{{--
    <div class="shelf:flex shelf:flex-1 shelf:overflow-hidden">
        --}}
{{-- Sidebar --}}{{--

        @include('shelf:library.partials.sidebar')

        --}}
{{-- File List --}}{{--

        @include('shelf:library.partials.file-list')

        --}}
{{-- File Details Panel --}}{{--

        @include('shelf:library.partials.file-details')
    </div>

    --}}
{{-- Footer --}}{{--

    @include('shelf:library.partials.footer')
--}}
</div>
</body>

</html>