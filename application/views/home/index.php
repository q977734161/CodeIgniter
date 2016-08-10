<html>

	<head>
		<meta charset="utf-8">
		<title>CI测试</title>
	</head>

	<body>
		<form action="<?php echo site_url("home/test");?>" method="post">
			<table>
				<tr>
					<td>名字:</td>
					<td><input type='text' name='name' placehoder="名字">
					
					<input type="hidden" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
					</td>
				</tr>
			</table>
		
		</form>
		
	</body>



</html>