<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Verify Email</title>

<style>
body{
    margin:0;
    padding:0;
    background:#f6f8fa;
    font-family:Arial, Helvetica, sans-serif;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:#ffffff;
    border:1px solid #d0d7de;
    border-radius:12px;
    overflow:hidden;
}

.header{
    padding:30px;
    text-align:center;
}

.logo{
    font-size:28px;
    font-weight:bold;
}

.content{
    padding:30px;
    color:#24292f;
}

.content h1{
    font-size:28px;
    margin-bottom:20px;
}

.code-box{
    text-align:center;
    background:#f6f8fa;
    border:1px solid #d0d7de;
    border-radius:10px;
    padding:25px;
    margin:25px 0;
}

.code{
    font-size:40px;
    font-weight:bold;
    letter-spacing:8px;
}

.warning{
    color:#57606a;
    font-size:14px;
}

.footer{
    text-align:center;
    padding:20px;
    font-size:13px;
    color:#656d76;
    border-top:1px solid #d8dee4;
    background:#f6f8fa;
}

.button{
    display:inline-block;
    padding:12px 20px;
    background:#0969da;
    color:white;
    text-decoration:none;
    border-radius:6px;
    margin-top:20px;
}
</style>

</head>

<body>

<div class="container">

<div class="header">
    <div class="logo">
         Carepay
    </div>
</div>


<div class="content">

<h1>Please verify your email</h1>

<p>Hello {{ $user->name }},</p>

<p>
Thank you for registering with Carepay.
Use the verification code below to confirm
your email address.
</p>


<div class="code-box">

<p>Your verification code:</p>

<div class="code">
    {{ $token }}
</div>

</div>


<p class="warning">
This code expires in <strong>5 minutes</strong>
and can only be used once.
</p>


<p>
If you did not request this verification,
you can safely ignore this email.
</p>


<p>
Regards,<br>
The Carepay Team
</p>

</div>


<div class="footer">

<p>
You're receiving this email because a verification
request was made for your account.
</p>

<p>
© {{ date('Y') }} Carepay. All rights reserved.
</p>

</div>


</div>

</body>
</html>