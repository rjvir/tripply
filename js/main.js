//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");

var TRIP = TRIP || {};
$.extend(TRIP, {
	user_airport: "DTW",
	parseDate: function(str) {
		var mdy = str.split('/')
    	return new Date('20' + mdy[2], mdy[0]-1, mdy[1]);
	},
	daydiff: function(start, end) {
		return Math.round((end-start)/(1000*60*60*24));
	},
	getURLParameter: function (name) {
	    return decodeURI(
	        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
	    );
	},
	locationString: function(str) {
		var parts = str.split(",");
		if (parts) {
			if (parts[1] != " DC") return parts[0];
		}
		return str;
	},
	setLocale: function() {
		var param = TRIP.getURLParameter("origin");
		if (param != 'null') TRIP.user_airport = param.substring(0, param.length - 1);;
		var Cities = Parse.Object.extend("Cities"),
		city = new Cities();
		query = new Parse.Query(Cities);
		query.equalTo("airport_code", TRIP.user_airport);
		query.find({
			success: function(results) {
				if (results && results.length > 0)
					$('#dropdown-button').html(results[0].get("city"));
			},
			error: function(results) {
			    alert("Error: " + error.code + " " + error.message);
			}
		})
	},
	appendToDropdown: function(city) {
		$("#dropdown-cities ul").append("<li><a href=?origin=" + city.get("airport_code") + ">" + city.get("city") + ", " + city.get("state") + "</a></li>");
	},
	appendDeal: function(flight, styleName) {
      	var imgUrl = flight.get("destImage"),
      		HTML0 = "<div class='deal-box grid_3'><div class=border-wrapper><div class=deal style='background-image:url(",
			HTML1 = ");'> <div class=destination>",
			HTML2 = "<br /><div class=price>",
			HTML3 = "</div></div></div></div></div>",
			startDate = TRIP.parseDate(flight.get("departDate")),
			returnDate = TRIP.parseDate(flight.get("returnDate")),
			numDays = TRIP.daydiff(startDate,returnDate);

		$('.deals').append(
  			HTML0 + imgUrl +
  			HTML1 + numDays + " nights in " + TRIP.locationString(flight.get("destLocation")) +
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
	getCities: function() {
		var Cities = Parse.Object.extend("Cities"),
		city = new Cities();
		query = new Parse.Query(Cities);
		query.notEqualTo("city", "Tijuana");
		query.find({
			success: function(results) {
		  		$.each(results, function() {
			  		TRIP.appendToDropdown(this);
			  	});
			},
			error: function(results) {
			    alert("Error: " + error.code + " " + error.message);
			}
		})
	}
});

$(function() {
     // Same as $(document).ready(function {}). TIL
     TRIP.setLocale();
     TRIP.getDeals();
     TRIP.getCities();
});