<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	<head>

		<title>Ticket System</title>

		<meta charset="UTF-8">

		@include('layouts.css.globalcss')
		@yield('css')
		
		<!-- favicon icon 
		<link rel="shortcut icon" href="assets/favicon.ico"/>
		-->
	</head>
	<body>
		@include('layouts.banner')
		@yield('contents')

		@include('layouts.footer')
		@include('layouts.js.globaljs')
		@yield('js')
		
	</body>
<!-- END BODY -->
</html>