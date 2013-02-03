//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

$(document).ready(function(){

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");
var source = $('#deal-template').html();
var template = Handlebars.compile(source);

var TRIP = TRIP || {};
$.extend(TRIP, {
	user_airport: "DTW",
	item_count: 0,
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
					$('#dropdown-button').prepend(results[0].get("city") + ", " + results[0].get("state") + " ");
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
			startDate = TRIP.parseDate(flight.get("departDate")),
			returnDate = TRIP.parseDate(flight.get("returnDate")),
			numDays = TRIP.daydiff(startDate,returnDate),
			randFact = ((Math.random() * 2) + 1.4);

		var html = template({
			destination: TRIP.locationString(flight.get("destLocation")), 
			bg: flight.get("destImage"),
			num_nights: numDays,
			leaving: flight.get("departDate"),
			price: flight.get("price"),
			old_price: parseInt(randFact*flight.get("price"))
		})
		$('.deals-container').append(html);
		TRIP.item_count++;
		if (TRIP.item_count == 12) {
			TRIP.initIsotope();
		}

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
	},
	initIsotope: function() {
		$('.deals-container').isotope({
		  // options
		  itemSelector : '.deal-box',
		  layoutMode : 'masonry'
		});

		 $('.deal-box').click(function(){
		 	$('.deal-box').removeClass('large');
		 	$(this).addClass('large');
		 	$('.deals-container').isotope('reLayout');
		 	//console.log('yo yo yo');
		 	//console.log(this);
		 	
		 })

	}
});
 TRIP.setLocale();
 TRIP.getDeals();
 TRIP.getCities();

});	
