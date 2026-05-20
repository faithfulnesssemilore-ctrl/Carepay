<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePay</title>
</head>
<body>
    <h1>Super Admin Dashboard</h1>
    <p>Welcome, Super Admin!</p>
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-link">Logout</button>
    </form>
</body>
</html>