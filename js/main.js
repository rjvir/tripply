//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

$(document).ready(function(){

Parse.initialize("mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL", "nMBVdpIpZ3XjGMBMOygTpC1OXfHtUUd7i5nlXaj3");
var source = $('#deal-template').html();
var template = Handlebars.compile(source),
	imgUrl = "http://www.photo-dictionary.com/photofiles/list/8/667airplane.jpg";

var TRIP = TRIP || {};

moment.calendar = {
    lastDay : '[Yesterday]',
    sameDay : '[Today]',
    nextDay : '[Tomorrow]',
    lastWeek : '[last] dddd',
    nextWeek : 'dddd',
    sameElse : 'L'
};

$.extend(TRIP, {
	user_airport: "DTW",
	item_count: 0,
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
		$("#dropdown-cities ul").append("<li><a href='?origin=" + city.get("airport_code") + "/'>" + city.get("city") + ", " + city.get("state") + "</a></li>");
	},
	appendDeal: function(flight, styleName) {
		var aptCode = flight.get("destCode"),
		CityImages = Parse.Object.extend("CityImages"),
		cityImage = new CityImages(),
		cityQuery = new Parse.Query(CityImages);
      	cityQuery.equalTo("airportCode", aptCode);
      	cityQuery.first({
		  success: function(result) {
		  	imgUrl = result.get("imageUrl");
			var startDate =  moment(flight.get("departDate"), "MM/DD/YY");
			returnDate = moment(flight.get("returnDate"), "MM/DD/YY"),
			numDays = returnDate.diff(startDate,'days'),
			randFact = ((Math.random() * 2) + 1.4);

		  	var html = template({
				destination: TRIP.locationString(flight.get("destLocation")), 
				bg: imgUrl,
				trip_length: (numDays != 0 ? ("for " + numDays + ((numDays != 1) ? " Nights" : " Night")) : "- Daytrip"),
				leaving: startDate.calendar(),
				price: flight.get("price"),
				old_price: parseInt(randFact*flight.get("price")),
				buynowhref: flight.get("link"),
				booknowhref: 'https://www.airbnb.com/s/'+flight.get("destLocation").replace('/', ' ')+'?checkin='+flight.get("departDate")+'&checkout='+flight.get("returnDate")
			})
			$('.deals-container').append(html);
			TRIP.item_count++;
			if (TRIP.item_count == TRIP.numCities) {
				$('.deal-box').first().addClass('large');
				TRIP.initIsotope();
			}
		  },
		  error: function(error) {
		    alert("Error: " + error.code + " " + error.message);
		  }
		});

	},
	getDeals: function() {
		var Deals = Parse.Object.extend("Deals"),
		deal = new Deals(),
		query = new Parse.Query(Deals);
		query.equalTo("originCode", TRIP.user_airport);
		query.count({
			success:function(count) {
				TRIP.numCities = count;
			},
			error: function(result) {
				//do nothing
			}
		});
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
		  layoutMode : 'masonry',
		  masonry : { 
		   	columnWidth : 320 
		  }

		});

		 $('.deal-box').click(function(){
		 	$('.deal-box.large').removeClass('large');
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
