//Written by Jesse Daugherty and Raj Vir
//(hop off our source code)

$(document).ready(function(){

$(window).politeWarning(10);

$('.deals-container').isotope({
  // options
  	itemSelector : '.deal-box',
  	layoutMode : 'masonry',
  	masonry : { 
   		columnWidth : 320 
  	}
});	

Parse.initialize("DaJkkAOKSFVxqPbI7gyPluuqRWkUgGIgzDzMJhUD", "X2oTRbTAnIRL3VVyI15JcSZJWs8BRqxxijudLJUq");
var deal_template_source = $('#deal-template').html(),
	dropdown_template_source = $('#dropdown-item-template').html(),
	deal_template = Handlebars.compile(deal_template_source),
	dropdown_item_template = Handlebars.compile(dropdown_template_source),
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

mixpanel.track('Loaded Page');

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
					$('#dropdown-button').prepend(results[0].get("city") + ", " + results[0].get("state") + " (" + results[0].get("airport_code") + ") ");
			},
			error: function(results) {
			    alert("Error: " + error.code + " " + error.message);
			}
		})
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
			var startDate =  moment(flight.get("departDate"), "MM/DD/YYYY"),
			returnDate = moment(flight.get("returnDate"), "MM/DD/YYYY"),
			numNights = returnDate.diff(startDate,'days'),
			dafact = ((Math.random() * 1.2) + 1.4),
			flightPrice = parseInt(flight.get("price")),
			hotelPrice = flight.get("hotel_price");
			if (hotelPrice == null) hotelPrice = 75; 
			
			var totalPrice = (hotelPrice * numNights) + flightPrice;
		  	var html = deal_template({
				destination: TRIP.locationString(flight.get("destLocation")), 
				bg: imgUrl,
				trip_length: "for " + numNights + ((numNights != 1) ? " Nights" : " Night"),
				leaving: startDate.calendar(),
				price: Math.round(flightPrice),
				old_price: parseInt(dafact * flightPrice),
				hotel_price: Math.round(hotelPrice),
				total_price: Math.round(totalPrice),
				buynowhref: flight.get("link"),
				booknowhref: flight.get("hotel_link")
			})
			$('.deal-box').first().addClass('large');
			$('.deals-container').isotope('insert', $(html));
			TRIP.item_count++;
			if (TRIP.item_count == TRIP.numCities) {
				$('.deal-box').click(function(){
					mixpanel.track('Enlarged Deal', $(this).find('.destination-name').text());
				 	$('.deal-box.large').removeClass('large');
				 	$(this).addClass('large');
				 	$('.deals-container').isotope('reLayout');
			 	});			
			}
			mixpanel.track_links(".buynow", "Click Buynow", function(ele) { return { type: $(ele).parent().find('.destination-name')}});
			mixpanel.track_links(".booknow", "Click Hotel", function(ele) { return { type: $(ele).parent().find('.destination-name')}});

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
		query.ascending("departDate");
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
  		origin_cities.sort(function(a,b) {return (a.city < b.city) ? -1 : 1;})
  		$.each(origin_cities, function() {
			var html = dropdown_item_template({
				airport_code: this.airport_code, 
				city: this.city,
				state: this.state
			});
			$("#dropdown-cities ul").append(html);
		});
	}
});
 TRIP.setLocale();
 TRIP.getDeals();
 TRIP.getCities();

});	
