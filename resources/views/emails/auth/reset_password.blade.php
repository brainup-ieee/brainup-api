<?php 
  $token = $data['token'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Reset Password Email</title>
  </head>
  <body style=" background-color: #F2F2F2;
  font-family: Arial, sans-serif;
  font-size: 16px;
  line-height: 1.4;
  color: #333333;">
    <div class="container" style=" max-width: 600px;
    margin: 0 auto;
    padding: 40px;
    background-color: #FFFFFF;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);">
      <h1 style="  font-size: 24px;
      margin: 0 0 20px;
      text-align: center;">You have Requested to reset your password</h1>
        <p style=" margin: 0 0 20px;">Please click the button below to reset your password</p>
        <a href="http://192.168.1.12:8080/auth/reset-password/{{$token}}" style=" display: inline-block">Reset Password</a>
        <p>Link will be expired in 15mins.</p>
      <p style=" margin: 0 0 20px;">brainup Team</p>
    </div>
  </body>
</html>