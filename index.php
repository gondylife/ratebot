<html>
	<head>
		<title>Currency rate bot</title>
	</head>
	<body>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {

				var params = {
				    home_currency: "NGN",
				    convert_to: "H",
				    output_type: "OBJECT"
				}, payload = [], ready = true;

				$.each(params, function(key, val) {
				    payload.push(key + "=" + escape(val));
				    if (val.trim().length === 0) {
				        ready = false;
				    }
				});
				
				ready && $.post('getrate.php', payload.join("&"), function (data) {
			    	console.log(data);
				});
			});
		</script>
	</body>
</html>