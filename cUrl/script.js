$(document).ready(function(){
	var gigName = $("#gigName");
	var totalNumberOrders = $("#totalNumberOrders");
	var gigInfo = $("#gigInfo");
	var list = '<li class="list-group-item list-group-item-info">';
	var listEnd = '</li>';

	$.ajax({
		type: "POST",
		url: "cUrlCrawler.php",
		cache: false,
		success: function(result){

			var html = "";
			for (var i = 0; i < result.message.PageDetails.length; i++) {
				var detail = result.message.PageDetails[i];
				var htmlNode = list + "Title: " + detail.title + " <br /> " + "Number of orders: " + detail.numQueueOrders + listEnd;
				html += htmlNode;
			}

			gigName.text(result.username)
			totalNumberOrders.text(result.message.totalQueueOrders);
			gigInfo.html(html);
		}
	});
});