$(document).ready(function(){
	var gigLayout = $("#gigLayout");
	var gigName = $("#gigName");
	var totalNumberOrders = $("#totalNumberOrders");
	var gigInfo = $("#gigInfo");
	var submit = $("#submit");
	var error = $("#error");
	var list = '<li class="list-group-item list-group-item-info">';
	var listEnd = '</li>';

	$("#submit").click(function(){
		var name = $("#username").val();

		$.ajax({
			type: "POST",
			url: "cUrlCrawler.php",
			data: name,
			cache: false,
			success: function(result){
				gigLayout.removeClass("hidden");
				error.addClass("hidden");
				var html = "";
				for (var i = 0; i < result.message.pageDetails.length; i++) {
					var detail = result.message.pageDetails[i];
					var htmlNode = list + "Title: " + detail.title + " <br /> " + "Number of orders: " + detail.numQueueOrders + listEnd;
					html += htmlNode;
				}

				gigName.text("Result for: " + result.message.username);
				totalNumberOrders.text(result.message.totalQueueOrders);
				gigInfo.html(html);
			},
			error: function(xhr, desc, err) {
				console.log(xhr);
				error.removeClass("hidden");
				gigLayout.addClass("hidden");
	      	}
		});
	});
});