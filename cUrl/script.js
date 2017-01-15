$(document).ready(function(){
	var gigLayout = $("#gigLayout");
	var gigName = $("#gigName");
	var totalNumberOrders = $("#totalNumberOrders");
	var gigInfo = $("#gigInfo");
	var submit = $("#submit");
	var error = $("#error");
	var list = '<li class="list-group-item list-group-item-info">';
	var listEnd = '</li>';
	var target = document.getElementById('spinner');
	var opts = {
		lines: 13 // The number of lines to draw
		, length: 28 // The length of each line
		, width: 14 // The line thickness
		, radius: 42 // The radius of the inner circle
		, scale: 1 // Scales overall size of the spinner
		, corners: 1 // Corner roundness (0..1)
		, color: '#000' // #rgb or #rrggbb or array of colors
		, opacity: 0.25 // Opacity of the lines
		, rotate: 0 // The rotation offset
		, direction: 1 // 1: clockwise, -1: counterclockwise
		, speed: 1 // Rounds per second
		, trail: 60 // Afterglow percentage
		, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
		, zIndex: 2e9 // The z-index (defaults to 2000000000)
		, className: 'spinner' // The CSS class to assign to the spinner
		, top: '50%' // Top position relative to parent
		, left: '50%' // Left position relative to parent
		, shadow: false // Whether to render a shadow
		, hwaccel: false // Whether to use hardware acceleration
		, position: 'absolute' // Element positioning
	};
	var spinner = new Spinner(opts);

	$("#submit").click(function(){

		startSpinner();

		var name = $("#username").val();

		$.ajax({
			type: "POST",
			url: "cUrlCrawler.php",
			data: {"username" : name},
			cache: false,
			success: function(result){
				stopSpinner();
				gigLayout.removeClass("hidden");
				error.addClass("hidden");
				var html = "";
				for (var i = 0; i < result.pageDetails.length; i++) {
					var detail = result.pageDetails[i];
					var htmlNode = list + "Title: " + detail.title + " <br /> " + "Number of orders: " + detail.numQueueOrders + listEnd;
					html += htmlNode;
				}

				gigName.text("Result for: " + result.username);
				totalNumberOrders.text(result.totalQueueOrders);
				gigInfo.html(html);
			},
			error: function(xhr, desc, err) {
				stopSpinner();
				console.log(xhr);
				error.removeClass("hidden");
				gigLayout.addClass("hidden");
	      	}
		});
	});

	function startSpinner(){
		spinner.spin(target);
		document.getElementById("submit").style = "display:none;";
	}

	function stopSpinner(){
		spinner.stop();
		document.getElementById("submit").style = "";
	}


});