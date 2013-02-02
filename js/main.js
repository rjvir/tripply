//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");

var TRIP = TRIP || {};
$.extend(TRIP, {
	user_airport: "ATL",
	appendDeal: function(flight, styleName) {
		//create HTML OBJ
		var HTML0 = "<div class=deal><div class=destLocation> Location: ",
			HTML1 = "</div><div class=deal_days> Nights: ",
			HTML2 = "</div><div class=deal_price> Price : ",
			HTML3 = "</div></div>";
			var startDate = flight.get("departDate").replace(/\//g,""),
			endDate = flight.get("returnDate").replace(/\//g,"");
			startDate = parseInt(startDate);
			endDate = parseInt(endDate);
			var numDays = (endDate - startDate) / 100;
		$('.deals').append(
  			HTML0 + flight.get("destLocation") +
  			HTML1 + numDays +
  			HTML2 + flight.get("price") + HTML3
	  	);
	},
	getDeals: function() {
		var Deals = Parse.Object.extend("Deals"),
		deal = new Deals(),
		query = new Parse.Query(Deals);
		query.equalTo("originCode", TRIP.user_airport);
		query.find({
		  success: function(results) {
		  	$.each(results, function() {
		  		TRIP.appendDeal(this);
		  	});
		  },
		  error: function(error) {
		    alert("Error: " + error.code + " " + error.message);
		  }
		});
	},	
});

$(function() {
     // Same as $(document).ready(function {}). TIL
     TRIP.getDeals();
});
