<!DOCTYPE html>
<html>
<head>
    <title>Upload Document</title>
</head>
<body>
    <form method="POST" action="{{ route('upload.file') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required accept=".doc,.docx">
        <button type="submit">Upload</button>
    </form>
</body>
</html>
