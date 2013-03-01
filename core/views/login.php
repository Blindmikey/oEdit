<?php if ($_SERVER["PHP_SELF"] == $_SERVER['REQUEST_URI']) die ("Tsk Tsk. Nice Try."); ?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>oEdit Login</title>
	<link rel="stylesheet" href="<?php echo STYLESPATH; ?>normalize.css" />
	<link rel="stylesheet" href="<?php echo STYLE; ?>" />
</head>
<body class="login">
	<!--<p>Message: <?php // echo $data['message']; ?></p>-->
	<fieldset>
		<legend><h2>oEdit Login</h2></legend>
		<form action="<?php echo LOCPATH; ?>" method="post">
		<?php if(isset($data['username'])) { ?>
			<label for="username">UserName: </label>
			<input type="text" name="username" />
		<?php } if(isset($data['email'])) { ?>
			<label for="email">Email Address: </label>
			<input type="text" name="email" />
		<?php } if(isset($data['password'])) { ?>
			<label for="password">Password: </label>
			<input type="password" name="password" />
		<?php } ?>
			<input type="submit" value="login" />
		</form>
	</fieldset>
</body>
</html>