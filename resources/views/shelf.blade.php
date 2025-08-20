<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHELF</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        #shelf-root { min-height: 100vh; background: #f5f5f5; }
    </style>
    <link rel="stylesheet" href="{{ url('hetbo/shelf/shelf.css') }}">
</head>
<body>
<div id="shelf-root"></div>

<script>
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.shelfApiUrl = '{{ url("/shelf/api") }}';
</script>

<script src="{{ url('hetbo/shelf/shelf.umd.cjs') }}"></script>
</body>
</html>