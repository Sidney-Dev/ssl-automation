<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letsencrypt Email</title>
</head>
<body>
    <h1>Failed to renew certificate</h1>
    <p>Hi Mr {{ $mailData['name'] }}, The certificate renewal for domain {{ $mailData['domain'] }} has been failed because of the following: {{ $mailData['errorMessage'] }}</p>
</body>
</html>