//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");

var TRIP = TRIP || {};
$.extend(TRIP, {
	user_airport,

	appendDeal: function(flight, styleName) {
		//create HTML OBJ
		var HTML0 = "<div class=deal><div class=deal_destination>",
			HTML1 = "</div><div class=deal_days>",
			HTML2 = "</div><div class=deal_price>",
			HTML3 = "</div></div>",
			numDays = flight.get("end_data") - flight.get("start_date");
		$('.deals').append(
  			HTML0 + flight.get("destination") +
  			HTML1 + numDays +
  			HTML2 + flight.get("price") + HTML3;
	  	);
	},
	handleDeals: function(deals) {
		
	},
	getDeals: function() {
		var Deals = Parse.Object.extend("Deals"),
		deal = new Deals(),
		query = new Parse.Query(Deals);
		query.equalTo("origin", TRIP.user_airport);
		query.find({
		  success: function(results) {
		  	TRIP.handleDeals(results);
		  },
		  error: function(error) {
		    alert("Error: " + error.code + " " + error.message);
		  }
		});
	},	
});

$(function() {
     // Same as $(document).ready(function {}). TIL
     SHIP.getDeals();
});
