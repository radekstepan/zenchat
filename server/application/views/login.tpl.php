<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en" />

	<meta name="description" content="ZenChat" />

	<title>ZenChat</title>

	<link rel="stylesheet" type="text/css" media="screen" href="<?php $this->url('/public/tumblr.css') ;?>" />
</head>

<body>
	<div id="content">
        <h1><a href="<?php $this->url('/') ;?>">ZenChat</a></h1>

        <?php if (isset($system)) foreach($system as $message): ?>
            <div class="flash <?php echo $message['status'] ;?>"><?php echo $message['message'] ;?></div>
        <?php endforeach ;?>

        <a href="<?php $this->url('/register/') ;?>">Register a new user</a>

		<h2>Login</h2>
		
		<form action="<?php $this->url('secure') ;?>" method="post">
			<table>
				<tr class="required">
					<th><label class="required">Username:</label></th>
					<td><input type="text" class="text" name="username" /></td>
				</tr>
				<tr class="required">
					<th><label class="required">Password:</label></th>
					<td><input type="password" class="text" name="password" /></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td><input type="submit" class="button" name="login" value="Login" /></td>
				</tr>
			</table>
			<input type="hidden" name="token" value="<?php echo $token ;?>">
		</form>
	</div>
</body>
</html>
