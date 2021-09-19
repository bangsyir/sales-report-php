<?php
/*
* This file should display the login form. Login and authentication process should use user credentials
* from the file users.txt
*/
  ob_start();
  session_start();

  $lines = file('users.txt');
  $credentials = array();
  foreach($lines as $line) {
    if(empty($line)) continue;

    $lineArr = explode('|', $line);

    $username = explode('|', $lineArr[0]);
    $username = array_pop($username);

    $password = explode('|', $lineArr[1]);
    $password =  array_pop($password);

    $credentials[$username] = $password;
  }

  $username =  $_POST['username'];
  $password = $_POST['password'];
  // print_r($credentials);
  // echo ($username);
  var_dump(strcmp($password, $credentials[$username]));

  // if(!empty($_POST && $_POST['username'] === $credentials[$_POST['username']] && $_POST['password'] === $credentials[$_POST['usernmae']])) {
  // if(!empty(strcmp($_POST['password'],$credentials[$_POST['username']]) == int(-1))) {
  //   $_SESSION['logged_id'] = true;
  //   header('Location: /index.php');
  // } else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Login</title>
</head>
<body>
  <div class="d-flex justify-content-center pt-5">
    <div class="row">
      <div class="col-12">
        <form method="POST">
          <div class="form-group">
            <label for="username">Username</label>
            <input class="form-control" type="text" name="username" id="username">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input class="form-control" type="password" name="password" id="password">
          </div>
          <div class="float-right">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


