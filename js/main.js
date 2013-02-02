//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");

var TRIP = TRIP || {};
$.extend(TRIP, {
	user_airport: "DTW",
	appendDeal: function(flight, styleName) {
      	var imgUrl = "http://www.travelwizard.com/fiji/media/wadigibeach.jpg",
      		HTML0 = "<div class='deal-box grid_4'><div class=border-wrapper><div class=deal style='background-image:url(",
			HTML1 = ");'> <div class=destination>",
			HTML2 = "<br /><div class=price>",
			HTML3 = "</div></div></div></div></div>";
			var startDate = flight.get("departDate").replace(/\//g,""),
			endDate = flight.get("returnDate").replace(/\//g,"");
			startDate = parseInt(startDate);
			endDate = parseInt(endDate);
			var numDays = (endDate - startDate) / 100;
		$('.deals').append(
  			HTML0 + imgUrl +
  			HTML1 + flight.get("destLocation") + " for " + numDays + " nights." +
  			HTML2 + "$" + flight.get("price") + " leaving " + flight.get("departDate") + HTML3
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
